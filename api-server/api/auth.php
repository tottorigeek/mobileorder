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
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    
    case 'logout':
        if ($method === 'POST') {
            logout();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    
    case 'me':
        if ($method === 'GET') {
            getCurrentUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
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
            http_response_code(400);
            echo json_encode(['error' => 'Username and password are required']);
            return;
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
        
        // ユーザーが存在しない場合
        if (!$userCheck) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Invalid credentials',
                'debug' => 'User not found'
            ]);
            return;
        }
        
        // ユーザーが無効化されている場合
        if (!$userCheck['is_active']) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Invalid credentials',
                'debug' => 'User is inactive'
            ]);
            return;
        }
        
        // 店舗が存在しない、または無効化されている場合
        if (!$userCheck['shop_id'] || !$userCheck['shop_active']) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Invalid credentials',
                'debug' => 'Shop not found or inactive',
                'shop_id' => $userCheck['shop_id'],
                'shop_active' => $userCheck['shop_active']
            ]);
            return;
        }
        
        // パスワード検証
        if (!password_verify($input['password'], $userCheck['password_hash'])) {
            http_response_code(401);
            echo json_encode([
                'error' => 'Invalid credentials',
                'debug' => 'Password mismatch'
            ]);
            return;
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
        
        // セッションに保存
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['shop_id'] = $user['shop_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        // 最終ログイン日時を更新
        $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);
        
        echo json_encode([
            'success' => true,
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
        error_log("Login error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Login failed']);
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
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
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
        error_log("Get user error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to get user']);
    }
}

