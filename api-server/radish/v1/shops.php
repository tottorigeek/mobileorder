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
    $action = isset($pathParts[2]) ? $pathParts[2] : null;
    $subId = isset($pathParts[3]) ? $pathParts[3] : null;
} else {
    // 直接呼び出された場合（通常は発生しない）
    $identifier = isset($pathParts[0]) ? $pathParts[0] : null;
    $action = isset($pathParts[1]) ? $pathParts[1] : null;
    $subId = isset($pathParts[2]) ? $pathParts[2] : null;
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
    
    case 'POST':
        // オーナー追加: POST /api/shops/{id}/owners
        if ($shopId && $action === 'owners') {
            addShopOwner($shopId);
        } else {
            sendErrorResponse(400, 'Invalid endpoint');
        }
        break;
    
    case 'PUT':
        if ($shopId) {
            updateShop($shopId);
        } else {
            sendErrorResponse(400, 'Shop ID is required');
        }
        break;
    
    case 'DELETE':
        // オーナー削除: DELETE /api/shops/{id}/owners/{userId}
        if ($shopId && $action === 'owners' && $subId) {
            removeShopOwner($shopId, (int)$subId);
        } elseif ($shopId) {
            deleteShop($shopId);
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
                // GROUP BYを削除して複数のオーナーを取得
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.max_tables, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN shop_users su ON s.id = su.shop_id AND su.role = 'owner'
                        LEFT JOIN users u ON su.user_id = u.id
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC, u.name ASC";
            } else {
                // shop_usersテーブルが存在しない場合：従来の方法
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.max_tables, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN users u ON s.id = u.shop_id AND u.role = 'owner'
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC, u.name ASC";
            }
        } else {
            if ($hasShopUsers) {
                // shop_usersテーブルが存在する場合：複数店舗オーナー対応
                // GROUP BYを削除して複数のオーナーを取得
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN shop_users su ON s.id = su.shop_id AND su.role = 'owner'
                        LEFT JOIN users u ON su.user_id = u.id
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC, u.name ASC";
            } else {
                // shop_usersテーブルが存在しない場合：従来の方法
                $sql = "SELECT s.id, s.code, s.name, s.description, s.address, s.phone, s.email, s.is_active,
                               u.id as owner_id, u.name as owner_name, u.username as owner_username, u.email as owner_email
                        FROM shops s
                        LEFT JOIN users u ON s.id = u.shop_id AND u.role = 'owner'
                        WHERE s.is_active = 1 
                        ORDER BY s.name ASC, u.name ASC";
            }
        }
        
        $stmt = $pdo->query($sql);
        $shops = $stmt->fetchAll();
        
        // 店舗ごとに複数のオーナーを配列として集約
        $shopsMap = [];
        foreach ($shops as $shopRow) {
            $shopId = (string)$shopRow['id'];
            
            if (!isset($shopsMap[$shopId])) {
                // 店舗情報を初期化
                $shopsMap[$shopId] = [
                    'id' => $shopId,
                    'code' => $shopRow['code'],
                    'name' => $shopRow['name'],
                    'description' => $shopRow['description'],
                    'address' => $shopRow['address'],
                    'phone' => $shopRow['phone'],
                    'email' => $shopRow['email'],
                    'maxTables' => $hasMaxTables ? (int)$shopRow['max_tables'] : 20,
                    'isActive' => (bool)$shopRow['is_active'],
                    'owners' => []
                ];
            }
            
            // オーナーが存在する場合は追加
            if ($shopRow['owner_id']) {
                $owner = [
                    'id' => (string)$shopRow['owner_id'],
                    'name' => $shopRow['owner_name'],
                    'username' => $shopRow['owner_username'],
                    'email' => $shopRow['owner_email'] ?? null
                ];
                
                // 重複チェック（同じオーナーIDが既に追加されていないか）
                $ownerExists = false;
                foreach ($shopsMap[$shopId]['owners'] as $existingOwner) {
                    if ($existingOwner['id'] === $owner['id']) {
                        $ownerExists = true;
                        break;
                    }
                }
                
                if (!$ownerExists) {
                    $shopsMap[$shopId]['owners'][] = $owner;
                }
            }
        }
        
        // 配列に変換し、owners配列をownerに変換（後方互換性のため）
        $result = array_map(function($shop) {
            return [
                'id' => $shop['id'],
                'code' => $shop['code'],
                'name' => $shop['name'],
                'description' => $shop['description'],
                'address' => $shop['address'],
                'phone' => $shop['phone'],
                'email' => $shop['email'],
                'maxTables' => $shop['maxTables'],
                'isActive' => $shop['isActive'],
                'owner' => count($shop['owners']) > 0 ? $shop['owners'][0] : null, // 後方互換性のため最初のオーナーを設定
                'owners' => $shop['owners'] // 複数のオーナーを配列として追加
            ];
        }, array_values($shopsMap));
        
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
            SELECT id, code, name, description, address, phone, email, max_tables, is_active, settings, created_at, updated_at
            FROM shops 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $shopId]);
        $shop = $stmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // shop_usersテーブルが存在するか確認
        $hasShopUsers = false;
        try {
            $checkShopUsers = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $hasShopUsers = $checkShopUsers->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        // オーナー情報を取得
        $owners = [];
        if ($hasShopUsers) {
            $ownerStmt = $pdo->prepare("
                SELECT u.id, u.name, u.username, u.email
                FROM shop_users su
                INNER JOIN users u ON su.user_id = u.id
                WHERE su.shop_id = :shop_id AND su.role = 'owner'
                ORDER BY u.name ASC
            ");
            $ownerStmt->execute([':shop_id' => $shopId]);
            $ownerRows = $ownerStmt->fetchAll();
            foreach ($ownerRows as $ownerRow) {
                $owners[] = [
                    'id' => (string)$ownerRow['id'],
                    'name' => $ownerRow['name'],
                    'username' => $ownerRow['username'],
                    'email' => $ownerRow['email'] ?? null
                ];
            }
        } else {
            // 従来の方法：usersテーブルから取得
            $ownerStmt = $pdo->prepare("
                SELECT id, name, username, email
                FROM users
                WHERE shop_id = :shop_id AND role = 'owner'
                ORDER BY name ASC
            ");
            $ownerStmt->execute([':shop_id' => $shopId]);
            $ownerRows = $ownerStmt->fetchAll();
            foreach ($ownerRows as $ownerRow) {
                $owners[] = [
                    'id' => (string)$ownerRow['id'],
                    'name' => $ownerRow['name'],
                    'username' => $ownerRow['username'],
                    'email' => $ownerRow['email'] ?? null
                ];
            }
        }
        
        // settings JSONをパース
        $settings = null;
        if (!empty($shop['settings'])) {
            $decodedSettings = json_decode($shop['settings'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $settings = $decodedSettings;
            }
        }
        
        $result = [
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
            'updatedAt' => $shop['updated_at'],
            'owner' => count($owners) > 0 ? $owners[0] : null, // 後方互換性のため
            'owners' => $owners,
            'settings' => $settings
        ];
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
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
        
        if (isset($input['settings'])) {
            // settings JSONをエンコード
            $settingsJson = json_encode($input['settings'], JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                sendValidationError(['settings' => 'Invalid settings JSON format']);
            }
            $updates[] = "settings = :settings";
            $params[':settings'] = $settingsJson;
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

/**
 * 店舗削除（認証必須、companyロールのみ）
 */
function deleteShop($shopId) {
    try {
        // 認証チェック（companyロールのみ）
        $auth = checkPermission(['company']);
        
        $pdo = getDbConnection();
        
        // 店舗の存在確認
        $shopStmt = $pdo->prepare("SELECT id, name FROM shops WHERE id = :id");
        $shopStmt->execute([':id' => $shopId]);
        $shop = $shopStmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // shop_usersテーブルが存在するか確認
        $hasShopUsers = false;
        try {
            $checkShopUsers = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $hasShopUsers = $checkShopUsers->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        // オーナーが存在するかチェック
        $hasOwner = false;
        if ($hasShopUsers) {
            $ownerStmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM shop_users 
                WHERE shop_id = :shop_id AND role = 'owner'
            ");
            $ownerStmt->execute([':shop_id' => $shopId]);
            $ownerCount = $ownerStmt->fetch()['count'];
            $hasOwner = $ownerCount > 0;
        } else {
            // 従来の方法：usersテーブルから確認
            $ownerStmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE shop_id = :shop_id AND role = 'owner'
            ");
            $ownerStmt->execute([':shop_id' => $shopId]);
            $ownerCount = $ownerStmt->fetch()['count'];
            $hasOwner = $ownerCount > 0;
        }
        
        if ($hasOwner) {
            sendErrorResponse(400, 'Cannot delete shop: Owner exists. Please remove the owner first.');
        }
        
        // 関連データの削除（外部キー制約がある場合に備えて）
        // shop_tablesテーブルから削除
        try {
            $deleteTablesStmt = $pdo->prepare("DELETE FROM shop_tables WHERE shop_id = :shop_id");
            $deleteTablesStmt->execute([':shop_id' => $shopId]);
        } catch (Exception $e) {
            // テーブルが存在しない場合は無視
        }
        
        // shop_categoriesテーブルから削除
        try {
            $deleteCategoriesStmt = $pdo->prepare("DELETE FROM shop_categories WHERE shop_id = :shop_id");
            $deleteCategoriesStmt->execute([':shop_id' => $shopId]);
        } catch (Exception $e) {
            // テーブルが存在しない場合は無視
        }
        
        // shop_usersテーブルから削除
        if ($hasShopUsers) {
            try {
                $deleteShopUsersStmt = $pdo->prepare("DELETE FROM shop_users WHERE shop_id = :shop_id");
                $deleteShopUsersStmt->execute([':shop_id' => $shopId]);
            } catch (Exception $e) {
                // エラーは無視
            }
        }
        
        // usersテーブルから削除（shop_idが設定されている場合）
        try {
            $deleteUsersStmt = $pdo->prepare("DELETE FROM users WHERE shop_id = :shop_id");
            $deleteUsersStmt->execute([':shop_id' => $shopId]);
        } catch (Exception $e) {
            // エラーは無視
        }
        
        // 店舗を削除（物理削除）
        $deleteStmt = $pdo->prepare("DELETE FROM shops WHERE id = :id");
        $deleteStmt->execute([':id' => $shopId]);
        
        if ($deleteStmt->rowCount() === 0) {
            sendNotFoundError('Shop');
        }
        
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting shop');
    }
}

/**
 * 店舗にオーナーを追加（認証必須、companyロールのみ）
 */
function addShopOwner($shopId) {
    try {
        // 認証チェック（companyロールのみ）
        $auth = checkPermission(['company']);
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['userId'])) {
            sendValidationError(['userId' => 'User ID is required']);
        }
        
        $userId = (int)$input['userId'];
        
        // 店舗の存在確認
        $shopStmt = $pdo->prepare("SELECT id, name FROM shops WHERE id = :id");
        $shopStmt->execute([':id' => $shopId]);
        $shop = $shopStmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // ユーザーの存在確認
        $userStmt = $pdo->prepare("SELECT id, name, username FROM users WHERE id = :id");
        $userStmt->execute([':id' => $userId]);
        $user = $userStmt->fetch();
        
        if (!$user) {
            sendNotFoundError('User');
        }
        
        // shop_usersテーブルが存在するか確認
        $hasShopUsers = false;
        try {
            $checkShopUsers = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $hasShopUsers = $checkShopUsers->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        if ($hasShopUsers) {
            // shop_usersテーブルを使用
            // 既にオーナーとして登録されているかチェック
            $checkStmt = $pdo->prepare("
                SELECT id FROM shop_users 
                WHERE shop_id = :shop_id AND user_id = :user_id AND role = 'owner'
            ");
            $checkStmt->execute([':shop_id' => $shopId, ':user_id' => $userId]);
            if ($checkStmt->fetch()) {
                sendErrorResponse(400, 'User is already an owner of this shop');
            }
            
            // shop_usersテーブルに追加
            $insertStmt = $pdo->prepare("
                INSERT INTO shop_users (shop_id, user_id, role, created_at, updated_at)
                VALUES (:shop_id, :user_id, 'owner', NOW(), NOW())
            ");
            $insertStmt->execute([':shop_id' => $shopId, ':user_id' => $userId]);
        } else {
            // 従来の方法：usersテーブルのshop_idとroleを更新
            // 既に他の店舗のオーナーになっているかチェック
            $checkStmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE id = :user_id AND shop_id IS NOT NULL AND shop_id != :shop_id AND role = 'owner'
            ");
            $checkStmt->execute([':user_id' => $userId, ':shop_id' => $shopId]);
            if ($checkStmt->fetch()) {
                sendErrorResponse(400, 'User is already an owner of another shop');
            }
            
            // usersテーブルを更新
            $updateStmt = $pdo->prepare("
                UPDATE users 
                SET shop_id = :shop_id, role = 'owner', updated_at = NOW()
                WHERE id = :user_id
            ");
            $updateStmt->execute([':shop_id' => $shopId, ':user_id' => $userId]);
        }
        
        // 追加したオーナー情報を返す
        echo json_encode([
            'success' => true,
            'owner' => [
                'id' => (string)$user['id'],
                'name' => $user['name'],
                'username' => $user['username']
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'adding shop owner');
    }
}

/**
 * 店舗からオーナーを削除（認証必須、companyロールのみ）
 */
function removeShopOwner($shopId, $userId) {
    try {
        // 認証チェック（companyロールのみ）
        $auth = checkPermission(['company']);
        
        $pdo = getDbConnection();
        
        // 店舗の存在確認
        $shopStmt = $pdo->prepare("SELECT id, name FROM shops WHERE id = :id");
        $shopStmt->execute([':id' => $shopId]);
        $shop = $shopStmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // shop_usersテーブルが存在するか確認
        $hasShopUsers = false;
        try {
            $checkShopUsers = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $hasShopUsers = $checkShopUsers->rowCount() > 0;
        } catch (Exception $e) {
            // テーブル確認に失敗した場合は続行
        }
        
        if ($hasShopUsers) {
            // shop_usersテーブルから削除
            $deleteStmt = $pdo->prepare("
                DELETE FROM shop_users 
                WHERE shop_id = :shop_id AND user_id = :user_id AND role = 'owner'
            ");
            $deleteStmt->execute([':shop_id' => $shopId, ':user_id' => $userId]);
            
            if ($deleteStmt->rowCount() === 0) {
                sendNotFoundError('Shop owner relationship');
            }
        } else {
            // 従来の方法：usersテーブルから削除（shop_idをNULLに設定）
            $updateStmt = $pdo->prepare("
                UPDATE users 
                SET shop_id = NULL, role = 'staff', updated_at = NOW()
                WHERE id = :user_id AND shop_id = :shop_id AND role = 'owner'
            ");
            $updateStmt->execute([':user_id' => $userId, ':shop_id' => $shopId]);
            
            if ($updateStmt->rowCount() === 0) {
                sendNotFoundError('Shop owner relationship');
            }
        }
        
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'removing shop owner');
    }
}

