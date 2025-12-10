<?php
/**
 * 認証API
 * POST /api/auth/login - ログイン
 * POST /api/auth/logout - ログアウト
 * GET /api/auth/me - 現在のユーザー情報取得
 * POST /api/auth/forgot-password - パスワードリセットメール送信
 * POST /api/auth/reset-password - パスワードリセット実行
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// /radish/v1/auth/ または /radish/api/auth/ を削除
$path = preg_replace('#^/radish/(v1|api)/auth/#', '', $path);
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
    
    case 'forgot-password':
        if ($method === 'POST') {
            forgotPassword();
        } else {
            sendErrorResponse(405, 'Method not allowed');
        }
        break;
    
    case 'reset-password':
        if ($method === 'POST') {
            resetPassword();
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
        // shop_activeがNULLの場合は店舗が存在しないことを意味するため、ログインを拒否
        if (!$userCheck || 
            !$userCheck['is_active'] || 
            !$userCheck['shop_id'] || 
            ($userCheck['shop_active'] === null || !$userCheck['shop_active']) || 
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

/**
 * パスワードリセットメール送信
 */
function forgotPassword() {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['username']) && !isset($input['email'])) {
            sendValidationError([
                'username' => 'Username or email is required'
            ]);
        }
        
        // ユーザー名またはメールアドレスでユーザーを検索
        if (isset($input['username'])) {
            $stmt = $pdo->prepare("
                SELECT u.*, s.is_active as shop_active
                FROM users u
                LEFT JOIN shops s ON u.shop_id = s.id
                WHERE u.username = :search_value
                AND u.is_active = 1
            ");
            $stmt->execute([':search_value' => $input['username']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT u.*, s.is_active as shop_active
                FROM users u
                LEFT JOIN shops s ON u.shop_id = s.id
                WHERE u.email = :search_value
                AND u.is_active = 1
            ");
            $stmt->execute([':search_value' => $input['email']]);
        }
        $user = $stmt->fetch();
        
        // セキュリティ上の理由で、ユーザーが存在しない場合でも成功メッセージを返す
        if (!$user || !$user['shop_active'] || !$user['email']) {
            // ユーザーが存在しない、またはメールアドレスが登録されていない場合でも
            // 成功メッセージを返す（ユーザー列挙攻撃を防ぐため）
            echo json_encode([
                'success' => true,
                'message' => 'パスワードリセットメールを送信しました。メールが届かない場合は、登録されているメールアドレスを確認してください。'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 既存の未使用トークンを無効化
        $invalidateStmt = $pdo->prepare("
            UPDATE password_reset_tokens 
            SET used_at = NOW() 
            WHERE user_id = :user_id 
            AND used_at IS NULL 
            AND expires_at > NOW()
        ");
        $invalidateStmt->execute([':user_id' => $user['id']]);
        
        // 新しいリセットトークンを生成
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // トークンをデータベースに保存
        $tokenStmt = $pdo->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at)
            VALUES (:user_id, :token, :expires_at)
        ");
        $tokenStmt->execute([
            ':user_id' => $user['id'],
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);
        
        // リセットパスの決定（リクエストボディから取得、デフォルトは/staff/reset-password）
        $resetPath = $input['reset_path'] ?? '/staff/reset-password';
        
        // セキュリティ: 許可されたパスのみ受け付ける
        $allowedPaths = ['/staff/reset-password', '/company/reset-password'];
        if (!in_array($resetPath, $allowedPaths)) {
            $resetPath = '/staff/reset-password'; // デフォルトにフォールバック
        }
        
        // メール送信
        $emailSent = sendPasswordResetEmail(
            $user['email'],
            $user['username'],
            $user['name'],
            $token,
            $resetPath
        );
        
        // メール送信のログを記録
        if ($emailSent) {
            error_log("Password reset email sent successfully to: {$user['email']} (username: {$user['username']})");
        } else {
            // メール送信に失敗した場合でも、トークンは作成済みなので成功として返す
            // （実際のメール送信エラーはログに記録される）
            error_log("Failed to send password reset email to: {$user['email']} (username: {$user['username']})");
            
            // デバッグモードの場合は詳細情報を返す
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Mail configuration check:");
                error_log("  MAIL_USE_SMTP: " . getEnvValue('MAIL_USE_SMTP', 'false'));
                error_log("  MAIL_FROM: " . getEnvValue('MAIL_FROM', 'not set'));
                if (getEnvValue('MAIL_USE_SMTP', 'false') === 'true') {
                    error_log("  MAIL_SMTP_HOST: " . getEnvValue('MAIL_SMTP_HOST', 'not set'));
                    error_log("  MAIL_SMTP_PORT: " . getEnvValue('MAIL_SMTP_PORT', 'not set'));
                    error_log("  MAIL_SMTP_USER: " . (getEnvValue('MAIL_SMTP_USER', '') ? 'set' : 'not set'));
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'パスワードリセットメールを送信しました。メールが届かない場合は、登録されているメールアドレスを確認してください。',
            'email_sent' => $emailSent // デバッグ用（本番環境では削除推奨）
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'forgot password');
    }
}

/**
 * パスワードリセット実行
 */
function resetPassword() {
    try {
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['token']) || !isset($input['password'])) {
            sendValidationError([
                'token' => 'Token is required',
                'password' => 'Password is required'
            ]);
        }
        
        $token = $input['token'];
        $newPassword = $input['password'];
        
        // パスワードの長さチェック
        if (strlen($newPassword) < 6) {
            sendValidationError([
                'password' => 'Password must be at least 6 characters'
            ]);
        }
        
        // トークンの検証
        $tokenStmt = $pdo->prepare("
            SELECT prt.*, u.id as user_id, u.username, u.email, u.name
            FROM password_reset_tokens prt
            INNER JOIN users u ON prt.user_id = u.id
            WHERE prt.token = :token
            AND prt.used_at IS NULL
            AND prt.expires_at > NOW()
        ");
        $tokenStmt->execute([':token' => $token]);
        $tokenData = $tokenStmt->fetch();
        
        if (!$tokenData) {
            sendUnauthorizedError('Invalid or expired token');
        }
        
        // パスワードをハッシュ化
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // パスワードを更新
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET password_hash = :password_hash, 
                updated_at = NOW()
            WHERE id = :user_id
        ");
        $updateStmt->execute([
            ':password_hash' => $passwordHash,
            ':user_id' => $tokenData['user_id']
        ]);
        
        // トークンを無効化
        $markUsedStmt = $pdo->prepare("
            UPDATE password_reset_tokens 
            SET used_at = NOW() 
            WHERE token = :token
        ");
        $markUsedStmt->execute([':token' => $token]);
        
        echo json_encode([
            'success' => true,
            'message' => 'パスワードが正常にリセットされました。'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'reset password');
    }
}

