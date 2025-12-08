<?php
/**
 * 店舗API
 * GET /api/shops - 店舗一覧取得（公開）
 * GET /api/shops/{code} - 店舗情報取得（公開）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 店舗コードの抽出
$shopCode = null;
if (preg_match('#/shops/([^/]+)#', $path, $matches)) {
    $shopCode = $matches[1];
}

switch ($method) {
    case 'GET':
        if ($shopCode) {
            getShop($shopCode);
        } else {
            getShops();
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * 店舗一覧取得（公開）
 */
function getShops() {
    try {
        $pdo = getDbConnection();
        
        $sql = "SELECT id, code, name, description, address, phone, is_active 
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
                'phone' => $shop['phone']
            ];
        }, $shops);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error fetching shops: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch shops']);
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
            http_response_code(404);
            echo json_encode(['error' => 'Shop not found']);
            return;
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
        error_log("Error fetching shop: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch shop']);
    }
}

