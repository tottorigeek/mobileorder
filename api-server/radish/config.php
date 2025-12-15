<?php
/**
 * データベース設定ファイル
 * エックスサーバーのデータベース情報を設定してください
 * 
 * 設定は.envファイルから読み込みます。
 * .envファイルが存在しない場合は、デフォルト値が使用されます。
 */

// .envファイルのパス
$envFile = __DIR__ . '/.env';

/**
 * .envファイルを読み込む関数
 * 
 * @param string $filePath .envファイルのパス
 * @return array 環境変数の配列
 */
function loadEnvFile($filePath) {
    $env = [];
    
    if (!file_exists($filePath)) {
        return $env;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // コメント行をスキップ
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // KEY=VALUE形式をパース
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // クォートを削除
            $value = trim($value, '"\'');
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

/**
 * 環境変数を取得（.envファイルまたはシステム環境変数から）
 * 
 * @param string $key 環境変数のキー
 * @param mixed $default デフォルト値
 * @return mixed 環境変数の値
 */
function getEnvValue($key, $default = null) {
    // まずシステム環境変数をチェック
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // .envファイルを読み込む（一度だけ）
    static $envCache = null;
    if ($envCache === null) {
        global $envFile;
        $envCache = loadEnvFile($envFile);
    }
    
    // .envファイルから取得
    if (isset($envCache[$key])) {
        return $envCache[$key];
    }
    
    return $default;
}

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// エラーレポート設定（本番環境では無効化推奨）
error_reporting(E_ALL);
ini_set('display_errors', 0); // 本番環境では0に設定

// 環境設定（development/production）
// .envファイルから読み込む、デフォルトはdevelopment
$environment = getEnvValue('ENVIRONMENT', 'development');
define('ENVIRONMENT', $environment);

// デバッグモード設定（本番環境ではfalseに設定）
// 開発環境ではtrue、本番環境ではfalseに設定
define('DEBUG_MODE', ENVIRONMENT === 'development');

// データベース接続情報（.envファイルから読み込む）
define('DB_HOST', getEnvValue('DB_HOST', 'localhost'));
define('DB_NAME', getEnvValue('DB_NAME', 'mameq_radish'));
define('DB_USER', getEnvValue('DB_USER', 'mameq_radish'));
define('DB_PASS', getEnvValue('DB_PASS', ''));
define('DB_CHARSET', getEnvValue('DB_CHARSET', 'utf8mb4'));

// JWT設定（.envファイルから読み込む）
// 注意: 本番環境では必ず強力な秘密鍵に変更してください
// この秘密鍵は以下のコマンドで生成できます: openssl rand -base64 32
$jwtSecret = getEnvValue('JWT_SECRET');
if (empty($jwtSecret)) {
    // デフォルト値（後方互換性のため）
    $jwtSecret = 'mameq_radish_jwt_secret_2024_' . hash('sha256', 'mameq_radish_restaurant_order_system' . DB_PASS);
}
define('JWT_SECRET', $jwtSecret);
define('JWT_ALGORITHM', getEnvValue('JWT_ALGORITHM', 'HS256'));
define('JWT_EXPIRATION', (int)getEnvValue('JWT_EXPIRATION', 86400 * 7)); // 7日間

/**
 * JWTトークンを生成
 */
function generateJWT($payload) {
    $header = [
        'typ' => 'JWT',
        'alg' => JWT_ALGORITHM
    ];
    
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRATION;
    
    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));
    
    $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
    $signatureEncoded = base64UrlEncode($signature);
    
    return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
}

/**
 * JWTトークンを検証
 */
function verifyJWT($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
    
    // 署名を検証
    $signature = base64UrlDecode($signatureEncoded);
    $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWT_SECRET, true);
    
    if (!hash_equals($expectedSignature, $signature)) {
        return false;
    }
    
    // ペイロードをデコード
    $payload = json_decode(base64UrlDecode($payloadEncoded), true);
    
    // 有効期限を確認
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Base64URLエンコード
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64URLデコード
 */
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * AuthorizationヘッダーからJWTトークンを取得
 */
