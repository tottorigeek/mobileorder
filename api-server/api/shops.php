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

// パスの解析
$pathParts = explode('/', trim(str_replace('/radish/api/shops/', '', $path), '/'));
$identifier = $pathParts[0] ?? null;

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
        
        $sql = "SELECT id, code, name, description, address, phone, email, max_tables, is_active 
                FROM shops 
                WHERE is_active = 1 
                ORDER BY name ASC";
        
        $stmt = $pdo->query($sql);
        $shops = $stmt->fetchAll();
        
        $result = array_map(function($shop) {
            return [
                'id' => (string)$shop['id'],
                'code' => $shop['code'],
                'name' => $shop['name'],
                'description' => $shop['description'],
                'address' => $shop['address'],
                'phone' => $shop['phone'],
                'email' => $shop['email'],
                'maxTables' => (int)$shop['max_tables'],
                'isActive' => (bool)$shop['is_active']
            ];
        }, $shops);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching shops');
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
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
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
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
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

