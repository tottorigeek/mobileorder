<?php
/**
 * パスワードハッシュ生成スクリプト
 * password123 の正しいハッシュを生成します
 */

header('Content-Type: application/json; charset=utf-8');

$password = 'password123';

// パスワードハッシュを生成
$hash = password_hash($password, PASSWORD_DEFAULT);

// 検証
$verify = password_verify($password, $hash);

echo json_encode([
    'password' => $password,
    'hash' => $hash,
    'verify' => $verify,
    'sql' => "UPDATE `users` SET `password_hash` = '" . $hash . "' WHERE `username` = 'admin';"
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