function getJWTFromHeader() {
    $headers = null;
    
    // 複数の方法でAuthorizationヘッダーを取得
    // エックスサーバーでは、リバースプロキシ経由でREDIRECT_HTTP_AUTHORIZATIONに設定される場合がある
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } else if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if ($requestHeaders) {
            // キーを小文字に統一して検索
            $requestHeadersLower = array_change_key_case($requestHeaders, CASE_LOWER);
            if (isset($requestHeadersLower['authorization'])) {
                $headers = trim($requestHeadersLower['authorization']);
            }
        }
    } else if (function_exists('getallheaders')) {
        $requestHeaders = getallheaders();
        if ($requestHeaders) {
            // キーを小文字に統一して検索
            $requestHeadersLower = array_change_key_case($requestHeaders, CASE_LOWER);
            if (isset($requestHeadersLower['authorization'])) {
                $headers = trim($requestHeadersLower['authorization']);
            }
        }
    }
    
    if (!empty($headers)) {
        if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

// データベース接続関数
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

// JSONレスポンスヘッダー設定
function setJsonHeader() {
    // CORSヘッダーを確実に設定
    if (!headers_sent()) {
        // 許可するオリジンのリスト
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001',
            'https://mameq.xsrv.jp',
            'http://mameq.xsrv.jp',
            'https://mobileorder-eight.vercel.app',
            'https://api.towndx.com',
            'http://api.towndx.com',
        ];
        
        // リクエスト元のオリジンを取得
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        
        // オリジンが空の場合は、Refererヘッダーから抽出を試みる
        if (empty($origin) && isset($_SERVER['HTTP_REFERER'])) {
            $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
            if ($parsedUrl) {
                $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                if (isset($parsedUrl['port'])) {
                    $origin .= ':' . $parsedUrl['port'];
                }
            }
        }
        
        // 許可されたオリジンの場合のみ設定（セッション使用のため）
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        } elseif (!empty($origin)) {
            // 開発環境でのデバッグ用（オリジンが指定されている場合）
            // localhostや127.0.0.1のパターンをチェック
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Expose-Headers: Authorization'); // クライアントがAuthorizationヘッダーを読み取れるように
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=utf-8');
        // サービスワーカーのキャッシュを防ぐ
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

// OPTIONSリクエストの処理（プリフライトリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // CORSヘッダーを確実に設定
    // 許可するオリジンのリスト
    $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'https://mameq.xsrv.jp',
        'http://mameq.xsrv.jp',
        'https://mobileorder-eight.vercel.app',
        'https://api.towndx.com',
        'http://api.towndx.com',
    ];
    
    // リクエスト元のオリジンを取得
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    
    // オリジンが空の場合は、Refererヘッダーから抽出を試みる
    if (empty($origin) && isset($_SERVER['HTTP_REFERER'])) {
        $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
        if ($parsedUrl) {
            $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            if (isset($parsedUrl['port'])) {
                $origin .= ':' . $parsedUrl['port'];
            }
        }
    }
    
    // ヘッダーを確実に送信
    if (!headers_sent()) {
        // 許可されたオリジンの場合のみ設定
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        } elseif (!empty($origin)) {
            // 開発環境でのデバッグ用（オリジンが指定されている場合）
            // localhostや127.0.0.1のパターンをチェック
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
            } else {
                // 開発環境では、すべてのオリジンを許可（デバッグ用）
                if (ENVIRONMENT === 'development') {
                    header('Access-Control-Allow-Origin: ' . $origin);
                    header('Access-Control-Allow-Credentials: true');
                }
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400');
        header('Content-Length: 0');
    }
    
    http_response_code(200);
    exit;
}

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    // セッションクッキーの設定
    // CORSでクッキーを送信するため、SameSite=None と Secure が必要
    // 開発環境（localhost）から本番環境へのリクエストでは、SameSite=None が必要
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    // 開発環境から本番環境へのリクエストでは、SameSite=Noneが必要
    // ただし、SameSite=Noneの場合はSecureも必要（HTTPS必須）
    $sameSite = $isHttps ? 'None' : 'Lax';
    
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7日間
        'path' => '/', // ルートパスに設定（すべてのパスで有効）
        'domain' => '', // 空文字列で現在のドメインを使用
        'secure' => $isHttps, // HTTPSの場合のみSecureを設定
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    session_start();
}

