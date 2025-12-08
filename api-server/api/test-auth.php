<?php
/**
 * 認証テスト用スクリプト
 * adminユーザーの存在確認とパスワード検証
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDbConnection();
    
    // adminユーザーの存在確認
    $stmt = $pdo->prepare("
        SELECT u.*, s.code as shop_code, s.name as shop_name, s.is_active as shop_active
        FROM users u
        LEFT JOIN shops s ON u.shop_id = s.id
        WHERE u.username = :username
    ");
    $stmt->execute([':username' => 'admin']);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'error' => 'User not found',
            'message' => 'adminユーザーがデータベースに存在しません'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // パスワード検証テスト
    $testPassword = 'password123';
    $passwordMatch = password_verify($testPassword, $user['password_hash']);
    
    // 結果を返す
    echo json_encode([
        'user_found' => true,
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'is_active' => (bool)$user['is_active'],
        'shop_id' => $user['shop_id'],
        'shop_code' => $user['shop_code'],
        'shop_name' => $user['shop_name'],
        'shop_active' => $user['shop_active'] ? (bool)$user['shop_active'] : null,
        'password_hash' => $user['password_hash'],
        'password_test' => [
            'test_password' => $testPassword,
            'password_match' => $passwordMatch,
            'hash_length' => strlen($user['password_hash'])
        ],
        'login_conditions' => [
            'user_exists' => true,
            'user_active' => (bool)$user['is_active'],
            'shop_exists' => $user['shop_id'] !== null,
            'shop_active' => $user['shop_active'] ? (bool)$user['shop_active'] : false,
            'password_correct' => $passwordMatch,
            'can_login' => (bool)$user['is_active'] && 
                          ($user['shop_active'] ? (bool)$user['shop_active'] : false) && 
                          $passwordMatch
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

