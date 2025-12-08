<?php
/**
 * adminユーザーのパスワードを修正するスクリプト
 * データベースに直接接続してパスワードを更新します
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDbConnection();
    
    $password = 'password123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // adminユーザーのパスワードを更新
    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = :password_hash 
        WHERE username = 'admin'
    ");
    $stmt->execute([':password_hash' => $passwordHash]);
    
    $affectedRows = $stmt->rowCount();
    
    // 更新後の確認
    $checkStmt = $pdo->prepare("
        SELECT username, password_hash 
        FROM users 
        WHERE username = 'admin'
    ");
    $checkStmt->execute();
    $user = $checkStmt->fetch();
    
    $verify = password_verify($password, $user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => $affectedRows > 0 ? 'パスワードを更新しました' : 'ユーザーが見つかりませんでした',
        'affected_rows' => $affectedRows,
        'password' => $password,
        'new_hash' => $passwordHash,
        'verify' => $verify,
        'user' => [
            'username' => $user['username'],
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

