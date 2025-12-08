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
$path = str_replace('/radish/api/', '', $path);
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
        require_once __DIR__ . '/users.php';
        break;
    
    case 'company-users':
        require_once __DIR__ . '/company-users.php';
        break;
    
    case 'my-shops':
        require_once __DIR__ . '/my-shops.php';
        break;
    
    case 'menus':
        require_once __DIR__ . '/menus.php';
        break;
    
    case 'tables':
        require_once __DIR__ . '/tables.php';
        break;
    
    case 'orders':
        // ordersエンドポイントの場合、残りのパスを取得
        $remainingPath = isset($pathParts[1]) ? $pathParts[1] : null;
        // orders.phpに直接処理を委譲
        require_once __DIR__ . '/orders.php';
        break;
    
    case 'error-logs':
        require_once __DIR__ . '/error-logs.php';
        break;
    
    case 'visitors':
        require_once __DIR__ . '/visitors.php';
        break;
    
    default:
        sendErrorResponse(404, 'Endpoint not found');
        break;
}

