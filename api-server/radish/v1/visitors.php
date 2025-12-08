<?php
/**
 * 来店情報管理API
 * GET /api/visitors?shop_id={shop_id} - 来店情報一覧取得（認証必須）
 * GET /api/visitors/{id} - 来店情報取得（認証必須）
 * POST /api/visitors - 来店情報作成（公開）
 * PUT /api/visitors/{id} - 来店情報更新（認証必須または公開）
 * PUT /api/visitors/{id}/checkout - 会計処理（公開）
 * PUT /api/visitors/{id}/payment - 支払い処理（公開）
 * PUT /api/visitors/{id}/set-complete - テーブルセット完了（認証必須）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析（index.php経由で呼び出される場合を考慮）
// v1/index.php経由で呼び出された場合、環境変数から残りのパスを取得
if (isset($_ENV['VISITORS_REMAINING_PATH']) && !empty($_ENV['VISITORS_REMAINING_PATH'])) {
    $path = $_ENV['VISITORS_REMAINING_PATH'];
} else {
    // 直接呼び出された場合、/radish/v1/visitors/ または /radish/api/visitors/ を削除
    $path = preg_replace('#^/radish/(v1|api)/visitors/#', '', $path);
}
$path = trim($path, '/');
$pathParts = explode('/', $path);

$visitorId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
$action = isset($pathParts[1]) ? $pathParts[1] : null;

// アクション別の処理
if ($visitorId && $action) {
    switch ($action) {
        case 'checkout':
            if ($method === 'PUT') {
                processCheckout($visitorId);
            } else {
                sendErrorResponse(405, 'Method not allowed');
            }
            exit;
        
        case 'payment':
            if ($method === 'PUT') {
                processPayment($visitorId);
            } else {
                sendErrorResponse(405, 'Method not allowed');
            }
            exit;
        
        case 'set-complete':
            if ($method === 'PUT') {
                completeTableSet($visitorId);
            } else {
                sendErrorResponse(405, 'Method not allowed');
            }
            exit;
    }
}

switch ($method) {
    case 'GET':
        if ($visitorId) {
            getVisitor($visitorId);
        } else {
            getVisitors();
        }
        break;
    
    case 'POST':
        createVisitor();
        break;
    
    case 'PUT':
        if ($visitorId) {
            updateVisitor($visitorId);
        } else {
            sendErrorResponse(400, 'Visitor ID is required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * 来店情報一覧取得（認証必須）
 */
function getVisitors() {
    try {
        $pdo = getDbConnection();
        
        // 認証チェック（スタッフ向け）
        $token = getJWTFromHeader();
        $shopId = null;
        
        if ($token) {
            $payload = verifyJWT($token);
            if ($payload && isset($payload['shop_id'])) {
                $shopId = $payload['shop_id'];
            }
        }
        
        // クエリパラメータからshop_idを取得（認証されていない場合）
        if (!$shopId) {
            $shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;
        }
        
        if (!$shopId) {
            sendValidationError(['shop_id' => 'Shop ID is required']);
        }
        
        // フィルター条件
        $tableId = isset($_GET['table_id']) ? (int)$_GET['table_id'] : null;
        $tableNumber = $_GET['table_number'] ?? null;
        $paymentStatus = $_GET['payment_status'] ?? null;
        $isSetCompleted = isset($_GET['is_set_completed']) ? (int)$_GET['is_set_completed'] : null;
        
        $sql = "SELECT v.*, st.id as table_id_value
                FROM visitors v
                LEFT JOIN shop_tables st ON v.table_id = st.id
                WHERE v.shop_id = :shop_id";
        
        $params = [':shop_id' => $shopId];
        
        if ($tableId) {
            $sql .= " AND v.table_id = :table_id";
            $params[':table_id'] = $tableId;
        }
        
        if ($tableNumber) {
            $sql .= " AND v.table_number = :table_number";
            $params[':table_number'] = $tableNumber;
        }
        
        if ($paymentStatus) {
            $sql .= " AND v.payment_status = :payment_status";
            $params[':payment_status'] = $paymentStatus;
        }
        
        if ($isSetCompleted !== null) {
            $sql .= " AND v.is_set_completed = :is_set_completed";
            $params[':is_set_completed'] = $isSetCompleted;
        }
        
        $sql .= " ORDER BY v.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $visitors = $stmt->fetchAll();
        
        $result = array_map(function($visitor) {
            return [
                'id' => (string)$visitor['id'],
                'shopId' => (string)$visitor['shop_id'],
                'tableId' => $visitor['table_id'] ? (string)$visitor['table_id'] : null,
                'tableNumber' => $visitor['table_number'],
                'numberOfGuests' => (int)$visitor['number_of_guests'],
                'arrivalTime' => $visitor['arrival_time'],
                'checkoutTime' => $visitor['checkout_time'],
                'totalAmount' => (int)$visitor['total_amount'],
                'paymentMethod' => $visitor['payment_method'],
                'paymentStatus' => $visitor['payment_status'],
                'isSetCompleted' => (bool)$visitor['is_set_completed'],
                'createdAt' => $visitor['created_at'],
                'updatedAt' => $visitor['updated_at']
            ];
        }, $visitors);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching visitors');
    }
}

