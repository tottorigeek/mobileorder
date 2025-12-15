<?php
/**
 * 来店情報管理API
 * GET /api/visitors?shop_id={shop_id} - 来店情報一覧取得（認証必須）
 * GET /api/visitors/{id} - 来店情報取得（認証必須）
 * POST /api/visitors - 来店情報作成（公開）
 * PUT /api/visitors/{id} - 来店情報更新（認証必須または公開）
 * PUT /api/visitors/{id}/checkout - 会計処理（公開）
 * PUT /api/visitors/{id}/payment - 支払い処理（公開）
 * PUT /api/visitors/{id}/set-complete - テーブルセット完了（認証必須、一般スタッフ可）
 * DELETE /api/visitors/{id} - visitor削除（認証必須、一般スタッフ可）
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
        
        case 'force-release':
            // テーブル強制開放（管理側用）
            if ($method === 'PUT') {
                forceReleaseVisitor($visitorId);
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
    
    case 'DELETE':
        if ($visitorId) {
            deleteVisitor($visitorId);
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
        
        // 認証チェック（スタッフの場合のみ、自分の店舗に限定）
        $token = getJWTFromHeader();
        if ($token) {
            $payload = verifyJWT($token);
            if ($payload) {
                $role = $payload['role'] ?? 'staff';
                // 通常スタッフのみ店舗制限をかける（オーナー/マネージャー/運営者は全店舗参照可）
                if ($role === 'staff' && isset($payload['shop_id'])) {
                    if ($visitor['shop_id'] != $payload['shop_id']) {
                        sendForbiddenError('You can only access visitors from your own shop');
                    }
                }
            }
        }
        
        $response = [
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
        
        // number_of_childrenカラムが存在する場合は追加
        if (isset($visitor['number_of_children'])) {
            $response['numberOfChildren'] = (int)$visitor['number_of_children'];
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
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
        // number_of_childrenカラムが存在するか確認
        $checkColumnStmt = $pdo->query("SHOW COLUMNS FROM visitors LIKE 'number_of_children'");
        $columnExists = $checkColumnStmt->rowCount() > 0;
        
        if ($columnExists) {
            // number_of_childrenカラムが存在する場合
            $numberOfChildren = isset($input['numberOfChildren']) ? (int)$input['numberOfChildren'] : 0;
            $stmt = $pdo->prepare("
                INSERT INTO visitors 
                (shop_id, table_id, table_number, number_of_guests, number_of_children, arrival_time)
                VALUES 
                (:shop_id, :table_id, :table_number, :number_of_guests, :number_of_children, NOW())
            ");
            
            $stmt->execute([
                ':shop_id' => (int)$input['shopId'],
                ':table_id' => $tableId,
                ':table_number' => $input['tableNumber'],
                ':number_of_guests' => (int)$input['numberOfGuests'],
                ':number_of_children' => $numberOfChildren
            ]);
        } else {
            // number_of_childrenカラムが存在しない場合（後方互換性のため）
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
        }
        
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

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Visitor created successfully (id=%d, shop_id=%d, table_id=%s, table_number=%s, guests=%d, children=%d)',
                $visitorId,
                (int)$input['shopId'],
                $tableId !== null ? (string)$tableId : 'null',
                $input['tableNumber'],
                (int)$input['numberOfGuests'],
                isset($numberOfChildren) ? (int)$numberOfChildren : 0
            )
        );
        
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

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Visitor updated successfully (id=%d) with fields: %s',
                $visitorId,
                implode(', ', array_keys($params))
            )
        );
        
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

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Checkout processed successfully for visitor (id=%d, total_amount=%d)',
                $visitorId,
                $totalAmount
            )
        );
        
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
        
        // 合計額を計算（注文から、checkout_timeが設定されていない場合のみ）
        $totalAmount = $visitor['total_amount'];
        if (!$visitor['checkout_time']) {
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
        }
        
        // visitorを更新（支払い完了、total_amountとcheckout_timeも設定）
        $updateStmt = $pdo->prepare("
            UPDATE visitors 
            SET payment_method = :payment_method,
                payment_status = 'completed',
                total_amount = :total_amount,
                checkout_time = COALESCE(checkout_time, NOW()),
                updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':id' => $visitorId,
            ':payment_method' => $input['paymentMethod'],
            ':total_amount' => $totalAmount
        ]);

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Payment processed successfully for visitor (id=%d, method=%s, total_amount=%d)',
                $visitorId,
                $input['paymentMethod'],
                $totalAmount
            )
        );
        
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
 * 一般スタッフでも自分の店舗のvisitorを操作可能
 */
