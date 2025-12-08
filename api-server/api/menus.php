<?php
/**
 * メニューAPI
 * GET /api/menus - メニュー一覧取得
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getMenus();
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * メニュー一覧取得
 */
function getMenus() {
    try {
        $pdo = getDbConnection();
        
        // 店舗IDの取得
        $shopId = getShopId();
        if (!$shopId) {
            http_response_code(400);
            echo json_encode(['error' => 'Shop ID or shop code is required']);
            return;
        }
        
        // カテゴリフィルター（オプション）
        $category = $_GET['category'] ?? null;
        
        $sql = "SELECT * FROM menus WHERE shop_id = :shop_id AND is_available = 1";
        $params = [':shop_id' => $shopId];
        
        if ($category) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }
        
        $sql .= " ORDER BY number ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $menus = $stmt->fetchAll();
        
        // データ形式を変換
        $result = array_map(function($menu) {
            return [
                'id' => (string)$menu['id'],
                'number' => $menu['number'],
                'name' => $menu['name'],
                'description' => $menu['description'],
                'price' => (int)$menu['price'],
                'category' => $menu['category'],
                'imageUrl' => $menu['image_url'] ?: null,
                'isAvailable' => (bool)$menu['is_available'],
                'isRecommended' => (bool)$menu['is_recommended']
            ];
        }, $menus);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error fetching menus: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch menus']);
    }
}