/**
 * 来店情報取得（単一）
 */
function getVisitor($visitorId) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $stmt->execute([':id' => $visitorId]);
        $visitor = $stmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // 認証チェック（スタッフの場合、自分の店舗のみ）
        $token = getJWTFromHeader();
        if ($token) {
            $payload = verifyJWT($token);
            if ($payload && isset($payload['shop_id'])) {
                if ($visitor['shop_id'] != $payload['shop_id']) {
                    sendForbiddenError('You can only access visitors from your own shop');
                }
            }
        }
        
        echo json_encode([
            'id' => (string)$visitor['id'],
            'shopId' => (string)$visitor['shop_id'],
            'tableId' => $visitor['table_id'] ? (string)$visitor['table_id'] : null,
            'tableNumber' => $visitor['table_number'],
            'numberOfGuests' => (int)$visitor['number_of_guests'],
            'arrivalTime' => $visitor['arrival_time'],
            'checkoutTime' => $visitor['checkout_time'],
            'totalAmount' => (int)$visitor['total_amount'],
            'paymentMethod' => $visitor['payment_method'],
            'paymentStatus' => $visitor['payment_status'],
            'isSetCompleted' => (bool)$visitor['is_set_completed'],
            'createdAt' => $visitor['created_at'],
            'updatedAt' => $visitor['updated_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching visitor');
    }
}

/**
 * 来店情報作成（公開）
 */
