<?php
/**
 * 認証テスト用スクリプト
 * ユーザーの存在確認とパスワード検証
 * 使用方法: ?username=seki または ?username=admin
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

try {
    $pdo = getDbConnection();
    
    // クエリパラメータからユーザー名を取得（デフォルトはadmin）
    $username = $_GET['username'] ?? 'admin';
    $testPassword = $_GET['password'] ?? 'password123';
    
    // ユーザーの存在確認
    $stmt = $pdo->prepare("
        SELECT u.*, s.code as shop_code, s.name as shop_name, s.is_active as shop_active
        FROM users u
        LEFT JOIN shops s ON u.shop_id = s.id
        WHERE u.username = :username
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'error' => 'User not found',
            'message' => "ユーザー '{$username}' がデータベースに存在しません",
            'tested_username' => $username
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // パスワード検証テスト
    $passwordMatch = password_verify($testPassword, $user['password_hash']);
    
    // ログインできない理由を確認
    $reasons = [];
    if (!$user['is_active']) {
        $reasons[] = 'ユーザーが無効化されています';
    }
    if (!$user['shop_id']) {
        $reasons[] = '店舗が関連付けられていません';
    } elseif (!$user['shop_active']) {
        $reasons[] = '店舗が無効化されています';
    }
    if (!$passwordMatch) {
        $reasons[] = 'パスワードが一致しません';
    }
    
    // 結果を返す
    echo json_encode([
        'user_found' => true,
        'tested_username' => $username,
        'tested_password' => $testPassword,
        'user' => [
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_active' => (bool)$user['is_active'],
            'shop_id' => $user['shop_id'],
            'shop_code' => $user['shop_code'],
            'shop_name' => $user['shop_name'],
            'shop_active' => $user['shop_active'] ? (bool)$user['shop_active'] : null,
        ],
        'password_test' => [
            'test_password' => $testPassword,
            'password_match' => $passwordMatch,
            'hash_length' => strlen($user['password_hash']),
            'hash_preview' => substr($user['password_hash'], 0, 20) . '...'
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
        ],
        'login_failure_reasons' => $reasons
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