// 認証チェック関数（JWTベース）
function checkAuth() {
    // JWTトークンを取得
    $token = getJWTFromHeader();
    
    if (!$token) {
        $response = ['error' => 'Unauthorized: Token not provided'];
        
        // デバッグ情報（開発環境のみ）
        if (DEBUG_MODE) {
            $debugInfo = [];
            $debugInfo['has_redirect_http_authorization'] = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            $debugInfo['has_http_authorization'] = isset($_SERVER['HTTP_AUTHORIZATION']);
            $debugInfo['has_authorization'] = isset($_SERVER['Authorization']);
            $debugInfo['has_apache_request_headers'] = function_exists('apache_request_headers');
            $debugInfo['has_getallheaders'] = function_exists('getallheaders');
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $debugInfo['redirect_http_authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $debugInfo['http_authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            }
            if (function_exists('apache_request_headers')) {
                $debugInfo['apache_headers'] = apache_request_headers();
            }
            if (function_exists('getallheaders')) {
                $debugInfo['getallheaders_result'] = getallheaders();
            }
            // すべての$_SERVER変数からauthorizationを検索
            $debugInfo['server_keys'] = array_keys(array_filter($_SERVER, function($key) {
                return stripos($key, 'auth') !== false;
            }, ARRAY_FILTER_USE_KEY));
            $response['debug'] = $debugInfo;
        }
        
        http_response_code(401);
        echo json_encode($response);
        exit;
    }
    
    // JWTトークンを検証
    $payload = verifyJWT($token);
    
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token']);
        exit;
    }
    
    // ペイロードから認証情報を取得
    // shop_id は会社ログイン（/unei/login）では null の場合があるため必須にしない
    // user_id は必須
    if (!isset($payload['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token payload']);
        exit;
    }
    
    return [
        'user_id' => $payload['user_id'],
        'shop_id' => $payload['shop_id'],
        'role' => $payload['role'] ?? 'staff',
        'username' => $payload['username'] ?? null
    ];
}

// 権限チェック関数
function checkPermission($requiredRole) {
    $auth = checkAuth();
    $roleHierarchy = ['staff' => 1, 'manager' => 2, 'owner' => 3];
    
    $userLevel = $roleHierarchy[$auth['role']] ?? 0;
    
    // 配列の場合は、いずれかのロールに該当すればOK
    if (is_array($requiredRole)) {
        $hasPermission = false;
        foreach ($requiredRole as $role) {
            $requiredLevel = $roleHierarchy[$role] ?? 0;
            if ($userLevel >= $requiredLevel) {
                $hasPermission = true;
                break;
            }
        }
        if (!$hasPermission) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: Insufficient permissions']);
            exit;
        }
    } else {
        // 単一のロールの場合
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        if ($userLevel < $requiredLevel) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden: Insufficient permissions']);
            exit;
        }
    }
    
    return $auth;
}

// 店舗IDの取得（リクエストから）
function getShopId() {
    // JWTトークンから取得（認証済みの場合）
    $token = getJWTFromHeader();
    if ($token) {
        $payload = verifyJWT($token);
        if ($payload && isset($payload['shop_id'])) {
            return $payload['shop_id'];
        }
    }
    
    // クエリパラメータから取得（顧客側）
    if (isset($_GET['shop']) || isset($_POST['shop'])) {
        $shopCode = $_GET['shop'] ?? $_POST['shop'];
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id FROM shops WHERE code = :code AND is_active = 1");
        $stmt->execute([':code' => $shopCode]);
        $shop = $stmt->fetch();
        return $shop ? $shop['id'] : null;
    }
    
    return null;
}