function completeTableSet($visitorId) {
    try {
        // 認証チェック（一般スタッフでもアクセス可能）
        $auth = checkAuth();
        $userId = $auth['user_id'];
        $defaultShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // ユーザーが所属する店舗IDのリストを取得（複数店舗対応）
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
        
        // 権限チェック：自分の店舗のvisitorのみ操作可能
        if (!empty($userShopIds) && !in_array(intval($visitor['shop_id']), $userShopIds)) {
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

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Table set completed successfully for visitor (id=%d, shop_id=%s, table_id=%s)',
                $visitorId,
                isset($visitor['shop_id']) ? (string)$visitor['shop_id'] : 'null',
                isset($visitor['table_id']) ? (string)$visitor['table_id'] : 'null'
            )
        );
        
        // 更新したvisitor情報を返す
        getVisitor($visitorId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'completing table set');
    }
}

/**
 * visitor削除（認証必須、強制解除）
 * 一般スタッフでも自分の店舗のvisitorを削除可能
 */
function deleteVisitor($visitorId) {
    try {
        // 認証チェック（一般スタッフでもアクセス可能）
        $auth = checkAuth();
        $userId = $auth['user_id'];
        $defaultShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();
        
        if (!$visitor) {
            sendNotFoundError('Visitor');
        }
        
        // ユーザーが所属する店舗IDのリストを取得（複数店舗対応）
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
        
        // 権限チェック：自分の店舗のvisitorのみ削除可能
        if (!empty($userShopIds) && !in_array(intval($visitor['shop_id']), $userShopIds)) {
            sendForbiddenError('You can only delete visitors from your own shop');
        }
        
        // テーブルのvisitor_idをクリアし、ステータスをavailableに更新
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
        
        // visitorを削除
        $deleteStmt = $pdo->prepare("DELETE FROM visitors WHERE id = :id");
        $deleteStmt->execute([':id' => $visitorId]);

        // デバッグ用: 成功ログを記録
        logErrorToDatabase(
            'info',
            sprintf(
                'Visitor deleted successfully (id=%d, shop_id=%s, table_id=%s)',
                $visitorId,
                isset($visitor['shop_id']) ? (string)$visitor['shop_id'] : 'null',
                isset($visitor['table_id']) ? (string)$visitor['table_id'] : 'null'
            )
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Visitor deleted successfully'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting visitor');
    }
}

/**
 * visitor強制解除（認証必須）
 * - 管理画面などから、着座状態を強制的に解除する用途
 * - treatOrdersAsPaid=true の場合: 注文を売上計上しつつテーブルを空席にする
 * - treatOrdersAsPaid=false の場合: 未完了注文をキャンセル扱いにしてテーブルを空席にする
 */
