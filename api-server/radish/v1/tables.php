<?php
/**
 * 店舗テーブル（座席）管理API
 * GET /api/tables?shop_id={shop_id} - 店舗のテーブル一覧取得（認証必須）
 * GET /api/tables/{id} - テーブル情報取得（認証必須）
 * POST /api/tables - テーブル作成（認証必須、オーナー・マネージャーのみ）
 * PUT /api/tables/{id} - テーブル更新（認証必須、オーナー・マネージャーのみ）
 * DELETE /api/tables/{id} - テーブル削除（認証必須、オーナー・マネージャーのみ）
 * GET /api/tables/qr/{shop_code}/{table_number} - QRコード用テーブル情報取得（公開）
 * GET /api/tables/shop/{shop_code} - 店舗コードからテーブル一覧取得（公開）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析（index.php経由で呼び出される場合を考慮）
// v1/index.php経由で呼び出された場合、環境変数から残りのパスを取得
if (isset($_ENV['TABLES_REMAINING_PATH']) && !empty($_ENV['TABLES_REMAINING_PATH'])) {
    $path = $_ENV['TABLES_REMAINING_PATH'];
} else {
    // 直接呼び出された場合、/radish/v1/tables/ または /radish/api/tables/ を削除
    $path = preg_replace('#^/radish/(v1|api)/tables/#', '', $path);
}
$path = trim($path, '/');
$pathParts = explode('/', $path);

// QRコード用エンドポイントの判定
if (isset($pathParts[0]) && $pathParts[0] === 'qr' && isset($pathParts[1]) && isset($pathParts[2])) {
    $shopCode = $pathParts[1];
    $tableNumber = $pathParts[2];
    getTableByQRCode($shopCode, $tableNumber);
    exit;
}

// 店舗コードからテーブル一覧取得（公開エンドポイント）
if (isset($pathParts[0]) && $pathParts[0] === 'shop' && isset($pathParts[1])) {
    $shopCode = $pathParts[1];
    getTablesByShopCode($shopCode);
    exit;
}

$tableId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
$shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;

switch ($method) {
    case 'GET':
        if ($tableId) {
            getTable($tableId);
        } else {
            getTables($shopId);
        }
        break;
    
    case 'POST':
        createTable();
        break;
    
    case 'PUT':
        if ($tableId) {
            updateTable($tableId);
        } else {
            sendErrorResponse(400, 'Table ID is required');
        }
        break;
    
    case 'DELETE':
        if ($tableId) {
            deleteTable($tableId);
        } else {
            sendErrorResponse(400, 'Table ID is required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * テーブル一覧取得（認証必須）
 */
