<?php
/**
 * メニューAPI デバッグ用
 * エラーの詳細を確認するためのファイル
 */

require_once __DIR__ . '/../config.php';

// エラー表示を有効化（デバッグ用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDbConnection();
    
    // テーブルの存在確認
    $stmt = $pdo->query("SHOW TABLES LIKE 'menus'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo json_encode([
            'error' => 'Table "menus" does not exist',
            'message' => 'データベースにmenusテーブルが存在しません。database.sqlをインポートしてください。'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // テーブル構造の確認
    $stmt = $pdo->query("DESCRIBE menus");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // データ数の確認
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menus");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // カテゴリフィルター（オプション）
    $category = $_GET['category'] ?? null;
    
    $sql = "SELECT * FROM menus WHERE is_available = 1";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }
    
    $sql .= " ORDER BY number ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $menus = $stmt->fetchAll();
    
    // データ形式を変換
    $result = array_map(function($menu) {
        return [
            'id' => (string)$menu['id'],
            'number' => $menu['number'],
            'name' => $menu['name'],
            'description' => $menu['description'],
            'price' => (int)$menu['price'],
            'category' => $menu['category'],
            'imageUrl' => $menu['image_url'] ?: null,
            'isAvailable' => (bool)$menu['is_available'],
            'isRecommended' => (bool)$menu['is_recommended']
        ];
    }, $menus);
    
    echo json_encode([
        'success' => true,
        'debug' => [
            'table_exists' => $tableExists,
            'table_columns' => $columns,
            'total_records' => (int)$count['count'],
            'available_records' => count($result)
        ],
        'data' => $result
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'General error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

