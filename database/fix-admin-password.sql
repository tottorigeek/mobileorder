-- adminユーザーのパスワードを修正するSQL
-- password123 の正しいハッシュを設定します

SET NAMES utf8mb4;

-- adminユーザーのパスワードを確認・修正
UPDATE `users` 
SET `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `username` = 'admin';

-- 確認用クエリ
SELECT 
    username,
    name,
    role,
    is_active,
    shop_id,
    CASE 
        WHEN password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
        THEN 'パスワードハッシュが正しく設定されています'
        ELSE 'パスワードハッシュが異なります'
    END AS password_status
FROM `users`
WHERE `username` = 'admin';

