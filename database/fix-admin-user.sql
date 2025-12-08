-- adminユーザーの作成・修正SQL
-- このSQLを実行すると、adminユーザーが正しく設定されます

SET NAMES utf8mb4;

-- 1. shop001のIDを取得（存在しない場合はエラーになるので注意）
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);

-- 2. adminユーザーが存在するか確認
SELECT 
    'adminユーザーの現在の状態:' AS info,
    username,
    name,
    role,
    is_active,
    shop_id,
    (SELECT code FROM shops WHERE id = shop_id) AS shop_code
FROM users 
WHERE username = 'seki';

-- 3. adminユーザーを作成または更新
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (
    @shop1_id, 
    'seki', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'システム管理者', 
    'admin@example.com', 
    'owner', 
    1
)
ON DUPLICATE KEY UPDATE
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `shop_id` = @shop1_id,
    `is_active` = 1,
    `name` = 'システム管理者',
    `email` = 'admin@example.com',
    `role` = 'owner';

-- 4. adminユーザーのIDを取得
SET @admin_id = (SELECT id FROM users WHERE username = 'seki' LIMIT 1);

-- 5. shop_usersテーブルが存在する場合は、全店舗に関連付け
-- テーブルの存在確認
SET @table_exists = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'shop_users'
);

-- shop_usersテーブルが存在する場合のみ実行
SET @sql = IF(@table_exists > 0,
    CONCAT('
        INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
        SELECT s.id, ', @admin_id, ', ''owner'', CASE WHEN s.code = ''shop001'' THEN 1 ELSE 0 END
        FROM shops s
        WHERE s.code IN (''shop001'', ''shop002'', ''shop003'', ''shop004'', ''shop005'', ''shop006'', ''shop007'', ''shop008'')
        AND s.is_active = 1
    '),
    'SELECT "shop_usersテーブルが存在しないため、スキップしました" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. 最終確認
SELECT 
    '修正後のadminユーザー状態:' AS info,
    u.username,
    u.name,
    u.role,
    u.is_active as user_active,
    u.shop_id,
    s.code as shop_code,
    s.name as shop_name,
    s.is_active as shop_active,
    CASE 
        WHEN u.is_active = 1 AND s.is_active = 1 THEN 'ログイン可能'
        ELSE 'ログイン不可（ユーザーまたは店舗が無効）'
    END AS login_status
FROM users u
LEFT JOIN shops s ON u.shop_id = s.id
WHERE u.username = 'seki';

