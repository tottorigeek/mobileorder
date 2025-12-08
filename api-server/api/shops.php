<?php
/**
 * 店舗API
 * GET /api/shops - 店舗一覧取得（公開）
 * GET /api/shops/{code} - 店舗情報取得（公開、店舗コードで）
 * GET /api/shops/{id} - 店舗情報取得（認証必須、IDで）
 * PUT /api/shops/{id} - 店舗情報更新（認証必須、オーナーのみ）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析（index.php経由で呼び出される場合を考慮）
// /radish/api/shops または /radish/api/shops/123 の形式から、shops以降の部分を取得
$path = str_replace('/radish/api/', '', $path);
$path = trim($path, '/');
$pathParts = explode('/', $path);

// shops以降の部分を取得（shops.phpが直接呼び出される場合とindex.php経由の場合の両方に対応）
if ($pathParts[0] === 'shops') {
    // shopsエンドポイントの場合、次の部分を取得
    $identifier = isset($pathParts[1]) ? $pathParts[1] : null;
} else {
    // 直接呼び出された場合（通常は発生しない）
    $identifier = isset($pathParts[0]) ? $pathParts[0] : null;
}

// IDかコードかを判定（数値ならID、そうでなければコード）
$shopId = null;
$shopCode = null;
if ($identifier) {
    if (is_numeric($identifier)) {
        $shopId = (int)$identifier;
    } else {
        $shopCode = $identifier;
    }
}

switch ($method) {
    case 'GET':
        if ($shopId) {
            getShopById($shopId);
        } elseif ($shopCode) {
            getShop($shopCode);
        } else {
            getShops();
        }
        break;
    
    case 'PUT':
        if ($shopId) {
            updateShop($shopId);
        } else {
            sendErrorResponse(400, 'Shop ID is required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * 店舗一覧取得（公開）
 */
