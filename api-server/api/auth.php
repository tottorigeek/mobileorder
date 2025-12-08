<?php
/**
 * 認証API
 * POST /api/auth/login - ログイン
 * POST /api/auth/logout - ログアウト
 * GET /api/auth/me - 現在のユーザー情報取得
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/radish/api/auth/', '', $path);
$path = trim($path, '/');

switch ($path) {
    case 'login':
        if ($method === 'POST') {
            login();
        } else {
            sendErrorResponse(405, 'Method not allowed');
        }
        break;
    
    case 'logout':
        if ($method === 'POST') {
            logout();
        } else {
            sendErrorResponse(405, 'Method not allowed');
        }
        break;
    
    case 'me':
        if ($method === 'GET') {
            getCurrentUser();
        } else {
            sendErrorResponse(405, 'Method not allowed');
        }
        break;
    
    default:
        sendErrorResponse(404, 'Endpoint not found');
        break;
}

/**
 * ログイン
 */
function login() {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['username']) || !isset($input['password'])) {
            sendValidationError([
                'username' => 'Username is required',
                'password' => 'Password is required'
            ]);
        }
        
        // まずユーザーの存在確認（店舗情報も含む）
        $checkStmt = $pdo->prepare("
            SELECT u.*, s.code as shop_code, s.name as shop_name, s.is_active as shop_active
            FROM users u
            LEFT JOIN shops s ON u.shop_id = s.id
            WHERE u.username = :username
        ");
        $checkStmt->execute([':username' => $input['username']]);
        $userCheck = $checkStmt->fetch();
        
        // ユーザーが存在しない場合、無効化されている場合、店舗が無効化されている場合、パスワードが一致しない場合
        // セキュリティ上の理由で、すべて同じエラーメッセージを返す
        if (!$userCheck || 
            !$userCheck['is_active'] || 
            !$userCheck['shop_id'] || 
            !$userCheck['shop_active'] || 
            !password_verify($input['password'], $userCheck['password_hash'])) {
            sendUnauthorizedError('Invalid credentials');
        }
        
        // ログイン成功 - ユーザー情報を取得
        $stmt = $pdo->prepare("
            SELECT u.*, s.code as shop_code, s.name as shop_name 
            FROM users u
            INNER JOIN shops s ON u.shop_id = s.id
            WHERE u.username = :username 
            AND u.is_active = 1 
            AND s.is_active = 1
        ");
        $stmt->execute([':username' => $input['username']]);
        $user = $stmt->fetch();
        
        // JWTトークンを生成
        $tokenPayload = [
            'user_id' => $user['id'],
            'shop_id' => $user['shop_id'],
            'role' => $user['role'],
            'username' => $user['username']
        ];
        $token = generateJWT($tokenPayload);
        
        // セッションにも保存（後方互換性のため）
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['shop_id'] = $user['shop_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        // 最終ログイン日時を更新
        $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => (string)$user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email'] ?? null,
                'role' => $user['role'],
                'shop' => [
                    'id' => (string)$user['shop_id'],
                    'code' => $user['shop_code'],
                    'name' => $user['shop_name']
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'login');
    }
}

/**
 * ログアウト
 */
function logout() {
    session_destroy();
    echo json_encode(['success' => true]);
}

/**
 * 現在のユーザー情報取得
 */
function getCurrentUser() {
    $auth = checkAuth();
    
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT u.id, u.shop_id, u.username, u.name, u.email, u.role, u.is_active,
                   s.code as shop_code, s.name as shop_name 
            FROM users u
            INNER JOIN shops s ON u.shop_id = s.id
            WHERE u.id = :user_id
        ");
        $stmt->execute([':user_id' => $auth['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendNotFoundError('User');
        }
        
        echo json_encode([
            'id' => (string)$user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'] ?? null,
            'role' => $user['role'],
            'shop' => [
                'id' => (string)$user['shop_id'],
                'code' => $user['shop_code'],
                'name' => $user['shop_name']
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'getting user');
    }
}

