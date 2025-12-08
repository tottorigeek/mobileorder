<?php
/**
 * データベース設定ファイル
 * エックスサーバーのデータベース情報を設定してください
 */

// データベース接続情報
define('DB_HOST', 'localhost'); // エックスサーバーの場合は通常 'localhost'
define('DB_NAME', 'mameq_radish'); // データベース名
define('DB_USER', 'mameq_radish'); // データベースユーザー名
define('DB_PASS', 'iloveradish1208'); // データベースパスワード
define('DB_CHARSET', 'utf8mb4');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// エラーレポート設定（本番環境では無効化推奨）
error_reporting(E_ALL);
ini_set('display_errors', 0); // 本番環境では0に設定

// 環境設定（development/production）
// 本番環境では 'production' に設定してください
define('ENVIRONMENT', 'development'); // 'development' または 'production'

// デバッグモード設定（本番環境ではfalseに設定）
// 開発環境ではtrue、本番環境ではfalseに設定
define('DEBUG_MODE', ENVIRONMENT === 'development');

// JWT設定
// 注意: 本番環境では必ず強力な秘密鍵に変更してください
// この秘密鍵は以下のコマンドで生成できます: openssl rand -base64 32
define('JWT_SECRET', 'mameq_radish_jwt_secret_2024_' . hash('sha256', 'mameq_radish_restaurant_order_system' . DB_PASS)); // 本番環境ではより強力な秘密鍵に変更してください
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400 * 7); // 7日間

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
    if (!isset($payload['user_id']) || !isset($payload['shop_id'])) {
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