function getTables($shopId = null) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $userId = $auth['user_id'];
        $defaultShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // ユーザーが所属する店舗IDのリストを取得
        $userShopIds = [];
        
        // shop_usersテーブルが存在するか確認
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (PDOException $e) {
            // テーブルが存在しない場合は既存の方法を使用
        }
        
        if ($tableExists) {
            // 複数店舗対応: shop_usersテーブルから取得
            $shopUsersSql = "SELECT shop_id FROM shop_users WHERE user_id = :user_id";
            $shopUsersStmt = $pdo->prepare($shopUsersSql);
            $shopUsersStmt->execute([':user_id' => $userId]);
            $shopUsers = $shopUsersStmt->fetchAll(PDO::FETCH_COLUMN);
            $userShopIds = array_map('intval', $shopUsers);
        } else {
            // 既存の方法: usersテーブルのshop_idから取得
            if ($defaultShopId) {
                $userShopIds = [intval($defaultShopId)];
            }
        }
        
        // shop_idが指定されている場合、その店舗がユーザーの所属店舗に含まれているかチェック
        if ($shopId) {
            $requestedShopId = intval($shopId);
            if (!in_array($requestedShopId, $userShopIds)) {
                // デバッグ情報: ユーザーの所属店舗IDを確認
                error_log("User {$userId} attempted to access shop_id={$requestedShopId}, but belongs to shops: " . implode(',', $userShopIds));
                sendForbiddenError('You can only access tables from your own shop');
            }
            // 指定された店舗のテーブルのみ取得
            $userShopIds = [$requestedShopId];
        }
        
        if (empty($userShopIds)) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // IN句用のプレースホルダーとパラメータを準備
        $placeholders = [];
        $params = [];
        foreach ($userShopIds as $index => $shopIdValue) {
            $paramName = ':shop_id_' . $index;
            $placeholders[] = $paramName;
            $params[$paramName] = $shopIdValue;
        }
        
        $stmt = $pdo->prepare("
            SELECT st.*, s.code as shop_code, s.name as shop_name
            FROM shop_tables st
            INNER JOIN shops s ON st.shop_id = s.id
            WHERE st.shop_id IN (" . implode(',', $placeholders) . ")
            ORDER BY 
                s.name ASC,
                CASE st.status
                    WHEN 'set_pending' THEN 1
                    WHEN 'checkout_pending' THEN 2
                    WHEN 'occupied' THEN 3
                    ELSE 4
                END,
                CAST(st.table_number AS UNSIGNED) ASC,
                st.table_number ASC
        ");
        $stmt->execute($params);
        $tables = $stmt->fetchAll();
        
        $result = array_map(function($table) {
            return [
                'id' => (string)$table['id'],
                'shopId' => (string)$table['shop_id'],
                'shopCode' => $table['shop_code'],
                'shopName' => $table['shop_name'],
                'tableNumber' => $table['table_number'],
                'name' => $table['name'],
                'capacity' => (int)$table['capacity'],
                'isActive' => (bool)$table['is_active'],
                'visitorId' => $table['visitor_id'] ? (string)$table['visitor_id'] : null,
                'status' => $table['status'] ?? 'available',
                'qrCodeUrl' => $table['qr_code_url'],
                'createdAt' => $table['created_at'],
                'updatedAt' => $table['updated_at']
            ];
        }, $tables);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching tables');
    }
}

/**
 * テーブル情報取得（認証必須）
 */