/**
 * 統一的なエラーレスポンスを送信
 * 
 * @param int $statusCode HTTPステータスコード
 * @param string $message エラーメッセージ
 * @param array $details 追加の詳細情報（オプション）
 * @param Exception|null $exception 例外オブジェクト（ログ用）
 */
function sendErrorResponse($statusCode, $message, $details = [], $exception = null) {
    http_response_code($statusCode);
    
    $response = [
        'error' => $message,
        'status' => $statusCode
    ];
    
    // デバッグモードの場合のみ詳細情報を追加
    if (DEBUG_MODE && !empty($details)) {
        $response['details'] = $details;
    }
    
    // エラーレベルの決定
    $level = 'error';
    if ($statusCode >= 400 && $statusCode < 500) {
        // クライアントエラー（400番台）はwarning
        $level = 'warning';
        if ($statusCode === 401 || $statusCode === 403) {
            // 認証・権限エラーはwarning
            $level = 'warning';
        }
    } else if ($statusCode >= 500) {
        // サーバーエラー（500番台）はerror
        $level = 'error';
    }
    
    // ログメッセージの構築
    $logMessage = $message;
    if ($exception !== null) {
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        error_log($logMessage);
        
        // デバッグモードの場合のみスタックトレースを追加
        if (DEBUG_MODE) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }
    } else {
        // 例外がない場合もログに記録
        $logMessage = sprintf(
            "[%s] HTTP %d: %s",
            date('Y-m-d H:i:s'),
            $statusCode,
            $message
        );
    }
    
    // データベースにエラーログを記録
    logErrorToDatabase($level, $logMessage, $exception);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * バリデーションエラーレスポンスを送信
 * 
 * @param array $errors バリデーションエラーの配列
 */
function sendValidationError($errors) {
    sendErrorResponse(400, 'Validation failed', ['validation_errors' => $errors]);
}

/**
 * 認証エラーレスポンスを送信
 * 
 * @param string $message エラーメッセージ（デフォルト: 'Unauthorized'）
 */
function sendUnauthorizedError($message = 'Unauthorized') {
    sendErrorResponse(401, $message);
}

/**
 * 権限エラーレスポンスを送信
 * 
 * @param string $message エラーメッセージ（デフォルト: 'Forbidden'）
 */
function sendForbiddenError($message = 'Forbidden') {
    sendErrorResponse(403, $message);
}

/**
 * リソース未検出エラーレスポンスを送信
 * 
 * @param string $resource リソース名（例: 'User', 'Order'）
 */
function sendNotFoundError($resource = 'Resource') {
    sendErrorResponse(404, $resource . ' not found');
}

/**
 * 競合エラーレスポンスを送信
 * 
 * @param string $message エラーメッセージ
 */
function sendConflictError($message) {
    sendErrorResponse(409, $message);
}

/**
 * サーバーエラーレスポンスを送信
 * 
 * @param string $message エラーメッセージ（デフォルト: 'Internal server error'）
 * @param Exception|null $exception 例外オブジェクト
 */
function sendServerError($message = 'Internal server error', $exception = null) {
    sendErrorResponse(500, $message, [], $exception);
}

/**
 * データベースエラーを処理
 * 
 * @param PDOException $e PDO例外オブジェクト
 * @param string $operation 操作名（例: 'fetching orders', 'creating user'）
 */
