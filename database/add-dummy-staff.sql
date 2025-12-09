-- ダミースタッフ追加スクリプト
-- 各店舗に複数のスタッフを追加します
-- パスワード: password123

SET NAMES utf8mb4;

-- 店舗IDを変数に保存
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);
SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002' LIMIT 1);
SET @shop3_id = (SELECT id FROM shops WHERE code = 'shop003' LIMIT 1);
SET @shop4_id = (SELECT id FROM shops WHERE code = 'shop004' LIMIT 1);
SET @shop5_id = (SELECT id FROM shops WHERE code = 'shop005' LIMIT 1);
SET @shop6_id = (SELECT id FROM shops WHERE code = 'shop006' LIMIT 1);
SET @shop7_id = (SELECT id FROM shops WHERE code = 'shop007' LIMIT 1);
SET @shop8_id = (SELECT id FROM shops WHERE code = 'shop008' LIMIT 1);

-- ============================================
-- shop001: レストラン イタリアン（スタッフ3人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop1_id, 'staff_italian_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '佐々木 健太', 'sasaki@example.com', 'staff', 1),
(@shop1_id, 'staff_italian_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '高橋 美咲', 'takahashi@example.com', 'staff', 1),
(@shop1_id, 'staff_italian_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '中村 翔太', 'nakamura@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop002: カフェ モカ（スタッフ2人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop2_id, 'staff_moka_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '渡辺 さくら', 'watanabe@example.com', 'staff', 1),
(@shop2_id, 'staff_moka_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '伊藤 大輔', 'ito@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop003: 和食 さくら（スタッフ3人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop3_id, 'staff_sakura_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '山本 優香', 'yamamoto@example.com', 'staff', 1),
(@shop3_id, 'staff_sakura_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '松本 健一', 'matsumoto@example.com', 'staff', 1),
(@shop3_id, 'staff_sakura_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '井上 麻衣', 'inoue@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop004: バーガーショップ ビーフ（スタッフ2人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop4_id, 'staff_beef_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '木村 拓也', 'kimura@example.com', 'staff', 1),
(@shop4_id, 'staff_beef_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '林 由美', 'hayashi@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop005: パスタハウス マンマ（スタッフ2人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop5_id, 'staff_mamma_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '斎藤 真一', 'saito@example.com', 'staff', 1),
(@shop5_id, 'staff_mamma_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '加藤 愛美', 'kato@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop006: ステーキハウス プレミアム（スタッフ3人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop6_id, 'staff_premium_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '吉田 雄一', 'yoshida@example.com', 'staff', 1),
(@shop6_id, 'staff_premium_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '後藤 美穂', 'goto@example.com', 'staff', 1),
(@shop6_id, 'staff_premium_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '近藤 翔', 'kondo@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop007: ラーメン屋 こだわり（スタッフ2人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop7_id, 'staff_ramen_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '長谷川 誠', 'hasegawa@example.com', 'staff', 1),
(@shop7_id, 'staff_ramen_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '橋本 彩', 'hashimoto@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop008: スイーツカフェ スイート（スタッフ2人）
-- ============================================
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop8_id, 'staff_sweet_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '石川 美咲', 'ishikawa@example.com', 'staff', 1),
(@shop8_id, 'staff_sweet_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '前田 健太', 'maeda@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================
-- shop_usersテーブルに追加（shop_usersテーブルが存在する場合）
-- ============================================
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
SELECT u.shop_id, u.id, u.role, 0
FROM users u
WHERE u.username LIKE 'staff_%'
AND NOT EXISTS (
    SELECT 1 FROM shop_users su 
    WHERE su.shop_id = u.shop_id AND su.user_id = u.id
);

-- ============================================
-- 完了メッセージ
-- ============================================
SELECT 
    'ダミースタッフの追加が完了しました！' AS message,
    (SELECT COUNT(*) FROM users WHERE username LIKE 'staff_%') AS staff_count,
    '全スタッフのパスワード: password123' AS password_info;


