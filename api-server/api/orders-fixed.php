<?php
/**
 * 注文API（修正版）
 * GET /api/orders - 注文一覧取得
 * POST /api/orders - 注文作成
 * PUT /api/orders/{id} - 注文ステータス更新
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];

// パスの取得と処理
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$orderId = null;

// /radish/api/orders/{id} の形式からIDを抽出
// /orders/ の後に数字がある場合のみIDとして抽出
if (preg_match('#/orders/(\d+)/?$#', $path, $matches)) {
    $orderId = $matches[1];
}

switch ($method) {
    case 'GET':
        if ($orderId) {
            getOrder($orderId);
        } else {
            getOrders();
        }
        break;
    
    case 'POST':
        createOrder();
        break;
    
    case 'PUT':
        if ($orderId) {
            updateOrderStatus($orderId);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID required']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * 注文一覧取得
 */
function getOrders() {
    try {
        $pdo = getDbConnection();
        
        // ステータスフィルター（オプション）
        $status = $_GET['status'] ?? null;
        
        $sql = "SELECT o.*, 
                       GROUP_CONCAT(
                           JSON_OBJECT(
                               'menuId', oi.menu_id,
                               'menuNumber', oi.menu_number,
                               'menuName', oi.menu_name,
                               'quantity', oi.quantity,
                               'price', oi.price
                           )
                       ) as items_json
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // データが空の場合は空配列を返す
        if (empty($orders)) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // データ形式を変換
        $result = array_map(function($order) {
            // items_jsonがnullの場合は空配列
            $itemsJson = $order['items_json'] ?? null;
            $items = $itemsJson ? json_decode('[' . $itemsJson . ']', true) : [];
            if (!is_array($items)) {
                $items = [];
            }
            
            return [
                'id' => (string)$order['id'],
                'orderNumber' => $order['order_number'],
                'tableNumber' => $order['table_number'],
                'items' => $items,
                'status' => $order['status'],
                'totalAmount' => (int)$order['total_amount'],
                'createdAt' => $order['created_at'],
                'updatedAt' => $order['updated_at']
            ];
        }, $orders);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch orders', 'message' => $e->getMessage()]);
    }
}

/**
 * 注文取得（単一）
 */
function getOrder($orderId) {
    try {
        $pdo = getDbConnection();
        
        $sql = "SELECT o.*, 
                       GROUP_CONCAT(
                           JSON_OBJECT(
                               'menuId', oi.menu_id,
                               'menuNumber', oi.menu_number,
                               'menuName', oi.menu_name,
                               'quantity', oi.quantity,
                               'price', oi.price
                           )
                       ) as items_json
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.id = :id
                GROUP BY o.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }
        
        $itemsJson = $order['items_json'] ?? null;
        $items = $itemsJson ? json_decode('[' . $itemsJson . ']', true) : [];
        if (!is_array($items)) {
            $items = [];
        }
        
        $result = [
            'id' => (string)$order['id'],
            'orderNumber' => $order['order_number'],
            'tableNumber' => $order['table_number'],
            'items' => $items,
            'status' => $order['status'],
            'totalAmount' => (int)$order['total_amount'],
            'createdAt' => $order['created_at'],
            'updatedAt' => $order['updated_at']
        ];
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch order', 'message' => $e->getMessage()]);
    }
}

/**
 * 注文作成
 */
function createOrder() {
    try {
        $pdo = getDbConnection();
        
        // リクエストボディの取得
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['tableNumber']) || !isset($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // 注文番号の生成
        $orderNumber = 'ORD-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
        
        // 注文の作成
        $sql = "INSERT INTO orders (order_number, table_number, status, total_amount, created_at, updated_at)
                VALUES (:order_number, :table_number, 'pending', :total_amount, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':table_number' => $input['tableNumber'],
            ':total_amount' => $input['totalAmount']
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // 注文アイテムの作成
        $itemSql = "INSERT INTO order_items (order_id, menu_id, menu_number, menu_name, quantity, price)
                    VALUES (:order_id, :menu_id, :menu_number, :menu_name, :quantity, :price)";
        
        $itemStmt = $pdo->prepare($itemSql);
        
        foreach ($input['items'] as $item) {
            $itemStmt->execute([
                ':order_id' => $orderId,
                ':menu_id' => $item['menuId'],
                ':menu_number' => $item['menuNumber'],
                ':menu_name' => $item['menuName'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }
        
        $pdo->commit();
        
        // 作成した注文を返す
        getOrder($orderId);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error creating order: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create order', 'message' => $e->getMessage()]);
    }
}

/**
 * 注文ステータス更新
 */
function updateOrderStatus($orderId) {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            return;
        }
        
        $allowedStatuses = ['pending', 'accepted', 'cooking', 'completed', 'cancelled'];
        if (!in_array($input['status'], $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            return;
        }
        
        $sql = "UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $input['status'],
            ':id' => $orderId
        ]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }
        
        // 更新した注文を返す
        getOrder($orderId);
        
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status', 'message' => $e->getMessage()]);
    }
}

