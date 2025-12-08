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
        ];
        
        // リクエスト元のオリジンを取得
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // 許可されたオリジンの場合のみ設定（セッション使用のため）
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        } elseif (!empty($origin)) {
            // 開発環境でのデバッグ用（オリジンが指定されている場合）
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=utf-8');
    }
}

// OPTIONSリクエストの処理（プリフライトリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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
        ];
        
        // リクエスト元のオリジンを取得
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // 許可されたオリジンの場合のみ設定
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        } elseif (!empty($origin)) {
            // 開発環境でのデバッグ用（オリジンが指定されている場合）
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400');
    }
    http_response_code(200);
    exit;
}

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    // セッションクッキーの設定
    // SameSite=None は HTTPS が必要ですが、開発環境では Lax を使用
    $sameSite = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'None' : 'Lax';
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7日間
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    session_start();
}

// 認証チェック関数
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['shop_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'shop_id' => $_SESSION['shop_id'],
        'role' => $_SESSION['role'] ?? 'staff'
    ];
}

// 権限チェック関数
function checkPermission($requiredRole) {
    $auth = checkAuth();
    $roleHierarchy = ['staff' => 1, 'manager' => 2, 'owner' => 3];
    
    $userLevel = $roleHierarchy[$auth['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
    
    if ($userLevel < $requiredLevel) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Insufficient permissions']);
        exit;
    }
    
    return $auth;
}

// 店舗IDの取得（リクエストから）
function getShopId() {
    // セッションから取得（認証済みの場合）
    if (isset($_SESSION['shop_id'])) {
        return $_SESSION['shop_id'];
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