function getTable($tableId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $userShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT st.*, s.code as shop_code, s.name as shop_name
            FROM shop_tables st
            INNER JOIN shops s ON st.shop_id = s.id
            WHERE st.id = :id
        ");
        $stmt->execute([':id' => $tableId]);
        $table = $stmt->fetch();
        
        if (!$table) {
            sendNotFoundError('Table');
        }
        
        // 権限チェック：自分の店舗のテーブルのみ取得可能（複数店舗対応）
        $userId = $auth['user_id'];
        $userShopIds = [];
        
        // shop_usersテーブルが存在するか確認
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'shop_users'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (PDOException $e) {
            // テーブルが存在しない場合は既存の方法を使用
        }
        
        if ($tableExists) {
            // 複数店舗対応: shop_usersテーブルから取得
            $shopUsersSql = "SELECT shop_id FROM shop_users WHERE user_id = :user_id";
            $shopUsersStmt = $pdo->prepare($shopUsersSql);
            $shopUsersStmt->execute([':user_id' => $userId]);
            $shopUsers = $shopUsersStmt->fetchAll(PDO::FETCH_COLUMN);
            $userShopIds = array_map('intval', $shopUsers);
        } else {
            // 既存の方法: usersテーブルのshop_idから取得
            if ($userShopId) {
                $userShopIds = [intval($userShopId)];
            }
        }
        
        // テーブルが所属する店舗がユーザーの所属店舗に含まれているかチェック
        if (!empty($userShopIds) && !in_array(intval($table['shop_id']), $userShopIds)) {
            error_log("User {$userId} attempted to access table_id={$tableId} from shop_id={$table['shop_id']}, but belongs to shops: " . implode(',', $userShopIds));
            sendForbiddenError('You can only access tables from your own shop');
        }
        
        echo json_encode([
            'id' => (string)$table['id'],
            'shopId' => (string)$table['shop_id'],
            'shopCode' => $table['shop_code'],
            'shopName' => $table['shop_name'],
            'tableNumber' => $table['table_number'],
            'name' => $table['name'],
            'capacity' => (int)$table['capacity'],
            'isActive' => (bool)$table['is_active'],
            'visitorId' => $table['visitor_id'] ? (string)$table['visitor_id'] : null,
            'status' => $table['status'] ?? 'available',
            'qrCodeUrl' => $table['qr_code_url'],
            'createdAt' => $table['created_at'],
            'updatedAt' => $table['updated_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching table');
    }
}

/**
 * QRコード用テーブル情報取得（公開）
 */
function getTableByQRCode($shopCode, $tableNumber) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT st.*, s.code as shop_code, s.name as shop_name
            FROM shop_tables st
            INNER JOIN shops s ON st.shop_id = s.id
            WHERE s.code = :shop_code 
            AND st.table_number = :table_number
            AND st.is_active = 1
            AND s.is_active = 1
        ");
        $stmt->execute([
            ':shop_code' => $shopCode,
            ':table_number' => $tableNumber
        ]);
        $table = $stmt->fetch();
        
        if (!$table) {
            sendNotFoundError('Table not found or inactive');
        }
        
        echo json_encode([
            'id' => (string)$table['id'],
            'shopId' => (string)$table['shop_id'],
            'shopCode' => $table['shop_code'],
            'shopName' => $table['shop_name'],
            'tableNumber' => $table['table_number'],
            'name' => $table['name'],
            'capacity' => (int)$table['capacity'],
            'visitorId' => $table['visitor_id'] ? (string)$table['visitor_id'] : null,
            'status' => $table['status'] ?? 'available'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching table by QR code');
    }
}

/**
 * 店舗コードからテーブル一覧取得（公開）
 */
function getTablesByShopCode($shopCode) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT st.*, s.code as shop_code, s.name as shop_name
            FROM shop_tables st
            INNER JOIN shops s ON st.shop_id = s.id
            WHERE s.code = :shop_code
            AND st.is_active = 1
            AND s.is_active = 1
            ORDER BY 
                CAST(st.table_number AS UNSIGNED) ASC,
                st.table_number ASC
        ");
        $stmt->execute([':shop_code' => $shopCode]);
        $tables = $stmt->fetchAll();
        
        $result = array_map(function($table) {
            return [
                'id' => (string)$table['id'],
                'shopId' => (string)$table['shop_id'],
                'shopCode' => $table['shop_code'],
                'shopName' => $table['shop_name'],
                'tableNumber' => $table['table_number'],
                'name' => $table['name'],
                'capacity' => (int)$table['capacity'],
                'visitorId' => $table['visitor_id'] ? (string)$table['visitor_id'] : null,
                'status' => $table['status'] ?? 'available'
            ];
        }, $tables);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching tables by shop code');
    }
}

/**
 * テーブル作成（認証必須、オーナー・マネージャーのみ）
 */
function createTable() {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        if (!$shopId) {
            sendValidationError(['shop_id' => 'Shop ID is required']);
        }
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['tableNumber'])) {
            sendValidationError([
                'tableNumber' => 'Table number is required'
            ]);
        }
        
        // テーブル番号の重複チェック
        $checkStmt = $pdo->prepare("
            SELECT id FROM shop_tables 
            WHERE shop_id = :shop_id AND table_number = :table_number
        ");
        $checkStmt->execute([
            ':shop_id' => $shopId,
            ':table_number' => $input['tableNumber']
        ]);
        if ($checkStmt->fetch()) {
            sendConflictError('Table number already exists in this shop');
        }
        
        // 店舗コードを取得（QRコードURL生成用）
        $shopStmt = $pdo->prepare("SELECT code FROM shops WHERE id = :id");
        $shopStmt->execute([':id' => $shopId]);
        $shop = $shopStmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // QRコードURLを生成
        // フロントエンドのベースURLを取得（環境変数またはデフォルト値）
        $baseUrl = isset($_SERVER['HTTP_HOST']) ? 
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : 
            'http://localhost:3000';
        
        $qrCodeUrl = $baseUrl . '/visitor?shop=' . urlencode($shop['code']) . '&table=' . urlencode($input['tableNumber']);
        
        $stmt = $pdo->prepare("
            INSERT INTO shop_tables 
            (shop_id, table_number, name, capacity, is_active, qr_code_url)
            VALUES 
            (:shop_id, :table_number, :name, :capacity, :is_active, :qr_code_url)
        ");
        
        $stmt->execute([
            ':shop_id' => $shopId,
            ':table_number' => $input['tableNumber'],
            ':name' => $input['name'] ?? null,
            ':capacity' => isset($input['capacity']) ? (int)$input['capacity'] : 4,
            ':is_active' => isset($input['isActive']) ? ($input['isActive'] ? 1 : 0) : 1,
            ':qr_code_url' => $qrCodeUrl
        ]);
        
        $tableId = $pdo->lastInsertId();
        
        // 作成したテーブル情報を返す
        getTable($tableId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'creating table');
    }
}