function handleDatabaseError($e, $operation) {
    $logMessage = sprintf(
        "[%s] Database error during %s: %s",
        date('Y-m-d H:i:s'),
        $operation,
        $e->getMessage()
    );
    error_log($logMessage);
    
    // データベースにも記録
    logErrorToDatabase('error', $logMessage, $e);
    
    // 本番環境では詳細なエラーメッセージを隠す
    $userMessage = DEBUG_MODE 
        ? "Database error during {$operation}: " . $e->getMessage()
        : "Failed to {$operation}";
    
    sendServerError($userMessage, $e);
}

/**
 * エラーログをデータベースに記録
 * 
 * @param string $level エラーレベル（error, warning, info, debug）
 * @param string $message エラーメッセージ
 * @param Exception|null $exception 例外オブジェクト（オプション）
 * @param array $context 追加のコンテキスト情報（オプション）
 */
function logErrorToDatabase($level = 'error', $message = '', $exception = null, $context = []) {
    try {
        $pdo = getDbConnection();
        
        // ユーザーIDと店舗IDを取得（認証済みの場合）
        $userId = null;
        $shopId = null;
        
        try {
            $token = getJWTFromHeader();
            if ($token) {
                $payload = verifyJWT($token);
                if ($payload) {
                    $userId = $payload['user_id'] ?? null;
                    $shopId = $payload['shop_id'] ?? null;
                }
            }
        } catch (Exception $e) {
            // 認証情報の取得に失敗しても続行
        }
        
        // 例外情報を取得
        $file = null;
        $line = null;
        $trace = null;
        
        if ($exception !== null) {
            $file = $exception->getFile();
            $line = $exception->getLine();
            
            // スタックトレースを取得（最大10フレーム）
            $traceArray = [];
            $traceFrames = $exception->getTrace();
            $maxFrames = min(10, count($traceFrames));
            
            for ($i = 0; $i < $maxFrames; $i++) {
                $frame = $traceFrames[$i];
                $traceArray[] = [
                    'file' => $frame['file'] ?? 'unknown',
                    'line' => $frame['line'] ?? 0,
                    'function' => $frame['function'] ?? 'unknown',
                    'class' => $frame['class'] ?? null
                ];
            }
            
            if (!empty($traceArray)) {
                $trace = json_encode($traceArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        
        // リクエスト情報を取得
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $requestUri = $_SERVER['REQUEST_URI'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // 環境情報を取得
        $environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'development';
        
        // エラーログをデータベースに挿入
        $sql = "INSERT INTO error_logs 
                (level, environment, message, file, line, trace, user_id, shop_id, request_method, request_uri, ip_address) 
                VALUES 
                (:level, :environment, :message, :file, :line, :trace, :user_id, :shop_id, :request_method, :request_uri, :ip_address)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':level' => $level,
            ':environment' => $environment,
            ':message' => $message,
            ':file' => $file,
            ':line' => $line,
            ':trace' => $trace,
            ':user_id' => $userId,
            ':shop_id' => $shopId,
            ':request_method' => $requestMethod,
            ':request_uri' => $requestUri,
            ':ip_address' => $ipAddress
        ]);
        
    } catch (Exception $e) {
        // エラーログの記録に失敗した場合は、通常のerror_logに記録
        error_log("Failed to log error to database: " . $e->getMessage());
    }
}

/**
 * SMTP経由でメールを送信
 * 
 * @param string $to 送信先メールアドレス
 * @param string $subject 件名
 * @param string $message メッセージ本文
 * @param string $from 送信元メールアドレス
 * @param string $fromName 送信元名（オプション）
 * @return bool 送信成功時true、失敗時false
 */
function sendEmailViaSMTP($to, $subject, $message, $from, $fromName = null) {
    $smtpHost = getEnvValue('MAIL_SMTP_HOST', 'localhost');
    $smtpPort = (int)getEnvValue('MAIL_SMTP_PORT', 587);
    $smtpSecure = getEnvValue('MAIL_SMTP_SECURE', 'tls');
    $smtpUser = getEnvValue('MAIL_SMTP_USER', '');
    $smtpPass = getEnvValue('MAIL_SMTP_PASS', '');
    $smtpAuth = getEnvValue('MAIL_SMTP_AUTH', 'true') === 'true';
    $debugMode = defined('DEBUG_MODE') && DEBUG_MODE;
    
    $socket = null;
    
    try {
        // SMTP接続
        $host = ($smtpSecure === 'ssl') ? 'ssl://' . $smtpHost : $smtpHost;
        
        if ($debugMode) {
            error_log("SMTP: Connecting to {$host}:{$smtpPort}");
        }
        
        $socket = @fsockopen($host, $smtpPort, $errno, $errstr, 30);
        
        if (!$socket) {
            $errorMsg = "SMTP connection failed to {$host}:{$smtpPort} - {$errstr} ({$errno})";
            error_log($errorMsg);
            return false;
        }
        
        if ($debugMode) {
            error_log("SMTP: Connection established");
        }
        
        // レスポンスを読み取る関数
        $readResponse = function($socket, $expectedCode = null) use ($debugMode) {
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            
            if ($debugMode) {
                error_log("SMTP Response: " . trim($response));
            }
            
            if ($expectedCode !== null && strpos($response, $expectedCode) !== 0) {
                if ($debugMode) {
                    error_log("SMTP Error: Expected {$expectedCode}, got: " . trim($response));
                }
                return false;
            }
            return $response;
        };
        
        // SMTPコマンドを送信する関数
        $sendCommand = function($socket, $command, $expectedCode, $hideSensitive = false) use ($readResponse, $debugMode) {
            if ($debugMode && !$hideSensitive) {
                error_log("SMTP Command: {$command}");
            } elseif ($debugMode && $hideSensitive) {
                error_log("SMTP Command: [AUTH credentials hidden]");
            }
            
            fputs($socket, $command . "\r\n");
            $response = $readResponse($socket, $expectedCode);
            
            if (!$response) {
                error_log("SMTP Command failed: {$command} - Expected {$expectedCode}");
            }
            
            return $response !== false;
        };
        
        // 初期応答を読み取る
        $initialResponse = $readResponse($socket, '220');
        if (!$initialResponse) {
            error_log("SMTP: Failed to get initial response from server");
            fclose($socket);
            return false;
        }
        
        // EHLOコマンド
        $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
        if (!$sendCommand($socket, "EHLO {$hostname}", '250')) {
            error_log("SMTP: EHLO command failed");
            fclose($socket);
            return false;
        }
        
        // TLS開始（tlsの場合）
        if ($smtpSecure === 'tls') {
            if (!$sendCommand($socket, 'STARTTLS', '220')) {
                error_log("SMTP: STARTTLS command failed");
                fclose($socket);
                return false;
            }
            
            // TLS暗号化を開始
            if ($debugMode) {
                error_log("SMTP: Enabling TLS encryption");
            }
            
            $cryptoResult = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$cryptoResult) {
                $cryptoError = error_get_last();
                error_log("SMTP: TLS encryption failed - " . ($cryptoError ? $cryptoError['message'] : 'Unknown error'));
                fclose($socket);
                return false;
            }
            
            if ($debugMode) {
                error_log("SMTP: TLS encryption enabled");
            }
            
            // EHLOを再送信
            if (!$sendCommand($socket, "EHLO {$hostname}", '250')) {
                error_log("SMTP: EHLO command failed after TLS");
                fclose($socket);
                return false;
            }
        }
        
        // 認証（必要な場合）
        if ($smtpAuth && !empty($smtpUser) && !empty($smtpPass)) {
            if ($debugMode) {
                error_log("SMTP: Starting authentication");
            }
            
            if (!$sendCommand($socket, 'AUTH LOGIN', '334')) {
                error_log("SMTP: AUTH LOGIN command failed");
                fclose($socket);
                return false;
            }
            
            if (!$sendCommand($socket, base64_encode($smtpUser), '334', true)) {
                error_log("SMTP: Username authentication failed");
                fclose($socket);
                return false;
            }
            
            if (!$sendCommand($socket, base64_encode($smtpPass), '235', true)) {
                error_log("SMTP: Password authentication failed - Check username and password");
                fclose($socket);
                return false;
            }
            
            if ($debugMode) {
                error_log("SMTP: Authentication successful");
            }
        }
        
        // 送信元を設定
        $fromHeader = $fromName ? "{$fromName} <{$from}>" : $from;
        if (!$sendCommand($socket, "MAIL FROM: <{$from}>", '250')) {
            error_log("SMTP: MAIL FROM command failed for {$from}");
            fclose($socket);
            return false;
        }
        
        // 送信先を設定
        if (!$sendCommand($socket, "RCPT TO: <{$to}>", '250')) {
            error_log("SMTP: RCPT TO command failed for {$to}");
            fclose($socket);
            return false;
        }
        
        // データ送信開始
        if (!$sendCommand($socket, 'DATA', '354')) {
            error_log("SMTP: DATA command failed");
            fclose($socket);
            return false;
        }
        
        // メールヘッダーと本文を送信
        $emailData = "From: {$fromHeader}\r\n";
        $emailData .= "To: <{$to}>\r\n";
        $emailData .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $emailData .= "MIME-Version: 1.0\r\n";
        $emailData .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailData .= "Content-Transfer-Encoding: base64\r\n";
        $emailData .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $emailData .= "\r\n";
        $emailData .= chunk_split(base64_encode($message));
        $emailData .= "\r\n.\r\n";
        
        if ($debugMode) {
            error_log("SMTP: Sending email data (" . strlen($emailData) . " bytes)");
        }
        
        fputs($socket, $emailData);
        
        // 送信完了確認
        $finalResponse = $readResponse($socket, '250');
        if (!$finalResponse) {
            error_log("SMTP: Email data send failed - Server did not accept the email");
            fclose($socket);
            return false;
        }
        
        if ($debugMode) {
            error_log("SMTP: Email accepted by server");
        }
        
        // QUITコマンド
        $sendCommand($socket, 'QUIT', '221');
        fclose($socket);
        
        error_log("SMTP: Email sent successfully to {$to}");
        return true;
        
    } catch (Exception $e) {
        $errorMsg = "SMTP send failed: " . $e->getMessage();
        error_log($errorMsg);
        error_log("SMTP Exception trace: " . $e->getTraceAsString());
        if ($socket) {
            @fclose($socket);
        }
        return false;
    }
}

/**
 * メール送信関数
 * SMTP設定がある場合はSMTP経由、ない場合はPHPのmail()関数を使用
 * 
 * @param string $to 送信先メールアドレス
 * @param string $subject 件名
 * @param string $message メッセージ本文
 * @param string $from 送信元メールアドレス（オプション）
 * @return bool 送信成功時true、失敗時false
 */
function sendEmail($to, $subject, $message, $from = null) {
    // 送信元メールアドレスの設定（.envファイルから読み込む）
    if ($from === null) {
        $from = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
    }
    
    $fromName = getEnvValue('MAIL_FROM_NAME', 'Radish System');
    
    // SMTPを使用するかどうかを確認
    $useSMTP = getEnvValue('MAIL_USE_SMTP', 'false') === 'true';
    
    // デバッグログ
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Sending email to: {$to}");
        error_log("  From: {$fromName} <{$from}>");
        error_log("  Subject: {$subject}");
        error_log("  Method: " . ($useSMTP ? 'SMTP' : 'mail()'));
    }
    
    if ($useSMTP) {
        // SMTP経由で送信
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Using SMTP to send email to: {$to}");
            error_log("  SMTP Host: " . getEnvValue('MAIL_SMTP_HOST', 'not set'));
            error_log("  SMTP Port: " . getEnvValue('MAIL_SMTP_PORT', 'not set'));
            error_log("  SMTP Secure: " . getEnvValue('MAIL_SMTP_SECURE', 'not set'));
        }
        
        $result = sendEmailViaSMTP($to, $subject, $message, $from, $fromName);
        
        if (!$result) {
            error_log("SMTP email send failed to: {$to}");
            error_log("  Check SMTP settings in .env file");
            error_log("  Verify SMTP credentials and server accessibility");
        }
        
        return $result;
    } else {
        // PHPのmail()関数を使用
        $headers = [
            'From: ' . ($fromName ? "{$fromName} <{$from}>" : $from),
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        $headersString = implode("\r\n", $headers);
        
        // メール送信
        // 注意: mail()関数は成功を返しても、実際にはメールが送信されない場合があります
        // 特に共有サーバー環境では、sendmail_pathが正しく設定されていない可能性があります
        $result = @mail($to, $subject, $message, $headersString);
        
        // エラー情報を取得
        $lastError = error_get_last();
        
        if (!$result) {
            $errorMsg = "Failed to send email via mail() to: {$to}";
            if ($lastError && $lastError['message']) {
                $errorMsg .= " - Error: " . $lastError['message'];
            }
            error_log($errorMsg);
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("  From: {$from}");
                error_log("  Subject: {$subject}");
                error_log("  Headers: {$headersString}");
                error_log("  sendmail_path: " . ini_get('sendmail_path'));
                error_log("  SMTP: " . ini_get('SMTP'));
            }
            return false;
        }
        
        // 成功した場合でも、警告を記録
        if ($lastError && strpos($lastError['message'], 'mail') !== false) {
            error_log("Warning: mail() returned true but error occurred: " . $lastError['message']);
        }
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("Email sent successfully via mail() to: {$to}");
            error_log("  Note: mail() returned true, but actual delivery depends on server configuration");
            error_log("  sendmail_path: " . ini_get('sendmail_path'));
        }
        
        return true;
    }
}

