<?php
/**
 * データベース設定ファイル
 * エックスサーバーのデータベース情報を設定してください
 */

// データベース接続情報
define('DB_HOST', 'localhost'); // エックスサーバーの場合は通常 'localhost'
define('DB_NAME', 'your_database_name'); // データベース名
define('DB_USER', 'your_database_user'); // データベースユーザー名
define('DB_PASS', 'your_database_password'); // データベースパスワード
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
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=utf-8');
    }
}

// OPTIONSリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    setJsonHeader();
    http_response_code(200);
    exit;
}