/**
 * テーブル更新（認証必須、オーナー・マネージャーのみ）
 */
function updateTable($tableId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // 既存のテーブル情報を取得
        $existingStmt = $pdo->prepare("SELECT shop_id FROM shop_tables WHERE id = :id");
        $existingStmt->execute([':id' => $tableId]);
        $existing = $existingStmt->fetch();
        
        if (!$existing) {
            sendNotFoundError('Table');
        }
        
        // 権限チェック：自分の店舗のテーブルのみ更新可能
        if ($shopId && $existing['shop_id'] != $shopId) {
            sendForbiddenError('You can only update tables from your own shop');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendValidationError(['data' => 'Request data is required']);
        }
        
        $updates = [];
        $params = [':id' => $tableId];
        
        if (isset($input['tableNumber'])) {
            // テーブル番号の重複チェック（自分以外）
            $checkStmt = $pdo->prepare("
                SELECT id FROM shop_tables 
                WHERE shop_id = :shop_id AND table_number = :table_number AND id != :id
            ");
            $checkStmt->execute([
                ':shop_id' => $existing['shop_id'],
                ':table_number' => $input['tableNumber'],
                ':id' => $tableId
            ]);
            if ($checkStmt->fetch()) {
                sendConflictError('Table number already exists in this shop');
            }
            
            $updates[] = "table_number = :table_number";
            $params[':table_number'] = $input['tableNumber'];
            
            // テーブル番号が変更された場合はQRコードURLも更新
            $shopStmt = $pdo->prepare("SELECT code FROM shops WHERE id = :id");
            $shopStmt->execute([':id' => $existing['shop_id']]);
            $shop = $shopStmt->fetch();
            
            if ($shop) {
                $baseUrl = isset($_SERVER['HTTP_HOST']) ? 
                    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : 
                    'http://localhost:3000';
                
                $qrCodeUrl = $baseUrl . '/visitor?shop=' . urlencode($shop['code']) . '&table=' . urlencode($input['tableNumber']);
                $updates[] = "qr_code_url = :qr_code_url";
                $params[':qr_code_url'] = $qrCodeUrl;
            }
        }
        
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $input['name'];
        }
        
        if (isset($input['capacity'])) {
            $updates[] = "capacity = :capacity";
            $params[':capacity'] = (int)$input['capacity'];
        }
        
        if (isset($input['isActive'])) {
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $input['isActive'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE shop_tables SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // 更新したテーブル情報を返す
        getTable($tableId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating table');
    }
}

/**
 * テーブル削除（認証必須、オーナー・マネージャーのみ）
 */
function deleteTable($tableId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // 既存のテーブル情報を取得
        $existingStmt = $pdo->prepare("SELECT shop_id FROM shop_tables WHERE id = :id");
        $existingStmt->execute([':id' => $tableId]);
        $existing = $existingStmt->fetch();
        
        if (!$existing) {
            sendNotFoundError('Table');
        }
        
        // 権限チェック：自分の店舗のテーブルのみ削除可能
        if ($shopId && $existing['shop_id'] != $shopId) {
            sendForbiddenError('You can only delete tables from your own shop');
        }
        
        // 削除（物理削除）
        $stmt = $pdo->prepare("DELETE FROM shop_tables WHERE id = :id");
        $stmt->execute([':id' => $tableId]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('Table');
        }
        
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting table');
    }
}