function createVisitor() {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['shopId']) || !isset($input['tableNumber']) || !isset($input['numberOfGuests'])) {
            sendValidationError([
                'shopId' => 'Shop ID is required',
                'tableNumber' => 'Table number is required',
                'numberOfGuests' => 'Number of guests is required'
            ]);
        }
        
        // 店舗の存在確認
        $shopStmt = $pdo->prepare("SELECT id FROM shops WHERE id = :id AND is_active = 1");
        $shopStmt->execute([':id' => (int)$input['shopId']]);
        $shop = $shopStmt->fetch();
        
        if (!$shop) {
            sendNotFoundError('Shop');
        }
        
        // テーブル情報を取得（オプション）
        $tableId = null;
        if (isset($input['tableId'])) {
            $tableId = (int)$input['tableId'];
        } else {
            // table_numberからtable_idを取得
            $tableStmt = $pdo->prepare("
                SELECT id FROM shop_tables 
                WHERE shop_id = :shop_id AND table_number = :table_number AND is_active = 1
            ");
            $tableStmt->execute([
                ':shop_id' => (int)$input['shopId'],
                ':table_number' => $input['tableNumber']
            ]);
            $table = $tableStmt->fetch();
            if ($table) {
                $tableId = (int)$table['id'];
            }
        }
        
        // visitorを作成
        $stmt = $pdo->prepare("
            INSERT INTO visitors 
            (shop_id, table_id, table_number, number_of_guests, arrival_time)
            VALUES 
            (:shop_id, :table_id, :table_number, :number_of_guests, NOW())
        ");
        
        $stmt->execute([
            ':shop_id' => (int)$input['shopId'],
            ':table_id' => $tableId,
            ':table_number' => $input['tableNumber'],
            ':number_of_guests' => (int)$input['numberOfGuests']
        ]);
        
        $visitorId = $pdo->lastInsertId();
        
        // テーブルにvisitor_idを設定し、ステータスをoccupiedに更新
        if ($tableId) {
            $updateTableStmt = $pdo->prepare("
                UPDATE shop_tables 
                SET visitor_id = :visitor_id, status = 'occupied', updated_at = NOW()
                WHERE id = :table_id
            ");
            $updateTableStmt->execute([
                ':visitor_id' => $visitorId,
                ':table_id' => $tableId
            ]);
        }
        
        // 作成したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'creating visitor');
    }
}

/**
 * 来店情報更新
 */
function updateVisitor($visitorId) {
    try {
        $pdo = getDbConnection();
        
        // 既存のvisitor情報を取得
        $existingStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $existingStmt->execute([':id' => $visitorId]);
        $existing = $existingStmt->fetch();
        
        if (!$existing) {
            sendNotFoundError('Visitor');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendValidationError(['data' => 'Request data is required']);
        }
        
        $updates = [];
        $params = [':id' => $visitorId];
        
        if (isset($input['tableId'])) {
            $updates[] = "table_id = :table_id";
            $params[':table_id'] = (int)$input['tableId'];
        }
        
        if (isset($input['numberOfGuests'])) {
            $updates[] = "number_of_guests = :number_of_guests";
            $params[':number_of_guests'] = (int)$input['numberOfGuests'];
        }
        
        if (isset($input['totalAmount'])) {
            $updates[] = "total_amount = :total_amount";
            $params[':total_amount'] = (int)$input['totalAmount'];
        }
        
        if (isset($input['paymentMethod'])) {
            $updates[] = "payment_method = :payment_method";
            $params[':payment_method'] = $input['paymentMethod'];
        }
        
        if (isset($input['paymentStatus'])) {
            $updates[] = "payment_status = :payment_status";
            $params[':payment_status'] = $input['paymentStatus'];
        }
        
        if (isset($input['isSetCompleted'])) {
            $updates[] = "is_set_completed = :is_set_completed";
            $params[':is_set_completed'] = $input['isSetCompleted'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE visitors SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // 更新したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating visitor');
    }
}

/**
 * 会計処理（公開）
 */
function processCheckout($visitorId) {
    try {
        $pdo = getDbConnection();
        
        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // 合計額を計算（注文から）
        $totalAmount = 0;
        $orderStmt = $pdo->prepare("
            SELECT SUM(total_amount) as total 
            FROM orders 
            WHERE shop_id = :shop_id 
            AND table_number = :table_number 
            AND status != 'cancelled'
        ");
        $orderStmt->execute([
            ':shop_id' => $visitor['shop_id'],
            ':table_number' => $visitor['table_number']
        ]);
        $orderTotal = $orderStmt->fetch();
        if ($orderTotal && $orderTotal['total']) {
            $totalAmount = (int)$orderTotal['total'];
        }
        
        // visitorを更新
        $updateStmt = $pdo->prepare("
            UPDATE visitors 
            SET checkout_time = NOW(), 
                total_amount = :total_amount,
                updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':id' => $visitorId,
            ':total_amount' => $totalAmount
        ]);
        
        // テーブルのステータスをcheckout_pendingに更新
        if ($visitor['table_id']) {
            $tableStmt = $pdo->prepare("
                UPDATE shop_tables 
                SET status = 'checkout_pending', updated_at = NOW()
                WHERE id = :table_id
            ");
            $tableStmt->execute([':table_id' => $visitor['table_id']]);
        }
        
        // 更新したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'processing checkout');
    }
}

/**
 * 支払い処理（公開）
 */
function processPayment($visitorId) {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['paymentMethod'])) {
            sendValidationError(['paymentMethod' => 'Payment method is required']);
        }
        
        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // visitorを更新（支払い完了）
        $updateStmt = $pdo->prepare("
            UPDATE visitors 
            SET payment_method = :payment_method,
                payment_status = 'completed',
                updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':id' => $visitorId,
            ':payment_method' => $input['paymentMethod']
        ]);
        
        // テーブルのステータスをset_pendingに更新
        if ($visitor['table_id']) {
            $tableStmt = $pdo->prepare("
                UPDATE shop_tables 
                SET status = 'set_pending', updated_at = NOW()
                WHERE id = :table_id
            ");
            $tableStmt->execute([':table_id' => $visitor['table_id']]);
        }
        
        // 更新したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'processing payment');
    }
}

/**
 * テーブルセット完了（認証必須）
 */
function completeTableSet($visitorId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $shopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // 権限チェック：自分の店舗のvisitorのみ
        if ($shopId && $visitor['shop_id'] != $shopId) {
            sendForbiddenError('You can only complete table sets for your own shop');
        }
        
        // visitorを更新
        $updateStmt = $pdo->prepare("
            UPDATE visitors 
            SET is_set_completed = 1, updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([':id' => $visitorId]);
        
        // テーブルのステータスをavailableに更新し、visitor_idをクリア
        if ($visitor['table_id']) {
            $tableStmt = $pdo->prepare("
                UPDATE shop_tables 
                SET status = 'available', 
                    visitor_id = NULL, 
                    updated_at = NOW()
                WHERE id = :table_id
            ");
            $tableStmt->execute([':table_id' => $visitor['table_id']]);
        }
        
        // 更新したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'completing table set');
    }
}

