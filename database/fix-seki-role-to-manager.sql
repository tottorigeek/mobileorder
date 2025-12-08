-- sekiユーザーのロールを「管理者（manager）」に変更するSQL
-- このSQLを実行すると、sekiユーザーが「管理者」として表示されます

SET NAMES utf8mb4;

-- 1. sekiユーザーの現在の状態を確認
SELECT 
    'sekiユーザーの現在の状態:' AS info,
    username,
    name,
    role,
    is_active,
    shop_id,
    (SELECT code FROM shops WHERE id = shop_id) AS shop_code
FROM users 
WHERE username = 'seki';

-- 2. sekiユーザーのロールを「manager」に変更
UPDATE `users` 
SET `role` = 'manager',
    `updated_at` = NOW()
WHERE `username` = 'seki';

-- 3. 変更後の状態を確認
SELECT 
    '変更後のsekiユーザー状態:' AS info,
    username,
    name,
    role,
    is_active,
    shop_id,
    (SELECT code FROM shops WHERE id = shop_id) AS shop_code,
    updated_at
FROM users 
WHERE username = 'seki';

