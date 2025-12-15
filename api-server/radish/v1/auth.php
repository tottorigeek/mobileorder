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
        
        // デバッグログ
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Login attempt - Username: {$input['username']}");
            if ($userCheck) {
                error_log("  User found: yes");
                error_log("  User ID: {$userCheck['id']}");
                error_log("  Is active: " . ($userCheck['is_active'] ? 'yes' : 'no'));
                error_log("  Shop ID: " . ($userCheck['shop_id'] ?? 'not set'));
                error_log("  Shop active: " . ($userCheck['shop_active'] ? 'yes' : ($userCheck['shop_active'] === null ? 'null' : 'no')));
                
                // パスワード検証テスト
                $passwordMatch = password_verify($input['password'], $userCheck['password_hash']);
                error_log("  Password match: " . ($passwordMatch ? 'yes' : 'no'));
                
                if (!$passwordMatch) {
                    error_log("  Password verification failed");
                    error_log("  Input password length: " . strlen($input['password']));
                    error_log("  Stored hash preview: " . substr($userCheck['password_hash'], 0, 20) . "...");
                }
            } else {
                error_log("  User found: no");
            }
        }
        
        /**
         * ログインタイプの判定
         * 
         * 【重要】運営者向け管理ログイン（/company/login）の判定
         * - company_loginフラグがtrueの場合、運営者向け管理ログインとして扱う
         * - 運営者向け管理ログインの場合は店舗の状態チェックをスキップする
         * - 理由: 運営者向け管理画面は店舗の状態に関係なくアクセスできる必要がある
         * 
         * 【注意】将来の変更時:
         * - この判定ロジックを変更する場合は、/company/loginと/staff/loginの両方の動作を確認すること
         * - 店舗チェックを追加する場合は、運営者向け管理ログインの場合はスキップする条件を維持すること
         */
        $isCompanyLogin = isset($input['company_login']) && $input['company_login'] === true;
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("  Company login mode: " . ($isCompanyLogin ? 'yes' : 'no'));
        }
        
        /**
         * ログイン条件の検証
         * 
         * 【共通チェック項目】
         * 1. ユーザーの存在確認
         * 2. ユーザーの有効性（is_active）
         * 3. パスワードの一致確認
         * 
         * 【通常ログイン（/staff/login）のみのチェック項目】
         * 4. 店舗IDの存在確認（shop_id）
         * 5. 店舗の有効性（shop_active）
         * 
         * 【運営者向け管理ログイン（/company/login）の場合】
         * - 上記の4と5をスキップする
         * - 理由: 運営者向け管理画面は店舗の状態に関係なくアクセスできる必要がある
         * 
         * 【セキュリティ】
         * - すべての失敗理由に対して同じエラーメッセージを返す
         * - ユーザー列挙攻撃を防ぐため
         */
        $loginCheckFailed = false;
        $failureReasons = [];
        
        // 1. ユーザーの存在確認
        if (!$userCheck) {
            $loginCheckFailed = true;
            $failureReasons[] = 'user_not_found';
        }
        // 2. ユーザーの有効性確認
        elseif (!$userCheck['is_active']) {
            $loginCheckFailed = true;
            $failureReasons[] = 'user_inactive';
        }
        // 3. 通常ログインの場合のみ店舗チェックを実行
        elseif (!$isCompanyLogin) {
            /**
             * 【重要】通常ログイン（/staff/login）の場合のみ店舗チェックを実行
             * 
             * 運営者向け管理ログイン（/company/login）の場合はこのブロックをスキップする
             * - 理由: 運営者向け管理画面は店舗の状態に関係なくアクセスできる必要がある
             * 
             * 【注意】将来の変更時:
             * - この条件分岐を削除したり変更したりしないこと
             * - 店舗チェックを追加する場合は、$isCompanyLoginの条件を維持すること
             */
            // 4. 店舗IDの存在確認
            if (!$userCheck['shop_id']) {
                $loginCheckFailed = true;
                $failureReasons[] = 'no_shop_id';
            }
            // 5. 店舗の有効性確認
            elseif ($userCheck['shop_active'] === null || !$userCheck['shop_active']) {
                $loginCheckFailed = true;
                $failureReasons[] = 'shop_inactive';
            }
        }
        
        // 3. パスワード検証（すべてのログインタイプで共通）
        if ($userCheck) {
            $passwordMatch = password_verify($input['password'], $userCheck['password_hash']);
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Login - Password verification for user: {$input['username']}");
                error_log("  Input password length: " . strlen($input['password']));
                error_log("  Stored hash length: " . strlen($userCheck['password_hash']));
                error_log("  Stored hash preview: " . substr($userCheck['password_hash'], 0, 20) . "...");
                error_log("  Password match: " . ($passwordMatch ? 'yes' : 'no'));
                
                if (!$passwordMatch) {
                    // パスワード検証が失敗した場合、より詳細な情報を記録
                    error_log("  Password verification failed - possible causes:");
                    error_log("    - Password was not correctly hashed during reset");
                    error_log("    - Password hash was truncated in database");
                    error_log("    - Character encoding issue");
                    error_log("    - Wrong password entered");
                }
            }
            
            if (!$passwordMatch) {
                $loginCheckFailed = true;
                $failureReasons[] = 'password_mismatch';
            }
        }
        
        if ($loginCheckFailed) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Login failed - Reasons: " . implode(', ', $failureReasons));
            }
            sendUnauthorizedError('Invalid credentials');
        }
        
        /**
         * ログイン成功 - ユーザー情報の取得
         * 
         * 【重要】ログインタイプに応じて異なるSQLクエリを使用
         * 
         * 【運営者向け管理ログイン（/company/login）の場合】
         * - LEFT JOINを使用して店舗が無効でも取得可能
         * - shop_activeの条件を追加しない
         * - 理由: 運営者向け管理画面は店舗の状態に関係なくアクセスできる必要がある
         * 
         * 【通常ログイン（/staff/login）の場合】
         * - INNER JOINを使用して店舗が有効な場合のみ取得
         * - shop_active = 1の条件を追加
         * - 理由: 通常のログインは店舗が有効である必要がある
         * 
         * 【注意】将来の変更時:
         * - この条件分岐を削除したり変更したりしないこと
         * - 運営者向け管理ログインの場合はLEFT JOINとshop_active条件なしを維持すること
         * - 通常ログインの場合はINNER JOINとshop_active = 1条件を維持すること
         */
        if ($isCompanyLogin) {
            // 運営者向け管理ログイン: 店舗の状態に関係なく取得
            $stmt = $pdo->prepare("
                SELECT u.*, s.code as shop_code, s.name as shop_name 
                FROM users u
                LEFT JOIN shops s ON u.shop_id = s.id
                WHERE u.username = :username 
                AND u.is_active = 1
            ");
        } else {
            // 通常ログイン: 店舗が有効な場合のみ取得
            $stmt = $pdo->prepare("
                SELECT u.*, s.code as shop_code, s.name as shop_name 
                FROM users u
                INNER JOIN shops s ON u.shop_id = s.id
                WHERE u.username = :username 
                AND u.is_active = 1 
                AND s.is_active = 1
            ");
        }
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
        
        // デバッグログ
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Password reset request - User search result:");
            error_log("  Search value: " . (isset($input['username']) ? $input['username'] : $input['email']));
            error_log("  User found: " . ($user ? 'yes' : 'no'));
            if ($user) {
                error_log("  User ID: " . $user['id']);
                error_log("  Username: " . $user['username']);
                error_log("  Email: " . ($user['email'] ?? 'not set'));
                error_log("  Is active: " . ($user['is_active'] ? 'yes' : 'no'));
                error_log("  Shop ID: " . ($user['shop_id'] ?? 'not set'));
                error_log("  Shop active: " . ($user['shop_active'] ? 'yes' : ($user['shop_active'] === null ? 'null' : 'no')));
            }
        }
        
        /**
         * パスワードリセットのユーザー検証
         * 
         * 【重要】セキュリティ上の理由で、ユーザーが存在しない場合でも成功メッセージを返す
         * （ユーザー列挙攻撃を防ぐため）
         * 
         * 【重要】パスワードリセットは店舗の状態に関係なく送信できる
         * - 理由: ログインできない状況を解決するための機能であるため
         * - 店舗が無効化されていても、ユーザーがパスワードを忘れた場合にリセットできる必要がある
         * - ログイン時は店舗が有効である必要があるが、パスワードリセットはその前段階の機能
         * 
         * 【注意】将来の変更時:
         * - この部分で店舗の状態チェック（shop_active）を追加しないこと
         * - 店舗が無効でもメールを送信する必要がある
         * - ユーザーが存在しない、またはメールアドレスが未設定の場合のみ早期リターン
         */
        if (!$user || !$user['email']) {
            // ユーザーが存在しない、またはメールアドレスが登録されていない場合でも
            // 成功メッセージを返す（ユーザー列挙攻撃を防ぐため）
            $reason = [];
            if (!$user) {
                $reason[] = 'user_not_found';
                error_log("Password reset: User not found");
            } elseif (!$user['email']) {
                $reason[] = 'email_not_set';
                error_log("Password reset: User email not set");
            }
            
            $response = [
                'success' => true,
                'message' => 'パスワードリセットメールを送信しました。メールが届かない場合は、登録されているメールアドレスを確認してください。',
                'email_sent' => false
            ];
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $response['debug'] = [
                    'user_found' => $user !== false,
                    'reason' => $reason,
                    'user_exists' => $user !== false,
                    'user_has_email' => $user && !empty($user['email']),
                    'shop_active' => $user && $user['shop_active'],
                    'note' => 'パスワードリセットは店舗の状態に関係なく送信されます'
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            return;
        }
        
        /**
         * 店舗が無効化されている場合の警告ログ
         * 
         * 【重要】メール送信は続行する
         * - パスワードリセットは店舗の状態に関係なく実行される
         * - ログインできない状況を解決するための機能であるため
         * - この警告は情報提供のためのみ（処理を停止しない）
         */
        if (!$user['shop_active']) {
            error_log("Password reset: Shop is inactive for user {$user['username']}, but password reset email will be sent anyway");
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
            error_log("  Reset URL: " . getEnvValue('FRONTEND_BASE_URL', 'https://mameq.xsrv.jp') . $resetPath . '?token=' . urlencode($token));
            
            // mail()関数を使用している場合の警告
            if (getEnvValue('MAIL_USE_SMTP', 'false') !== 'true') {
                error_log("  WARNING: Using PHP mail() function. Email may not be delivered on shared hosting.");
                error_log("  Recommendation: Configure SMTP settings in .env file.");
            }
        } else {
            // メール送信に失敗した場合でも、トークンは作成済みなので成功として返す
            // （実際のメール送信エラーはログに記録される）
            error_log("Failed to send password reset email to: {$user['email']} (username: {$user['username']})");
            
            // デバッグモードの場合は詳細情報を返す
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Mail configuration check:");
                error_log("  MAIL_USE_SMTP: " . getEnvValue('MAIL_USE_SMTP', 'false'));
                error_log("  MAIL_FROM: " . getEnvValue('MAIL_FROM', 'not set'));
                error_log("  sendmail_path: " . ini_get('sendmail_path'));
                error_log("  SMTP: " . ini_get('SMTP'));
                if (getEnvValue('MAIL_USE_SMTP', 'false') === 'true') {
                    error_log("  MAIL_SMTP_HOST: " . getEnvValue('MAIL_SMTP_HOST', 'not set'));
                    error_log("  MAIL_SMTP_PORT: " . getEnvValue('MAIL_SMTP_PORT', 'not set'));
                    error_log("  MAIL_SMTP_USER: " . (getEnvValue('MAIL_SMTP_USER', '') ? 'set' : 'not set'));
                }
            }
        }
        
        $response = [
            'success' => true,
            'message' => 'パスワードリセットメールを送信しました。メールが届かない場合は、登録されているメールアドレスを確認してください。',
            'email_sent' => $emailSent
        ];
        
        // デバッグモードの場合は詳細情報を追加
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $response['debug'] = [
                'user_found' => true,
                'user_email' => $user['email'],
                'user_username' => $user['username'],
                'token_created' => true,
                'reset_path' => $resetPath,
                'reset_url' => getEnvValue('FRONTEND_BASE_URL', 'https://mameq.xsrv.jp') . $resetPath . '?token=' . urlencode($token),
                'mail_config' => [
                    'use_smtp' => getEnvValue('MAIL_USE_SMTP', 'false') === 'true',
                    'smtp_host' => getEnvValue('MAIL_SMTP_HOST', 'not set'),
                    'smtp_port' => getEnvValue('MAIL_SMTP_PORT', 'not set'),
                    'mail_from' => getEnvValue('MAIL_FROM', 'not set')
                ]
            ];
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
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
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Password reset - Updating password for user ID: {$tokenData['user_id']}");
            error_log("  Username: {$tokenData['username']}");
            error_log("  Password length: " . strlen($newPassword));
            error_log("  Hash generated: " . substr($passwordHash, 0, 20) . "...");
        }
        
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
        
        $affectedRows = $updateStmt->rowCount();
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Password reset - Updating password for user ID: {$tokenData['user_id']}");
            error_log("Password reset - Update affected rows: {$affectedRows}");
        }
        
        if ($affectedRows === 0) {
            error_log("Password reset - Failed to update password for user ID: {$tokenData['user_id']}");
            sendServerError('Failed to update password');
        }
        
        // 更新後のパスワード検証（必ず実行）
        // データベースから最新のパスワードハッシュを取得して検証
        $verifyStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :user_id");
        $verifyStmt->execute([':user_id' => $tokenData['user_id']]);
        $updatedUser = $verifyStmt->fetch();
        
        if (!$updatedUser) {
            error_log("Password reset - Failed to retrieve updated user for user ID: {$tokenData['user_id']}");
            sendServerError('Failed to verify password update');
        }
        
        // パスワード検証を実行
        $verifyResult = password_verify($newPassword, $updatedUser['password_hash']);
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Password reset - Password verification test: " . ($verifyResult ? 'SUCCESS' : 'FAILED'));
            error_log("Password reset - Stored hash length: " . strlen($updatedUser['password_hash']));
            error_log("Password reset - Stored hash preview: " . substr($updatedUser['password_hash'], 0, 20) . "...");
        }
        
        // 検証が失敗した場合はエラーを返す
        if (!$verifyResult) {
            error_log("Password reset - CRITICAL: Password verification failed after update for user ID: {$tokenData['user_id']}");
            error_log("Password reset - This indicates a serious problem with password hashing or storage");
            sendServerError('パスワードの更新に失敗しました。管理者にお問い合わせください。');
        }
        
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

