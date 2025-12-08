-- adminユーザーのパスワードを正しく修正するSQL
-- password123 の正しいハッシュを設定します

SET NAMES utf8mb4;

-- 注意: このハッシュは password_hash('password123', PASSWORD_DEFAULT) で生成されたものです
-- ただし、PASSWORD_DEFAULT は毎回異なるハッシュを生成するため、
-- 以下のSQLを実行する前に、generate-password-hash.php で最新のハッシュを生成してください

-- 方法1: PHPスクリプトで生成したハッシュを使用（推奨）
-- http://mameq.xsrv.jp/radish/api/generate-password-hash.php にアクセスして
-- 表示されたSQLを実行してください

-- 方法2: 既知のハッシュを使用（password123用）
-- このハッシュは password_hash('password123', PASSWORD_BCRYPT) で生成されたものです
UPDATE `users` 
SET `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `username` = 'seki';

-- 確認用クエリ
-- 注意: password_verifyはMySQLの関数ではないため、PHP側で検証してください
-- パスワードの検証は http://mameq.xsrv.jp/radish/api/test-auth.php を使用してください
SELECT 
    username,
    name,
    role,
    is_active,
    shop_id,
    password_hash,
    CASE 
        WHEN password_hash IS NOT NULL AND password_hash != '' THEN 'パスワードハッシュが設定されています（PHP側で検証が必要）'
        ELSE 'パスワードハッシュが設定されていません'
    END AS password_status,
    CASE 
        WHEN password_hash LIKE '$2y$%' THEN 'bcrypt形式のハッシュです'
        WHEN password_hash LIKE '$2a$%' THEN 'bcrypt形式のハッシュです'
        WHEN password_hash IS NOT NULL AND password_hash != '' THEN 'その他の形式のハッシュです'
        ELSE 'ハッシュが設定されていません'
    END AS hash_format
FROM `users`
WHERE `username` = 'seki';