function forceReleaseVisitor($visitorId) {
    try {
        // 認証チェック（ロールも取得）
        $auth = checkAuth();
        $userId = $auth['user_id'];
        $defaultShopId = $auth['shop_id'] ?? null;
        $role = $auth['role'] ?? 'staff';

        $pdo = getDbConnection();

        // visitor情報を取得
        $visitorStmt = $pdo->prepare("SELECT * FROM visitors WHERE id = :id");
        $visitorStmt->execute([':id' => $visitorId]);
        $visitor = $visitorStmt->fetch();

        if (!$visitor) {
            sendNotFoundError('Visitor');
        }

        // ユーザーが所属する店舗IDのリストを取得（複数店舗対応）
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

        // 権限チェック：通常スタッフは自分の店舗のみ操作可能
        // オーナー・マネージャー（およびそれ以外の上位ロール）は全店舗のvisitorを操作可能
        if ($role === 'staff') {
            if (!empty($userShopIds) && !in_array(intval($visitor['shop_id']), $userShopIds)) {
                sendForbiddenError('You can only force release tables from your own shop');
            }
        }

        // リクエストボディを取得（treatOrdersAsPaidフラグ）
        $input = json_decode(file_get_contents('php://input'), true);
        $treatOrdersAsPaid = isset($input['treatOrdersAsPaid']) ? (bool)$input['treatOrdersAsPaid'] : false;

        $pdo->beginTransaction();

        $shopId = (int)$visitor['shop_id'];
        $tableNumber = $visitor['table_number'];

        if ($treatOrdersAsPaid) {
            // ---- A: 注文を清算済み扱いにして席を空ける ----

            // 合計額を計算（キャンセル以外の注文）
            $totalAmount = 0;
            $orderSumStmt = $pdo->prepare("
                SELECT SUM(total_amount) as total
                FROM orders
                WHERE shop_id = :shop_id
                  AND table_number = :table_number
                  AND status != 'cancelled'
            ");
            $orderSumStmt->execute([
                ':shop_id' => $shopId,
                ':table_number' => $tableNumber
            ]);
            $orderTotal = $orderSumStmt->fetch();
            if ($orderTotal && $orderTotal['total']) {
                $totalAmount = (int)$orderTotal['total'];
            }

            // 進行中の注文ステータスをcompletedに更新
            $completeOrdersStmt = $pdo->prepare("
                UPDATE orders
                SET status = 'completed', updated_at = NOW()
                WHERE shop_id = :shop_id
                  AND table_number = :table_number
                  AND status IN ('pending','accepted','cooking','checkout_pending')
            ");
            $completeOrdersStmt->execute([
                ':shop_id' => $shopId,
                ':table_number' => $tableNumber
            ]);

            // visitorを支払い完了状態に更新（支払方法が未設定ならcashとする）
            $paymentMethod = $visitor['payment_method'] ?: 'cash';
            $updateVisitorStmt = $pdo->prepare("
                UPDATE visitors
                SET payment_method = :payment_method,
                    payment_status = 'completed',
                    total_amount = :total_amount,
                    checkout_time = COALESCE(checkout_time, NOW()),
                    is_set_completed = 1,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateVisitorStmt->execute([
                ':id' => $visitorId,
                ':payment_method' => $paymentMethod,
                ':total_amount' => $totalAmount
            ]);
        } else {
            // ---- B: 注文を清算せずキャンセル扱いにして席を空ける ----

            // 進行中の注文をキャンセル扱いに更新
            $cancelOrdersStmt = $pdo->prepare("
                UPDATE orders
                SET status = 'cancelled', updated_at = NOW()
                WHERE shop_id = :shop_id
                  AND table_number = :table_number
                  AND status IN ('pending','accepted','cooking','checkout_pending')
            ");
            $cancelOrdersStmt->execute([
                ':shop_id' => $shopId,
                ':table_number' => $tableNumber
            ]);

            // visitorを「売上ゼロの終了状態」に更新
            $updateVisitorStmt = $pdo->prepare("
                UPDATE visitors
                SET total_amount = 0,
                    payment_status = 'pending',
                    checkout_time = COALESCE(checkout_time, NOW()),
                    is_set_completed = 1,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $updateVisitorStmt->execute([
                ':id' => $visitorId
            ]);
        }

        // いずれの場合も、テーブルを空席にする
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

        // 成功ログ
        logErrorToDatabase(
            'info',
            sprintf(
                'Visitor force-released (id=%d, shop_id=%s, table_id=%s, treatOrdersAsPaid=%s)',
                $visitorId,
                isset($visitor['shop_id']) ? (string)$visitor['shop_id'] : 'null',
                isset($visitor['table_id']) ? (string)$visitor['table_id'] : 'null',
                $treatOrdersAsPaid ? 'true' : 'false'
            )
        );

        $pdo->commit();

        // 更新後のvisitor情報を返す
        getVisitor($visitorId);

    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        handleDatabaseError($e, 'force releasing visitor/table');
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendErrorResponse(500, 'Internal server error');
    }
}