function getShops() {
    try {
        $pdo = getDbConnection();
        
        // shopsテーブルの存在確認
        $tableExists = false;
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'shops'");
            $tableExists = $checkTable->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        if (!$tableExists) {
            // テーブルが存在しない場合は空配列を返す
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // max_tablesカラムが存在するか確認（後方互換性のため）
        $hasMaxTables = false;
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM shops LIKE 'max_tables'");
            $hasMaxTables = $checkColumn->rowCount() > 0;
        } catch (Exception $e) {
            // カラム確認に失敗した場合は続行
        }
        
        // shop_usersテーブルが存在するか確認
        $hasShopUsers = false;
        try {
            $checkShopUsers = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $hasShopUsers = $checkShopUsers->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        if ($hasMaxTables) {
            if ($hasShopUsers) {
                // shop_usersテーブルが存在する場合：複数店舗オーナー対応
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.max_tables, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN shop_users su ON s.id = su.shop_id AND su.role = 'owner'
                        LEFT JOIN users u ON su.user_id = u.id
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC";
            } else {
                // shop_usersテーブルが存在しない場合：従来の方法
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.max_tables, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN users u ON s.id = u.shop_id AND u.role = 'owner'
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC";
            }
        } else {
            if ($hasShopUsers) {
                // shop_usersテーブルが存在する場合：複数店舗オーナー対応
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN shop_users su ON s.id = su.shop_id AND su.role = 'owner'
                        LEFT JOIN users u ON su.user_id = u.id
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC";
            } else {
                // shop_usersテーブルが存在しない場合：従来の方法
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN users u ON s.id = u.shop_id AND u.role = 'owner'
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC";
            }
        }
        
        $stmt = $pdo->query($sql);
        $shops = $stmt->fetchAll();
        
        $result = array_map(function($shop) use ($hasMaxTables) {
            $owner = null;
            if ($shop['owner_id']) {
                $owner = [
                    'id' => (string)$shop['owner_id'],
                    'name' => $shop['owner_name'],
                    'username' => $shop['owner_username'],
                    'email' => $shop['owner_email'] ?? null
                ];
            }
            
            return [
                'id' => (string)$shop['id'],
                'code' => $shop['code'],
                'name' => $shop['name'],
                'description' => $shop['description'],
                'address' => $shop['address'],
                'phone' => $shop['phone'],
                'email' => $shop['email'],
                'maxTables' => $hasMaxTables ? (int)$shop['max_tables'] : 20, // デフォルト値
                'isActive' => (bool)$shop['is_active'],
                'owner' => $owner
            ];
        }, $shops);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        // エラーログに記録（無限ループを防ぐため、try-catchで囲む）
        try {
            error_log("Error in getShops(): " . $e->getMessage() . " | Code: " . $e->getCode());
        } catch (Exception $logError) {
            // ログ記録に失敗しても続行
        }
        
        // エラーメッセージを返す（デバッグ用）
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch shops',
            'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error',
            'code' => DEBUG_MODE ? $e->getCode() : null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        // その他のエラー
        try {
            error_log("Unexpected error in getShops(): " . $e->getMessage());
        } catch (Exception $logError) {
            // ログ記録に失敗しても続行
        }
        
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch shops',
            'message' => DEBUG_MODE ? $e->getMessage() : 'Internal server error'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * 店舗情報取得（公開）
 */
function getShop($shopCode) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT id, code, name, description, address, phone, email, max_tables 
            FROM shops 
            WHERE code = :code AND is_active = 1
        ");
        $stmt->execute([':code' => $shopCode]);
        $shop = $stmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        echo json_encode([
            'id' => (string)$shop['id'],
            'code' => $shop['code'],
            'name' => $shop['name'],
            'description' => $shop['description'],
            'address' => $shop['address'],
            'phone' => $shop['phone'],
            'email' => $shop['email'],
            'maxTables' => (int)$shop['max_tables']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching shop');
    }
}

/**
 * 店舗情報取得（IDで、認証必須）
 */
function getShopById($shopId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT id, code, name, description, address, phone, email, max_tables, is_active, created_at, updated_at
            FROM shops 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $shopId]);
        $shop = $stmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        echo json_encode([
            'id' => (string)$shop['id'],
            'code' => $shop['code'],
            'name' => $shop['name'],
            'description' => $shop['description'],
            'address' => $shop['address'],
            'phone' => $shop['phone'],
            'email' => $shop['email'],
            'maxTables' => (int)$shop['max_tables'],
            'isActive' => (bool)$shop['is_active'],
            'createdAt' => $shop['created_at'],
            'updatedAt' => $shop['updated_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching shop');
    }
}

/**
 * 店舗情報更新（認証必須、オーナーのみ）
 */
function updateShop($shopId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendValidationError(['data' => 'Request data is required']);
        }
        
        // 更新可能なフィールドをチェック
        $updates = [];
        $params = [':id' => $shopId];
        
        if (isset($input['code'])) {
            // 店舗コードの重複チェック
            $checkStmt = $pdo->prepare("SELECT id FROM shops WHERE code = :code AND id != :id");
            $checkStmt->execute([':code' => $input['code'], ':id' => $shopId]);
            if ($checkStmt->fetch()) {
                sendConflictError('Shop code already exists');
            }
            $updates[] = "code = :code";
            $params[':code'] = $input['code'];
        }
        
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $input['name'];
        }
        
        if (isset($input['description'])) {
            $updates[] = "description = :description";
            $params[':description'] = $input['description'];
        }
        
        if (isset($input['address'])) {
            $updates[] = "address = :address";
            $params[':address'] = $input['address'];
        }
        
        if (isset($input['phone'])) {
            $updates[] = "phone = :phone";
            $params[':phone'] = $input['phone'];
        }
        
        if (isset($input['email'])) {
            $updates[] = "email = :email";
            $params[':email'] = $input['email'];
        }
        
        if (isset($input['maxTables'])) {
            $updates[] = "max_tables = :max_tables";
            $params[':max_tables'] = (int)$input['maxTables'];
        }
        
        if (isset($input['isActive'])) {
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $input['isActive'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE shops SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('Shop');
        }
        
        // 更新した店舗情報を返す
        getShopById($shopId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating shop');
    }
}