/**
 * パスワードリセットメールを送信
 * 
 * @param string $email 送信先メールアドレス
 * @param string $username ユーザー名
 * @param string $name ユーザー名（表示名）
 * @param string $resetToken リセットトークン
 * @param string $resetPath リセットページのパス（デフォルト: /staff/reset-password）
 * @return bool 送信成功時true、失敗時false
 */
function sendPasswordResetEmail($email, $username, $name, $resetToken, $resetPath = '/staff/reset-password') {
    // フロントエンドのベースURLを取得（.envファイルから読み込む）
    $frontendBaseUrl = getEnvValue('FRONTEND_BASE_URL', 'https://mameq.xsrv.jp');
    
    // リセットURL
    $resetUrl = $frontendBaseUrl . $resetPath . '?token=' . urlencode($resetToken);
    
    // メール件名
    $subject = 'パスワードリセットのお知らせ';
    
    // メール本文（HTML形式）
    $message = "
    <!DOCTYPE html>
    <html lang='ja'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>パスワードリセット</title>
    </head>
    <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0; font-size: 24px;'>パスワードリセット</h1>
        </div>
        <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
            <p style='font-size: 16px; margin-bottom: 20px;'>{$name} 様</p>
            <p style='font-size: 16px; margin-bottom: 20px;'>
                パスワードリセットのリクエストを受け付けました。<br>
                以下のリンクをクリックして、新しいパスワードを設定してください。
            </p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetUrl}' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;'>
                    パスワードをリセット
                </a>
            </div>
            <p style='font-size: 14px; color: #666; margin-top: 30px;'>
                このリンクは24時間有効です。<br>
                もしこのリクエストを送信していない場合は、このメールを無視してください。
            </p>
            <p style='font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;'>
                リンクがクリックできない場合は、以下のURLをブラウザにコピー＆ペーストしてください：<br>
                <a href='{$resetUrl}' style='color: #667eea; word-break: break-all;'>{$resetUrl}</a>
            </p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

