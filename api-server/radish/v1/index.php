<?php
/**
 * APIルーティング
 * http://mameq.xsrv.jp/radish/api/ でアクセス
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

// リクエストメソッドとパスの取得
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// /radish/v1/ または /radish/api/ を削除
$path = preg_replace('#^/radish/(v1|api)/#', '', $path);
$path = trim($path, '/');

// パスの分割
$pathParts = explode('/', $path);
$endpoint = $pathParts[0] ?? '';

// ルーティング
switch ($endpoint) {
    case 'shops':
        require_once __DIR__ . '/shops.php';
        break;
    
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
    
    case 'users':
        // 残りのパスを取得（users以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['USERS_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/users.php';
        break;
    
    case 'unei-users':
        // 残りのパスを取得（unei-users以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['COMPANY_USERS_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/unei-users.php';
        break;
    
    case 'my-shops':
        require_once __DIR__ . '/my-shops.php';
        break;
    
    case 'menus':
        require_once __DIR__ . '/menus.php';
        break;
    
    case 'tables':
        // 残りのパスを取得（tables以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        // 環境変数として残りのパスを設定（tables.phpで使用）
        $_ENV['TABLES_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/tables.php';
        break;
    
    case 'orders':
        // ordersエンドポイントの場合、残りのパスを取得
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['ORDERS_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/orders.php';
        break;
    
    case 'error-logs':
        // 残りのパスを取得（error-logs以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['ERROR_LOGS_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/error-logs.php';
        break;
    
    case 'visitors':
        // 残りのパスを取得（visitors以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['VISITORS_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/visitors.php';
        break;
    
    case 'categories':
        // 残りのパスを取得（categories以降の部分）
        $remainingPath = isset($pathParts[1]) ? implode('/', array_slice($pathParts, 1)) : '';
        $_ENV['CATEGORIES_REMAINING_PATH'] = $remainingPath;
        require_once __DIR__ . '/categories.php';
        break;
    
    default:
        sendErrorResponse(404, 'Endpoint not found');
        break;
}

