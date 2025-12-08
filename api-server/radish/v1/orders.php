<?php
/**
 * 注文API
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

// v1/index.php経由で呼び出された場合、環境変数から残りのパスを取得
if (isset($_ENV['ORDERS_REMAINING_PATH']) && !empty($_ENV['ORDERS_REMAINING_PATH'])) {
    $remainingPath = $_ENV['ORDERS_REMAINING_PATH'];
    // 残りのパスからIDを抽出
    if (preg_match('#^(\d+)/?$#', $remainingPath, $matches)) {
        $orderId = $matches[1];
    }
} else {
    // 直接呼び出された場合、/radish/v1/orders/{id} または /radish/api/orders/{id} の形式からIDを抽出
    if (preg_match('#/orders/(\d+)/?$#', $path, $matches)) {
        $orderId = $matches[1];
    }
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
            sendErrorResponse(400, 'Order ID required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * 注文一覧取得
 * 店舗スタッフのみ（認証必須）、またはクエリパラメータで店舗コード指定の場合は公開（顧客側の注文確認用）
 */
function getOrders() {
    try {
        $pdo = getDbConnection();
        
        // 店舗IDの取得
        // 1. 認証されている場合はJWTから取得（店舗スタッフ）
        // 2. 認証されていない場合はクエリパラメータからshop_codeを取得（顧客側）
        $shopId = null;
        $token = getJWTFromHeader();
        
        if ($token) {
            // 認証済みの場合：JWTからshop_idを取得
            $payload = verifyJWT($token);
            if ($payload && isset($payload['shop_id'])) {
                $shopId = $payload['shop_id'];
            }
        }
        
        if (!$shopId) {
            // 認証されていない場合：クエリパラメータからshop_codeを取得
            $shopCode = $_GET['shop'] ?? null;
            
            if (!$shopCode) {
                sendErrorResponse(400, 'Shop code is required for unauthenticated requests');
            }
            
            // shop_codeからshop_idを取得
            $stmt = $pdo->prepare("SELECT id FROM shops WHERE code = :code AND is_active = 1");
            $stmt->execute([':code' => $shopCode]);
            $shop = $stmt->fetch();
            
            if (!$shop) {
                sendNotFoundError('Shop');
            }
            
            $shopId = $shop['id'];
        }
        
        // ステータスフィルター（オプション）
        $status = $_GET['status'] ?? null;
        
        // テーブル番号フィルター（オプション、顧客側の注文確認用）
        $tableNumber = $_GET['tableNumber'] ?? null;
        
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
                WHERE o.shop_id = :shop_id";
        
        $params = [':shop_id' => $shopId];
        
        if ($status) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $status;
        }
        
        if ($tableNumber) {
            $sql .= " AND o.table_number = :table_number";
            $params[':table_number'] = $tableNumber;
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // データ形式を変換
        $result = array_map(function($order) {
            $items = json_decode('[' . $order['items_json'] . ']', true) ?: [];
            
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
        handleDatabaseError($e, 'fetching orders');
    }
}

/**
 * 注文取得（単一）
 * 認証されている場合は同じ店舗の注文のみ取得可能、認証されていない場合は公開
 */
function getOrder($orderId) {
    try {
        $pdo = getDbConnection();
        
        // 認証チェック（オプション）
        $token = getJWTFromHeader();
        $shopId = null;
        
        if ($token) {
            // 認証済みの場合：JWTからshop_idを取得して検証
            $payload = verifyJWT($token);
            if ($payload && isset($payload['shop_id'])) {
                $shopId = $payload['shop_id'];
            }
        }
        
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
                WHERE o.id = :id";
        
        $params = [':id' => $orderId];
        
        // 認証されている場合は同じ店舗の注文のみ取得可能
        if ($shopId) {
            $sql .= " AND o.shop_id = :shop_id";
            $params[':shop_id'] = $shopId;
        }
        
        $sql .= " GROUP BY o.id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendNotFoundError('Order');
        }
        
        $items = json_decode('[' . $order['items_json'] . ']', true) ?: [];
        
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
        handleDatabaseError($e, 'fetching order');
    }
}

/**
 * 注文作成
 * 一般顧客もQRコードでテーブル番号を読み込むことができたら注文可能
 */
function createOrder() {
    try {
        $pdo = getDbConnection();
        
        // リクエストボディの取得
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['tableNumber']) || !isset($input['items'])) {
            sendValidationError([
                'tableNumber' => 'Table number is required',
                'items' => 'Items are required'
            ]);
        }
        
        // 店舗IDの取得
        // 1. 認証されている場合はJWTから取得
        // 2. 認証されていない場合はリクエストボディまたはクエリパラメータからshop_codeを取得
        $shopId = null;
        $token = getJWTFromHeader();
        
        if ($token) {
            // 認証済みの場合：JWTからshop_idを取得
            $payload = verifyJWT($token);
            if ($payload && isset($payload['shop_id'])) {
                $shopId = $payload['shop_id'];
            }
        }
        
        if (!$shopId) {
            // 認証されていない場合：リクエストボディまたはクエリパラメータからshop_codeを取得
            $shopCode = $input['shopCode'] ?? $_GET['shop'] ?? null;
            
            if (!$shopCode) {
                sendErrorResponse(400, 'Shop code is required for unauthenticated requests');
            }
            
            // shop_codeからshop_idを取得
            $stmt = $pdo->prepare("SELECT id FROM shops WHERE code = :code AND is_active = 1");
            $stmt->execute([':code' => $shopCode]);
            $shop = $stmt->fetch();
            
            if (!$shop) {
                sendNotFoundError('Shop');
            }
            
            $shopId = $shop['id'];
        }
        
        $pdo->beginTransaction();
        
        // 注文番号の生成
        $orderNumber = 'ORD-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
        
        // 注文の作成（shop_idを含める）
        $sql = "INSERT INTO orders (shop_id, order_number, table_number, status, total_amount, created_at, updated_at)
                VALUES (:shop_id, :order_number, :table_number, 'pending', :total_amount, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':shop_id' => $shopId,
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
        handleDatabaseError($e, 'creating order');
    }
}

/**
 * 注文ステータス更新
 * 店舗スタッフのみ（認証必須）
 */
function updateOrderStatus($orderId) {
    try {
        // 認証チェック（店舗スタッフのみ）
        $auth = checkAuth();
        $shopId = $auth['shop_id'];
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['status'])) {
            sendValidationError(['status' => 'Status is required']);
        }
        
        $allowedStatuses = ['pending', 'accepted', 'cooking', 'completed', 'cancelled'];
        if (!in_array($input['status'], $allowedStatuses)) {
            sendValidationError(['status' => 'Invalid status. Allowed values: ' . implode(', ', $allowedStatuses)]);
        }
        
        // 同じ店舗の注文のみ更新可能
        $sql = "UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id AND shop_id = :shop_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':status' => $input['status'],
            ':id' => $orderId,
            ':shop_id' => $shopId
        ]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('Order');
        }
        
        // 更新した注文を返す
        getOrder($orderId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating order status');
    }
}

