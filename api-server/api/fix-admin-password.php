<?php
/**
 * ユーザーのパスワードを修正するスクリプト
 * データベースに直接接続してパスワードを更新します
 * 使用方法: ?username=seki または ?username=admin
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

try {
    $pdo = getDbConnection();
    
    // クエリパラメータからユーザー名を取得（デフォルトはadmin）
    $username = $_GET['username'] ?? 'admin';
    $password = 'password123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // ユーザーのパスワードを更新
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = :password_hash 
        WHERE username = :username
    ");
    $stmt->execute([
        ':password_hash' => $passwordHash,
        ':username' => $username
    ]);
    
    $affectedRows = $stmt->rowCount();
    
    // 更新後の確認
    $checkStmt = $pdo->prepare("
        SELECT username, password_hash, name, role, is_active, shop_id
        FROM users 
        WHERE username = :username
    ");
    $checkStmt->execute([':username' => $username]);
    $user = $checkStmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'error' => 'User not found',
            'message' => "ユーザー '{$username}' が見つかりませんでした"
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    $verify = password_verify($password, $user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => $affectedRows > 0 ? 'パスワードを更新しました' : 'ユーザーが見つかりませんでした',
        'affected_rows' => $affectedRows,
        'username' => $username,
        'password' => $password,
        'new_hash' => $passwordHash,
        'verify' => $verify,
        'user' => [
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
            'is_active' => (bool)$user['is_active'],
            'shop_id' => $user['shop_id'],
            'hash_in_db' => $user['password_hash']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

