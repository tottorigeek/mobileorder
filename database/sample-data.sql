-- サンプルデータ（ダミーデータ）
-- ユーザー5人、店舗8店舗、メニュー100点
-- 既存のスキーマを実行した後に実行してください
--
-- 【重要】sekiユーザーについて
-- このスクリプトは既存のsekiユーザーを保護します。
-- sekiユーザーが既に存在する場合、削除・再作成は行わず、既存のデータ（パスワード、設定など）をそのまま保持します。
-- sekiユーザーが存在しない場合のみ、新規作成されます。
-- 既存のsekiユーザーのパスワードや設定を変更したい場合は、該当部分のコメントアウトを解除してください。

SET NAMES utf8mb4;

-- ============================================
-- 既存データの削除（重複を避けるため）
-- ============================================

-- 外部キー制約を一時的に無効化（MySQLの場合）
SET FOREIGN_KEY_CHECKS = 0;

-- 既存のメニューを削除（店舗IDを直接取得）
DELETE m FROM `menus` m
INNER JOIN `shops` s ON m.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 既存のshop_users関連を削除（テーブルが存在する場合）
DELETE su FROM `shop_users` su
INNER JOIN `shops` s ON su.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 既存のユーザーを削除
DELETE FROM `users` WHERE `username` IN ('admin', 'owner_multi', 'owner_sakura', 'manager_beef', 'staff_mamma', 'owner_premium');

-- 既存の店舗を削除
DELETE FROM `shops` WHERE `code` IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 外部キー制約を再有効化
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 店舗データ（8店舗）
-- ============================================

INSERT INTO `shops` (`code`, `name`, `description`, `address`, `phone`, `email`, `max_tables`, `is_active`) VALUES
('shop001', 'レストラン イタリアン', '本格的なイタリアン料理を提供するレストラン', '東京都渋谷区神南1-1-1', '03-1234-5678', 'shop001@example.com', 25, 1),
('shop002', 'カフェ モカ', 'コーヒーと軽食が楽しめるカフェ', '東京都新宿区新宿3-2-1', '03-2345-6789', 'shop002@example.com', 20, 1),
('shop003', '和食 さくら', '伝統的な和食を提供する日本料理店', '東京都港区六本木4-3-2', '03-3456-7890', 'shop003@example.com', 30, 1),
('shop004', 'バーガーショップ ビーフ', 'ジューシーなハンバーガー専門店', '東京都世田谷区三軒茶屋5-4-3', '03-4567-8901', 'shop004@example.com', 15, 1),
('shop005', 'パスタハウス マンマ', '手作りパスタが自慢のイタリアン', '東京都目黒区目黒6-5-4', '03-5678-9012', 'shop005@example.com', 22, 1),
('shop006', 'ステーキハウス プレミアム', '高級ステーキを提供するレストラン', '東京都千代田区丸の内7-6-5', '03-6789-0123', 'shop006@example.com', 18, 1),
('shop007', 'ラーメン屋 こだわり', 'こだわりのスープと麺が自慢', '東京都台東区上野8-7-6', '03-7890-1234', 'shop007@example.com', 12, 1),
('shop008', 'スイーツカフェ スイート', '手作りスイーツとドリンクが楽しめるカフェ', '東京都中央区銀座9-8-7', '03-8901-2345', 'shop008@example.com', 16, 1);

-- 店舗IDを変数に保存
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001');
SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002');
SET @shop3_id = (SELECT id FROM shops WHERE code = 'shop003');
SET @shop4_id = (SELECT id FROM shops WHERE code = 'shop004');
SET @shop5_id = (SELECT id FROM shops WHERE code = 'shop005');
SET @shop6_id = (SELECT id FROM shops WHERE code = 'shop006');
SET @shop7_id = (SELECT id FROM shops WHERE code = 'shop007');
SET @shop8_id = (SELECT id FROM shops WHERE code = 'shop008');

-- ============================================
-- ユーザーデータ（6人：店舗ユーザー5人 + サービス管理者1人）
-- ============================================

-- サービス全体の管理者（全店舗を管理）
-- shop_idは最初の店舗を設定（実際には全店舗にアクセス可能）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop1_id, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'システム管理者', 'admin@example.com', 'owner', 1);
SET @admin_id = LAST_INSERT_ID();

-- ユーザー1: 複数店舗オーナー（shop001, shop002）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop1_id, 'owner_multi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '山田 太郎', 'yamada@example.com', 'owner', 1);
SET @user1_id = LAST_INSERT_ID();

-- ユーザー2: shop003のオーナー
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop3_id, 'owner_sakura', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '佐藤 花子', 'sato@example.com', 'owner', 1);
SET @user2_id = LAST_INSERT_ID();

-- ユーザー3: shop004のマネージャー
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop4_id, 'manager_beef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '鈴木 一郎', 'suzuki@example.com', 'manager', 1);
SET @user3_id = LAST_INSERT_ID();

-- ユーザー4: shop005のスタッフ
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop5_id, 'staff_mamma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '田中 次郎', 'tanaka@example.com', 'staff', 1);
SET @user4_id = LAST_INSERT_ID();

-- ユーザー5: shop006, shop007, shop008のオーナー（複数店舗）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop6_id, 'owner_premium', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '伊藤 三郎', 'ito@example.com', 'owner', 1)
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `email` = VALUES(`email`),
  `role` = VALUES(`role`),
  `is_active` = VALUES(`is_active`);
SET @user5_id = (SELECT id FROM users WHERE username = 'owner_premium' LIMIT 1);

-- ユーザー6: seki（管理者、全店舗にアクセス可能）
-- 【重要】sekiユーザーは既存のデータを保護するため、削除・再作成を行いません
-- 既存のsekiユーザーが存在する場合はそのまま使用し、存在しない場合のみ作成します

-- 既存のsekiユーザーIDを取得
SET @existing_seki_id = (SELECT id FROM `users` WHERE `username` = 'seki' LIMIT 1);

-- sekiユーザーが存在しない場合のみ作成
-- 注意: 既存のsekiユーザーがある場合は、パスワードや設定を変更しません
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
SELECT @shop1_id, 'seki', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '関 力仁', 'seki_r@yamata.co.jp', 'owner', 1
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'seki');

-- sekiユーザーIDを設定（既存の場合は既存ID、新規作成の場合はLAST_INSERT_ID）
SET @seki_id = COALESCE(@existing_seki_id, LAST_INSERT_ID());

-- 既存のsekiユーザーが存在しない場合のみ、IDを再取得
SET @seki_id = COALESCE(@seki_id, (SELECT id FROM `users` WHERE `username` = 'seki' LIMIT 1));

-- 【コメントアウト】既存のsekiユーザーを削除する処理（sekiユーザー保護のため無効化）
-- SET @existing_seki_id = (SELECT id FROM `users` WHERE `username` = 'seki' LIMIT 1);
-- DELETE FROM `shop_users` WHERE `user_id` = @existing_seki_id;
-- DELETE FROM `users` WHERE `username` = 'seki';

-- 【コメントアウト】sekiユーザーのパスワードと状態を強制的に設定する処理（既存データ保護のため無効化）
-- 注意: 既存のsekiユーザーのパスワードや設定を変更したくない場合は、この処理をコメントアウトしたままにしてください
-- UPDATE `users` 
-- SET `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
--     `is_active` = 1,
--     `shop_id` = COALESCE(`shop_id`, @shop1_id),
--     `updated_at` = NOW()
-- WHERE `username` = 'seki';

-- shop_usersテーブルが存在する場合は、複数店舗の関連を追加
-- サービス管理者は全店舗にアクセス可能（shop_usersテーブルに全店舗を追加）
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) VALUES
(@shop1_id, @admin_id, 'owner', 1),
(@shop2_id, @admin_id, 'owner', 0),
(@shop3_id, @admin_id, 'owner', 0),
(@shop4_id, @admin_id, 'owner', 0),
(@shop5_id, @admin_id, 'owner', 0),
(@shop6_id, @admin_id, 'owner', 0),
(@shop7_id, @admin_id, 'owner', 0),
(@shop8_id, @admin_id, 'owner', 0);

-- sekiユーザーは全店舗にアクセス可能（shop_usersテーブルに全店舗を追加）
-- 注意: INSERT IGNOREを使用しているため、既存のshop_users関連データは保護されます
-- 既存のsekiユーザーが存在する場合も、shop_usersへの追加は既存データを上書きしません
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) VALUES
(@shop1_id, @seki_id, 'owner', 1),
(@shop2_id, @seki_id, 'owner', 0),
(@shop3_id, @seki_id, 'owner', 0),
(@shop4_id, @seki_id, 'owner', 0),
(@shop5_id, @seki_id, 'owner', 0),
(@shop6_id, @seki_id, 'owner', 0),
(@shop7_id, @seki_id, 'owner', 0),
(@shop8_id, @seki_id, 'owner', 0);

-- shop_usersテーブルが存在する場合は、複数店舗の関連を追加
-- ユーザー1: shop001（主店舗）, shop002
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) VALUES
(@shop1_id, @user1_id, 'owner', 1),
(@shop2_id, @user1_id, 'owner', 0);

-- ユーザー5: shop006（主店舗）, shop007, shop008
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) VALUES
(@shop6_id, @user5_id, 'owner', 1),
(@shop7_id, @user5_id, 'owner', 0),
(@shop8_id, @user5_id, 'owner', 0);

-- ============================================
-- ダミースタッフデータ（19人）
-- ============================================

-- shop001: レストラン イタリアン（スタッフ3人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop1_id, 'staff_italian_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '佐々木 健太', 'sasaki@example.com', 'staff', 1),
(@shop1_id, 'staff_italian_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '高橋 美咲', 'takahashi@example.com', 'staff', 1),
(@shop1_id, 'staff_italian_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '中村 翔太', 'nakamura@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop002: カフェ モカ（スタッフ2人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop2_id, 'staff_moka_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '渡辺 さくら', 'watanabe@example.com', 'staff', 1),
(@shop2_id, 'staff_moka_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '伊藤 大輔', 'ito@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop003: 和食 さくら（スタッフ3人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop3_id, 'staff_sakura_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '山本 優香', 'yamamoto@example.com', 'staff', 1),
(@shop3_id, 'staff_sakura_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '松本 健一', 'matsumoto@example.com', 'staff', 1),
(@shop3_id, 'staff_sakura_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '井上 麻衣', 'inoue@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop004: バーガーショップ ビーフ（スタッフ2人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop4_id, 'staff_beef_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '木村 拓也', 'kimura@example.com', 'staff', 1),
(@shop4_id, 'staff_beef_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '林 由美', 'hayashi@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop005: パスタハウス マンマ（スタッフ2人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop5_id, 'staff_mamma_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '斎藤 真一', 'saito@example.com', 'staff', 1),
(@shop5_id, 'staff_mamma_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '加藤 愛美', 'kato@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop006: ステーキハウス プレミアム（スタッフ3人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop6_id, 'staff_premium_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '吉田 雄一', 'yoshida@example.com', 'staff', 1),
(@shop6_id, 'staff_premium_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '後藤 美穂', 'goto@example.com', 'staff', 1),
(@shop6_id, 'staff_premium_03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '近藤 翔', 'kondo@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop007: ラーメン屋 こだわり（スタッフ2人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop7_id, 'staff_ramen_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '長谷川 誠', 'hasegawa@example.com', 'staff', 1),
(@shop7_id, 'staff_ramen_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '橋本 彩', 'hashimoto@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop008: スイーツカフェ スイート（スタッフ2人）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) VALUES
(@shop8_id, 'staff_sweet_01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '石川 美咲', 'ishikawa@example.com', 'staff', 1),
(@shop8_id, 'staff_sweet_02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '前田 健太', 'maeda@example.com', 'staff', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- shop_usersテーブルに追加（shop_usersテーブルが存在する場合）
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
SELECT u.shop_id, u.id, u.role, 0
FROM users u
WHERE u.username LIKE 'staff_%'
AND NOT EXISTS (
    SELECT 1 FROM shop_users su 
    WHERE su.shop_id = u.shop_id AND su.user_id = u.id
);

-- ============================================
-- メニューデータ（100点）
-- ============================================

-- shop001: レストラン イタリアン（15点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop1_id, '001', 'マルゲリータピザ', 'トマトとモッツァレラチーズの定番ピザ', 1200, 'food', 1, 1),
(@shop1_id, '002', 'カルボナーラ', 'クリーミーなパスタ', 1100, 'food', 1, 1),
(@shop1_id, '003', 'アラビアータ', '辛めのトマトソースパスタ', 1050, 'food', 1, 0),
(@shop1_id, '004', 'ミラノ風カツレツ', 'ジューシーな仔牛のカツレツ', 1800, 'food', 1, 1),
(@shop1_id, '005', 'シーザーサラダ', '新鮮なレタスとシーザードレッシング', 800, 'food', 1, 0),
(@shop1_id, '006', 'イタリアンサラダ', 'トマトとモッツァレラのサラダ', 750, 'food', 1, 0),
(@shop1_id, '007', 'ミネストローネ', '具だくさんの野菜スープ', 600, 'food', 1, 0),
(@shop1_id, '008', 'エスプレッソ', '本格的なイタリアンコーヒー', 400, 'drink', 1, 0),
(@shop1_id, '009', 'カプチーノ', 'ふわふわのミルクフォーム', 450, 'drink', 1, 1),
(@shop1_id, '010', 'イタリアンレモネード', 'さわやかなレモネード', 500, 'drink', 1, 0),
(@shop1_id, '011', 'ワイン（グラス）', 'イタリアワイン', 600, 'drink', 1, 1),
(@shop1_id, '012', 'ティラミス', '本格的なイタリアンデザート', 700, 'dessert', 1, 1),
(@shop1_id, '013', 'パンナコッタ', 'なめらかなイタリアンデザート', 650, 'dessert', 1, 0),
(@shop1_id, '014', 'ジェラート', '本格的なイタリアンアイス', 550, 'dessert', 1, 1),
(@shop1_id, '015', 'アフォガート', 'ジェラートにエスプレッソ', 750, 'dessert', 1, 0);

-- shop002: カフェ モカ（12点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop2_id, '001', 'ブレンドコーヒー', 'こだわりのブレンドコーヒー', 450, 'drink', 1, 1),
(@shop2_id, '002', 'カフェラテ', 'ミルクたっぷりのラテ', 500, 'drink', 1, 1),
(@shop2_id, '003', 'カプチーノ', 'ふわふわのカプチーノ', 500, 'drink', 1, 0),
(@shop2_id, '004', 'エスプレッソ', '濃厚なエスプレッソ', 400, 'drink', 1, 0),
(@shop2_id, '005', '紅茶', 'アールグレイ', 450, 'drink', 1, 0),
(@shop2_id, '006', 'オレンジジュース', 'フレッシュなオレンジジュース', 400, 'drink', 1, 0),
(@shop2_id, '007', 'サンドイッチ', 'ハムとチーズのサンドイッチ', 650, 'food', 1, 1),
(@shop2_id, '008', 'クロワッサン', 'バターたっぷりのクロワッサン', 350, 'food', 1, 0),
(@shop2_id, '009', 'パンケーキ', 'ふわふわのパンケーキ', 800, 'food', 1, 1),
(@shop2_id, '010', 'チーズケーキ', '濃厚なチーズケーキ', 600, 'dessert', 1, 1),
(@shop2_id, '011', 'チョコレートケーキ', 'リッチなチョコレートケーキ', 650, 'dessert', 1, 0),
(@shop2_id, '012', 'マフィン', '手作りマフィン', 400, 'dessert', 1, 0);

-- shop003: 和食 さくら（13点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop3_id, '001', '刺身盛り合わせ', '新鮮な刺身の盛り合わせ', 1800, 'food', 1, 1),
(@shop3_id, '002', '天ぷら定食', 'エビと野菜の天ぷら定食', 1200, 'food', 1, 1),
(@shop3_id, '003', '寿司セット', '握り寿司10貫', 2000, 'food', 1, 1),
(@shop3_id, '004', 'うな重', '特製のうな重', 2500, 'food', 1, 0),
(@shop3_id, '005', '親子丼', 'とろとろの親子丼', 900, 'food', 1, 0),
(@shop3_id, '006', '天丼', '天ぷらたっぷりの天丼', 1100, 'food', 1, 0),
(@shop3_id, '007', '味噌汁', '本格的な味噌汁', 200, 'food', 1, 0),
(@shop3_id, '008', 'ご飯', '白米', 200, 'food', 1, 0),
(@shop3_id, '009', '日本酒（1合）', 'こだわりの日本酒', 500, 'drink', 1, 1),
(@shop3_id, '010', 'ビール', '生ビール', 500, 'drink', 1, 1),
(@shop3_id, '011', '緑茶', '本格的な緑茶', 300, 'drink', 1, 0),
(@shop3_id, '012', '抹茶アイス', '本格的な抹茶アイス', 500, 'dessert', 1, 1),
(@shop3_id, '013', 'わらび餅', '伝統的な和菓子', 450, 'dessert', 1, 0);

-- shop004: バーガーショップ ビーフ（13点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop4_id, '001', 'クラシックバーガー', 'ジューシーなハンバーガー', 800, 'food', 1, 1),
(@shop4_id, '002', 'チーズバーガー', 'チーズたっぷりのバーガー', 900, 'food', 1, 1),
(@shop4_id, '003', 'ベーコンバーガー', 'ベーコンとチーズのバーガー', 1000, 'food', 1, 0),
(@shop4_id, '004', 'ダブルバーガー', '肉2枚のボリューム満点', 1200, 'food', 1, 1),
(@shop4_id, '005', 'チキンバーガー', 'ジューシーなチキンバーガー', 750, 'food', 1, 0),
(@shop4_id, '006', 'フライドポテト', 'カリッと揚げたポテト', 400, 'food', 1, 1),
(@shop4_id, '007', 'オニオンリング', 'サクサクのオニオンリング', 450, 'food', 1, 0),
(@shop4_id, '008', 'チキンナゲット', 'ジューシーなチキンナゲット', 500, 'food', 1, 0),
(@shop4_id, '009', 'コーラ', '冷たいコーラ', 300, 'drink', 1, 0),
(@shop4_id, '010', 'オレンジジュース', 'フレッシュなオレンジジュース', 350, 'drink', 1, 0),
(@shop4_id, '011', 'レモネード', 'さわやかなレモネード', 350, 'drink', 1, 0),
(@shop4_id, '012', 'シェイク', 'バニラシェイク', 500, 'drink', 1, 1),
(@shop4_id, '013', 'アップルパイ', 'サクサクのアップルパイ', 450, 'dessert', 1, 0);

-- shop005: パスタハウス マンマ（12点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop5_id, '001', 'ボロネーゼ', '本格的なミートソース', 1100, 'food', 1, 1),
(@shop5_id, '002', 'カルボナーラ', 'クリーミーなカルボナーラ', 1200, 'food', 1, 1),
(@shop5_id, '003', 'ペペロンチーノ', 'シンプルで美味しい', 900, 'food', 1, 0),
(@shop5_id, '004', 'アラビアータ', '辛めのトマトソース', 1000, 'food', 1, 0),
(@shop5_id, '005', 'ナポリタン', 'ケチャップベースのパスタ', 950, 'food', 1, 0),
(@shop5_id, '006', 'ミートソース', '手作りミートソース', 1100, 'food', 1, 0),
(@shop5_id, '007', 'ガーリックトースト', '香ばしいガーリックトースト', 400, 'food', 1, 0),
(@shop5_id, '008', 'シーザーサラダ', '新鮮なシーザーサラダ', 700, 'food', 1, 0),
(@shop5_id, '009', 'ワイン（グラス）', 'イタリアワイン', 600, 'drink', 1, 1),
(@shop5_id, '010', 'コーラ', '冷たいコーラ', 300, 'drink', 1, 0),
(@shop5_id, '011', 'ティラミス', '本格的なティラミス', 700, 'dessert', 1, 1),
(@shop5_id, '012', 'ジェラート', '本格的なジェラート', 550, 'dessert', 1, 0);

-- shop006: ステーキハウス プレミアム（13点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop6_id, '001', 'サーロインステーキ', '最高級のサーロイン', 3500, 'food', 1, 1),
(@shop6_id, '002', 'フィレステーキ', '柔らかいフィレ', 4000, 'food', 1, 1),
(@shop6_id, '003', 'リブロースステーキ', 'ジューシーなリブロース', 3800, 'food', 1, 0),
(@shop6_id, '004', 'ハンバーグステーキ', '手作りハンバーグ', 1500, 'food', 1, 0),
(@shop6_id, '005', 'シーザーサラダ', '本格的なシーザーサラダ', 900, 'food', 1, 0),
(@shop6_id, '006', 'オニオンスープ', '濃厚なオニオンスープ', 600, 'food', 1, 0),
(@shop6_id, '007', 'ガーリックライス', '香ばしいガーリックライス', 400, 'food', 1, 0),
(@shop6_id, '008', 'フライドポテト', 'カリッと揚げたポテト', 500, 'food', 1, 0),
(@shop6_id, '009', 'ワイン（ボトル）', '高級ワイン', 3000, 'drink', 1, 1),
(@shop6_id, '010', 'ビール', '生ビール', 600, 'drink', 1, 0),
(@shop6_id, '011', 'コーラ', '冷たいコーラ', 400, 'drink', 1, 0),
(@shop6_id, '012', 'チョコレートケーキ', 'リッチなチョコレートケーキ', 800, 'dessert', 1, 1),
(@shop6_id, '013', 'アイスクリーム', 'バニラアイスクリーム', 500, 'dessert', 1, 0);

-- shop007: ラーメン屋 こだわり（11点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop7_id, '001', '醤油ラーメン', 'こだわりの醤油スープ', 750, 'food', 1, 1),
(@shop7_id, '002', '味噌ラーメン', '濃厚な味噌スープ', 800, 'food', 1, 1),
(@shop7_id, '003', '塩ラーメン', 'あっさりした塩スープ', 750, 'food', 1, 0),
(@shop7_id, '004', 'とんこつラーメン', '濃厚なとんこつスープ', 850, 'food', 1, 1),
(@shop7_id, '005', 'チャーシュー麺', 'チャーシューたっぷり', 950, 'food', 1, 0),
(@shop7_id, '006', '味玉ラーメン', '味玉付きラーメン', 900, 'food', 1, 0),
(@shop7_id, '007', 'チャーシュー丼', 'チャーシューたっぷりの丼', 600, 'food', 1, 0),
(@shop7_id, '008', '餃子', '手作り餃子（5個）', 500, 'food', 1, 1),
(@shop7_id, '009', 'ライス', '白米', 200, 'food', 1, 0),
(@shop7_id, '010', 'ビール', '生ビール', 500, 'drink', 1, 0),
(@shop7_id, '011', 'コーラ', '冷たいコーラ', 300, 'drink', 1, 0);

-- shop008: スイーツカフェ スイート（11点）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop8_id, '001', 'ショートケーキ', 'ふわふわのショートケーキ', 650, 'dessert', 1, 1),
(@shop8_id, '002', 'チョコレートケーキ', 'リッチなチョコレートケーキ', 700, 'dessert', 1, 1),
(@shop8_id, '003', 'チーズケーキ', '濃厚なチーズケーキ', 650, 'dessert', 1, 0),
(@shop8_id, '004', 'モンブラン', '本格的なモンブラン', 750, 'dessert', 1, 1),
(@shop8_id, '005', 'パフェ', 'フルーツたっぷりのパフェ', 900, 'dessert', 1, 1),
(@shop8_id, '006', 'アイスクリーム', 'バニラアイスクリーム', 450, 'dessert', 1, 0),
(@shop8_id, '007', 'マカロン', 'カラフルなマカロン（3個）', 600, 'dessert', 1, 0),
(@shop8_id, '008', 'ブレンドコーヒー', 'こだわりのブレンドコーヒー', 450, 'drink', 1, 0),
(@shop8_id, '009', 'カフェラテ', 'ミルクたっぷりのラテ', 500, 'drink', 1, 0),
(@shop8_id, '010', '紅茶', 'アールグレイ', 450, 'drink', 1, 0),
(@shop8_id, '011', 'フレッシュジュース', '季節のフルーツジュース', 550, 'drink', 1, 0);

-- 合計: 15 + 12 + 13 + 13 + 12 + 13 + 11 + 11 = 100点

-- ============================================
-- カテゴリデータ（日本語ラベル）
-- ============================================

-- 既存のカテゴリを削除（重複を避けるため）
DELETE FROM `shop_categories` 
WHERE `shop_id` IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id);

-- shop001: レストラン イタリアン
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop1_id, 'food', '食べ物', 1, 1),
(@shop1_id, 'drink', '飲み物', 2, 1),
(@shop1_id, 'dessert', 'デザート', 3, 1);

-- shop002: カフェ モカ
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop2_id, 'drink', 'ドリンク', 1, 1),
(@shop2_id, 'food', '軽食', 2, 1),
(@shop2_id, 'dessert', 'スイーツ', 3, 1);

-- shop003: 和食 さくら
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop3_id, 'food', '和食', 1, 1),
(@shop3_id, 'drink', '飲み物', 2, 1),
(@shop3_id, 'dessert', '和菓子', 3, 1);

-- shop004: バーガーショップ ビーフ
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop4_id, 'food', 'バーガー', 1, 1),
(@shop4_id, 'drink', 'ドリンク', 2, 1),
(@shop4_id, 'dessert', 'デザート', 3, 1);

-- shop005: パスタハウス マンマ
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop5_id, 'food', 'パスタ', 1, 1),
(@shop5_id, 'drink', 'ドリンク', 2, 1),
(@shop5_id, 'dessert', 'デザート', 3, 1);

-- shop006: ステーキハウス プレミアム
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop6_id, 'food', 'ステーキ', 1, 1),
(@shop6_id, 'drink', 'ドリンク', 2, 1),
(@shop6_id, 'dessert', 'デザート', 3, 1);

-- shop007: ラーメン屋 こだわり
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop7_id, 'food', 'ラーメン', 1, 1),
(@shop7_id, 'drink', 'ドリンク', 2, 1);

-- shop008: スイーツカフェ スイート
INSERT INTO `shop_categories` (`shop_id`, `code`, `name`, `display_order`, `is_active`) VALUES
(@shop8_id, 'dessert', 'スイーツ', 1, 1),
(@shop8_id, 'drink', 'ドリンク', 2, 1);

-- ============================================
-- 営業時間・定休日データ
-- ============================================

-- shop001: レストラン イタリアン（毎週月曜日 + 毎月第1火曜日が定休日）
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'monday',
        JSON_OBJECT('type', 'monthly', 'day', 'tuesday', 'week', 1)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '23:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '23:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '22:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop001';

-- shop002: カフェ モカ
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(),
    'temporaryHolidays', JSON_ARRAY('2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '07:00', 'close', '20:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '07:00', 'close', '21:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '08:00', 'close', '21:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '08:00', 'close', '20:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop002';

-- shop003: 和食 さくら（毎週火曜日 + 毎月最終金曜日が定休日）
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'tuesday',
        JSON_OBJECT('type', 'monthly', 'day', 'friday', 'week', -1)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-30', '2024-12-31', '2025-01-01', '2025-01-02', '2025-01-03'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '14:30', 'isClosed', false)
    )
)
WHERE `code` = 'shop003';

-- shop004: バーガーショップ ビーフ
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(),
    'temporaryHolidays', JSON_ARRAY('2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '10:00', 'close', '23:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '10:00', 'close', '23:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '10:00', 'close', '22:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop004';

-- shop005: パスタハウス マンマ
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('monday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:30', 'close', '15:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop005';

-- shop006: ステーキハウス プレミアム（毎週月・火曜日 + 毎月第2水曜日が定休日）
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY(
        'monday',
        'tuesday',
        JSON_OBJECT('type', 'monthly', 'day', 'wednesday', 'week', 2)
    ),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01', '2025-01-02'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', true),
        'tuesday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '17:00', 'close', '23:30', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '17:00', 'close', '23:30', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '17:00', 'close', '23:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop006';

-- shop007: ラーメン屋 こだわり
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('wednesday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'wednesday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', true),
        'thursday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '11:00', 'close', '15:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop007';

-- shop008: スイーツカフェ スイート
UPDATE `shops` 
SET `settings` = JSON_OBJECT(
    'regularHolidays', JSON_ARRAY('tuesday'),
    'temporaryHolidays', JSON_ARRAY('2024-12-25', '2024-12-31', '2025-01-01'),
    'businessHours', JSON_OBJECT(
        'monday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'tuesday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', true),
        'wednesday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'thursday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false),
        'friday', JSON_OBJECT('open', '10:00', 'close', '21:00', 'isClosed', false),
        'saturday', JSON_OBJECT('open', '10:00', 'close', '21:00', 'isClosed', false),
        'sunday', JSON_OBJECT('open', '10:00', 'close', '20:00', 'isClosed', false)
    )
)
WHERE `code` = 'shop008';

-- ============================================
-- 店舗テーブル（座席）データ
-- ============================================

-- 既存のテーブルデータを削除（重複を避けるため）
DELETE st FROM `shop_tables` st
INNER JOIN `shops` s ON st.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- QRコードURLのベースURL（環境に応じて変更してください）
SET @base_url = 'http://localhost:3000';

-- shop001: レストラン イタリアン（15テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop1_id, '1', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=1')),
(@shop1_id, '2', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=2')),
(@shop1_id, '3', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=3')),
(@shop1_id, '4', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=4')),
(@shop1_id, '5', '中央席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=5')),
(@shop1_id, '6', '中央席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=6')),
(@shop1_id, '7', '個室', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=7')),
(@shop1_id, '8', '個室', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=8')),
(@shop1_id, '9', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=9')),
(@shop1_id, '10', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=10')),
(@shop1_id, '11', 'テラス席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=11')),
(@shop1_id, '12', 'テラス席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=12')),
(@shop1_id, '13', 'テラス席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=13')),
(@shop1_id, '14', 'VIP席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=14')),
(@shop1_id, '15', 'VIP席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop001&table=15'));

-- shop002: カフェ モカ（12テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop2_id, '1', '窓際席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=1')),
(@shop2_id, '2', '窓際席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=2')),
(@shop2_id, '3', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=3')),
(@shop2_id, '4', '中央席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=4')),
(@shop2_id, '5', '中央席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=5')),
(@shop2_id, '6', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=6')),
(@shop2_id, '7', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=7')),
(@shop2_id, '8', 'ソファ席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=8')),
(@shop2_id, '9', 'ソファ席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=9')),
(@shop2_id, '10', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=10')),
(@shop2_id, '11', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=11')),
(@shop2_id, '12', 'テラス席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop002&table=12'));

-- shop003: 和食 さくら（18テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop3_id, '1', '個室A', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=1')),
(@shop3_id, '2', '個室A', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=2')),
(@shop3_id, '3', '個室B', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=3')),
(@shop3_id, '4', '個室B', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=4')),
(@shop3_id, '5', '個室C', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=5')),
(@shop3_id, '6', '個室C', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=6')),
(@shop3_id, '7', '座敷', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=7')),
(@shop3_id, '8', '座敷', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=8')),
(@shop3_id, '9', '座敷', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=9')),
(@shop3_id, '10', '座敷', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=10')),
(@shop3_id, '11', '座敷', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=11')),
(@shop3_id, '12', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=12')),
(@shop3_id, '13', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=13')),
(@shop3_id, '14', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=14')),
(@shop3_id, '15', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=15')),
(@shop3_id, '16', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=16')),
(@shop3_id, '17', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=17')),
(@shop3_id, '18', 'VIP個室', 10, 1, CONCAT(@base_url, '/shop-select?shop=shop003&table=18'));

-- shop004: バーガーショップ ビーフ（10テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop4_id, '1', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=1')),
(@shop4_id, '2', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=2')),
(@shop4_id, '3', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=3')),
(@shop4_id, '4', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=4')),
(@shop4_id, '5', 'テーブル席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=5')),
(@shop4_id, '6', 'テーブル席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=6')),
(@shop4_id, '7', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=7')),
(@shop4_id, '8', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=8')),
(@shop4_id, '9', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=9')),
(@shop4_id, '10', 'テラス席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop004&table=10'));

-- shop005: パスタハウス マンマ（12テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop5_id, '1', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=1')),
(@shop5_id, '2', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=2')),
(@shop5_id, '3', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=3')),
(@shop5_id, '4', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=4')),
(@shop5_id, '5', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=5')),
(@shop5_id, '6', '中央席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=6')),
(@shop5_id, '7', '中央席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=7')),
(@shop5_id, '8', '個室', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=8')),
(@shop5_id, '9', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=9')),
(@shop5_id, '10', 'カウンター', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=10')),
(@shop5_id, '11', 'テラス席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=11')),
(@shop5_id, '12', 'テラス席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop005&table=12'));

-- shop006: ステーキハウス プレミアム（10テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop6_id, '1', 'VIP席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=1')),
(@shop6_id, '2', 'VIP席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=2')),
(@shop6_id, '3', 'VIP席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=3')),
(@shop6_id, '4', '個室A', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=4')),
(@shop6_id, '5', '個室A', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=5')),
(@shop6_id, '6', '個室B', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=6')),
(@shop6_id, '7', '個室B', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=7')),
(@shop6_id, '8', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=8')),
(@shop6_id, '9', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=9')),
(@shop6_id, '10', '特別室', 8, 1, CONCAT(@base_url, '/shop-select?shop=shop006&table=10'));

-- shop007: ラーメン屋 こだわり（8テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop7_id, '1', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=1')),
(@shop7_id, '2', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=2')),
(@shop7_id, '3', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=3')),
(@shop7_id, '4', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=4')),
(@shop7_id, '5', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=5')),
(@shop7_id, '6', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=6')),
(@shop7_id, '7', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=7')),
(@shop7_id, '8', 'テーブル席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=8'));

-- shop008: スイーツカフェ スイート（10テーブル）
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop8_id, '1', '窓際席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=1')),
(@shop8_id, '2', '窓際席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=2')),
(@shop8_id, '3', '窓際席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=3')),
(@shop8_id, '4', '中央席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=4')),
(@shop8_id, '5', '中央席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=5')),
(@shop8_id, '6', '中央席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=6')),
(@shop8_id, '7', 'ソファ席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=7')),
(@shop8_id, '8', 'ソファ席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=8')),
(@shop8_id, '9', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=9')),
(@shop8_id, '10', 'テラス席', 2, 1, CONCAT(@base_url, '/shop-select?shop=shop008&table=10'));

-- ============================================
-- 来店者（visitors）データ（直近3日分）
-- ============================================

-- 既存の来店者データを削除（重複を避けるため）
DELETE v FROM `visitors` v
INNER JOIN `shops` s ON v.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- テーブルIDを取得（各店舗の最初のテーブルを使用）
SET @shop1_table1_id = (SELECT id FROM shop_tables WHERE shop_id = @shop1_id AND table_number = '1' LIMIT 1);
SET @shop1_table2_id = (SELECT id FROM shop_tables WHERE shop_id = @shop1_id AND table_number = '2' LIMIT 1);
SET @shop2_table1_id = (SELECT id FROM shop_tables WHERE shop_id = @shop2_id AND table_number = '1' LIMIT 1);
SET @shop3_table1_id = (SELECT id FROM shop_tables WHERE shop_id = @shop3_id AND table_number = '1' LIMIT 1);

-- 【一昨日】shop001: レストラン イタリアン - 来店者1（支払い完了）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `checkout_time`, `total_amount`, `payment_method`, `payment_status`, `is_set_completed`) VALUES
(@shop1_id, @shop1_table1_id, '1', 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 19 HOUR, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 20 HOUR + INTERVAL 30 MINUTE, 3500, 'cash', 'completed', 1);
SET @visitor1_id = LAST_INSERT_ID();

-- 【昨日】shop002: カフェ モカ - 来店者2（支払い完了）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `checkout_time`, `total_amount`, `payment_method`, `payment_status`, `is_set_completed`) VALUES
(@shop2_id, @shop2_table1_id, '1', 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 18 HOUR, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 19 HOUR + INTERVAL 30 MINUTE, 1200, 'credit', 'completed', 1);
SET @visitor2_id = LAST_INSERT_ID();

-- 【今日】shop001: レストラン イタリアン - 来店者3（支払い完了、4時間前に来店）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `checkout_time`, `total_amount`, `payment_method`, `payment_status`, `is_set_completed`) VALUES
(@shop1_id, @shop1_table1_id, '1', 2, DATE_SUB(NOW(), INTERVAL 4 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR), 3500, 'cash', 'completed', 1);
SET @visitor3_id = LAST_INSERT_ID();

-- 【今日】shop001: レストラン イタリアン - 来店者4（着座済み、未注文、15分前に来店）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `total_amount`, `payment_status`, `is_set_completed`) VALUES
(@shop1_id, (SELECT id FROM shop_tables WHERE shop_id = @shop1_id AND table_number = '3' LIMIT 1), '3', 2, DATE_SUB(NOW(), INTERVAL 15 MINUTE), 0, 'pending', 1);
SET @visitor4_id = LAST_INSERT_ID();

-- 【今日】shop002: カフェ モカ - 来店者5（着座済み、未注文、10分前に来店）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `total_amount`, `payment_status`, `is_set_completed`) VALUES
(@shop2_id, (SELECT id FROM shop_tables WHERE shop_id = @shop2_id AND table_number = '2' LIMIT 1), '2', 3, DATE_SUB(NOW(), INTERVAL 10 MINUTE), 0, 'pending', 1);
SET @visitor5_id = LAST_INSERT_ID();

-- 【今日】shop003: 和食 さくら - 来店者6（着座済み、注文済み、30分前に来店）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `total_amount`, `payment_status`, `is_set_completed`) VALUES
(@shop3_id, @shop3_table1_id, '1', 3, DATE_SUB(NOW(), INTERVAL 30 MINUTE), 0, 'pending', 1);
SET @visitor6_id = LAST_INSERT_ID();

-- 【今日】shop004: バーガーショップ ビーフ - 来店者7（着座済み、未注文、5分前に来店）
INSERT INTO `visitors` (`shop_id`, `table_id`, `table_number`, `number_of_guests`, `arrival_time`, `total_amount`, `payment_status`, `is_set_completed`) VALUES
(@shop4_id, (SELECT id FROM shop_tables WHERE shop_id = @shop4_id AND table_number = '1' LIMIT 1), '1', 4, DATE_SUB(NOW(), INTERVAL 5 MINUTE), 0, 'pending', 1);
SET @visitor7_id = LAST_INSERT_ID();

-- shop_tablesテーブルのvisitor_idとstatusを更新
UPDATE `shop_tables` SET `visitor_id` = @visitor4_id, `status` = 'occupied' WHERE shop_id = @shop1_id AND table_number = '3';
UPDATE `shop_tables` SET `visitor_id` = @visitor5_id, `status` = 'occupied' WHERE shop_id = @shop2_id AND table_number = '2';
UPDATE `shop_tables` SET `visitor_id` = @visitor6_id, `status` = 'occupied' WHERE shop_id = @shop3_id AND table_number = '1';
UPDATE `shop_tables` SET `visitor_id` = @visitor7_id, `status` = 'occupied' WHERE shop_id = @shop4_id AND table_number = '1';

-- ============================================
-- 注文（orders）データ（直近3日分）
-- ============================================

-- 既存の注文データを削除（重複を避けるため）
DELETE o FROM `orders` o
INNER JOIN `shops` s ON o.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 【一昨日】shop001: テーブル1の注文1（完了）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop1_id, CONCAT('ORD-', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '%Y%m%d'), '-001'), '1', 'completed', 3500, DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 19 HOUR + INTERVAL 15 MINUTE);
SET @order1_id = LAST_INSERT_ID();

-- 【昨日】shop002: テーブル1の注文2（完了）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop2_id, CONCAT('ORD-', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y%m%d'), '-001'), '1', 'completed', 1200, DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 18 HOUR + INTERVAL 20 MINUTE);
SET @order2_id = LAST_INSERT_ID();

-- 【今日】shop001: テーブル2の注文3（調理中、3時間前に作成、来店者なし）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop1_id, CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-001'), '2', 'cooking', 3500, DATE_SUB(NOW(), INTERVAL 3 HOUR));
SET @order3_id = LAST_INSERT_ID();

-- 【今日】shop001: テーブル2の注文4（受付済み、1時間前に作成、来店者なし）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop1_id, CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-002'), '2', 'accepted', 2400, DATE_SUB(NOW(), INTERVAL 1 HOUR));
SET @order4_id = LAST_INSERT_ID();

-- 【今日】shop003: テーブル1の注文5（受付済み、25分前に作成、来店者6に関連）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop3_id, CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-001'), '1', 'accepted', 2800, DATE_SUB(NOW(), INTERVAL 25 MINUTE));
SET @order5_id = LAST_INSERT_ID();

-- 【今日】shop002: テーブル3の注文6（pending、20分前に作成、来店者なし）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop2_id, CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-001'), '3', 'pending', 1800, DATE_SUB(NOW(), INTERVAL 20 MINUTE));
SET @order6_id = LAST_INSERT_ID();

-- 【今日】shop004: テーブル2の注文7（調理中、45分前に作成、来店者なし）
INSERT INTO `orders` (`shop_id`, `order_number`, `table_number`, `status`, `total_amount`, `created_at`) VALUES
(@shop4_id, CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-001'), '2', 'cooking', 3200, DATE_SUB(NOW(), INTERVAL 45 MINUTE));
SET @order7_id = LAST_INSERT_ID();

-- ============================================
-- 注文アイテム（order_items）データ
-- ============================================

-- 既存の注文アイテムデータを削除（重複を避けるため）
DELETE oi FROM `order_items` oi
INNER JOIN `orders` o ON oi.order_id = o.id
INNER JOIN `shops` s ON o.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- メニューIDを取得
SET @shop1_menu1_id = (SELECT id FROM menus WHERE shop_id = @shop1_id AND number = '001' LIMIT 1);
SET @shop1_menu2_id = (SELECT id FROM menus WHERE shop_id = @shop1_id AND number = '002' LIMIT 1);
SET @shop1_menu3_id = (SELECT id FROM menus WHERE shop_id = @shop1_id AND number = '011' LIMIT 1);
SET @shop1_menu4_id = (SELECT id FROM menus WHERE shop_id = @shop1_id AND number = '012' LIMIT 1);
SET @shop2_menu1_id = (SELECT id FROM menus WHERE shop_id = @shop2_id AND number = '001' LIMIT 1);
SET @shop2_menu3_id = (SELECT id FROM menus WHERE shop_id = @shop2_id AND number = '003' LIMIT 1);
SET @shop2_menu9_id = (SELECT id FROM menus WHERE shop_id = @shop2_id AND number = '009' LIMIT 1);
SET @shop3_menu1_id = (SELECT id FROM menus WHERE shop_id = @shop3_id AND number = '001' LIMIT 1);
SET @shop3_menu2_id = (SELECT id FROM menus WHERE shop_id = @shop3_id AND number = '002' LIMIT 1);
SET @shop4_menu1_id = (SELECT id FROM menus WHERE shop_id = @shop4_id AND number = '001' LIMIT 1);
SET @shop4_menu2_id = (SELECT id FROM menus WHERE shop_id = @shop4_id AND number = '002' LIMIT 1);
SET @shop4_menu6_id = (SELECT id FROM menus WHERE shop_id = @shop4_id AND number = '006' LIMIT 1);

-- 【一昨日】注文1のアイテム（マルゲリータピザ、ワイン、ティラミス）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order1_id, @shop1_menu1_id, '001', 'マルゲリータピザ', 2, 1200),
(@order1_id, @shop1_menu3_id, '011', 'ワイン（グラス）', 2, 600),
(@order1_id, @shop1_menu4_id, '012', 'ティラミス', 1, 700);

-- 【昨日】注文2のアイテム（コーヒー、サンドイッチ）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order2_id, @shop2_menu1_id, '001', 'ブレンドコーヒー', 2, 450),
(@order2_id, @shop2_menu3_id, '003', 'サンドイッチ', 1, 650);

-- 【今日】注文3のアイテム（マルゲリータピザ、ワイン）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order3_id, @shop1_menu1_id, '001', 'マルゲリータピザ', 2, 1200),
(@order3_id, @shop1_menu3_id, '011', 'ワイン（グラス）', 2, 600);

-- 【今日】注文4のアイテム（カルボナーラ、シーザーサラダ）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order4_id, @shop1_menu2_id, '002', 'カルボナーラ', 2, 1100),
(@order4_id, (SELECT id FROM menus WHERE shop_id = @shop1_id AND number = '005' LIMIT 1), '005', 'シーザーサラダ', 1, 800);

-- 【今日】注文5のアイテム（刺身盛り合わせ、天ぷら定食）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order5_id, @shop3_menu1_id, '001', '刺身盛り合わせ', 1, 1800),
(@order5_id, @shop3_menu2_id, '002', '天ぷら定食', 1, 1200);

-- 【今日】注文6のアイテム（パンケーキ、コーヒー）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order6_id, @shop2_menu9_id, '009', 'パンケーキ', 1, 800),
(@order6_id, @shop2_menu1_id, '001', 'ブレンドコーヒー', 2, 450);

-- 【今日】注文7のアイテム（クラシックバーガー、チーズバーガー、フライドポテト）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order7_id, @shop4_menu1_id, '001', 'クラシックバーガー', 2, 800),
(@order7_id, @shop4_menu2_id, '002', 'チーズバーガー', 1, 900),
(@order7_id, @shop4_menu6_id, '006', 'フライドポテト', 2, 400);

-- 【今日】注文6のアイテム（パンケーキ、コーヒー）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order6_id, @shop2_menu9_id, '009', 'パンケーキ', 1, 800),
(@order6_id, @shop2_menu1_id, '001', 'ブレンドコーヒー', 2, 450);

-- 【今日】注文7のアイテム（クラシックバーガー、チーズバーガー、フライドポテト）
INSERT INTO `order_items` (`order_id`, `menu_id`, `menu_number`, `menu_name`, `quantity`, `price`) VALUES
(@order7_id, @shop4_menu1_id, '001', 'クラシックバーガー', 2, 800),
(@order7_id, @shop4_menu2_id, '002', 'チーズバーガー', 1, 900),
(@order7_id, @shop4_menu6_id, '006', 'フライドポテト', 2, 400);

-- ============================================
-- 会計（payments）データ
-- ============================================

-- 既存の会計データを削除（重複を避けるため）
DELETE p FROM `payments` p
INNER JOIN `orders` o ON p.order_id = o.id
INNER JOIN `shops` s ON o.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 注: 支払い済みの来店者（visitor1, visitor2, visitor3）は既にvisitorsテーブルに支払い情報が記録されているため、会計データは追加しません
-- 注文1（一昨日）と注文2（昨日）は完了済み、注文3-5（今日）はまだ支払い待ちのため、会計データは追加しません

-- ============================================
-- エラーログサンプルデータ（直近3日分）
-- ============================================

-- 既存のエラーログサンプルデータを削除（重複を避けるため）
-- 注意: error_logsテーブルが存在する場合のみ実行される
DELETE el FROM `error_logs` el
WHERE el.id > 0
AND EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'error_logs');

-- ユーザーIDを取得（スタッフユーザーを使用）
SET @staff_user1_id = (SELECT id FROM users WHERE username = 'staff_italian_01' LIMIT 1);

-- エラーログサンプルデータの挿入（error_logsテーブルが存在する場合のみ）
-- 注意: テーブルが存在しない場合はスキップされる
SET @error_logs_table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'error_logs');

-- エラーログサンプルデータ（5件、直近3日分に分散）
-- 注意: error_logsテーブルが存在する場合のみ実行
INSERT INTO `error_logs` (`level`, `environment`, `message`, `file`, `line`, `trace`, `user_id`, `shop_id`, `request_method`, `request_uri`, `ip_address`, `created_at`)
SELECT 'error', 'development', 'データベース接続エラー: Connection refused', '/api-server/config.php', 45, JSON_OBJECT('frames', JSON_ARRAY(JSON_OBJECT('file', '/api-server/config.php', 'line', 45, 'function', 'getDbConnection', 'class', NULL))), @staff_user1_id, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(CURDATE(), INTERVAL 2 DAY) + INTERVAL 14 HOUR
WHERE @error_logs_table_exists > 0
UNION ALL
SELECT 'info', 'production', 'ユーザーログイン成功', '/api-server/api/auth.php', 45, NULL, @staff_user1_id, @shop1_id, 'POST', '/api/auth/login', '192.168.1.100', DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 10 HOUR
WHERE @error_logs_table_exists > 0
UNION ALL
SELECT 'warning', 'production', 'リクエストパラメータが不正です: limit=abc', '/api-server/api/menus.php', 45, NULL, NULL, @shop1_id, 'GET', '/api/menus?limit=abc', '192.168.1.104', DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 15 HOUR
WHERE @error_logs_table_exists > 0
UNION ALL
SELECT 'error', 'production', '認証トークンが無効です', '/api-server/config.php', 123, JSON_OBJECT('frames', JSON_ARRAY(JSON_OBJECT('file', '/api-server/config.php', 'line', 123, 'function', 'checkAuth', 'class', NULL))), NULL, @shop1_id, 'POST', '/api/orders', '203.0.113.45', DATE_SUB(NOW(), INTERVAL 3 HOUR)
WHERE @error_logs_table_exists > 0
UNION ALL
SELECT 'debug', 'development', 'SQLクエリ実行: SELECT * FROM menus WHERE shop_id = 1', '/api-server/api/menus.php', 67, NULL, NULL, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 HOUR)
WHERE @error_logs_table_exists > 0;

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT 
    'サンプルデータの挿入が完了しました！' AS message,
    (SELECT COUNT(*) FROM shops WHERE code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008')) AS shop_count,
    (SELECT COUNT(*) FROM users WHERE username IN ('admin', 'owner_multi', 'owner_sakura', 'manager_beef', 'staff_mamma', 'owner_premium', 'seki') OR username LIKE 'staff_%') AS user_count,
    (SELECT COUNT(*) FROM menus) AS menu_count,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id)) AS table_count,
    (SELECT COUNT(*) FROM visitors WHERE shop_id IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id)) AS visitor_count,
    (SELECT COUNT(*) FROM orders WHERE shop_id IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id)) AS order_count,
    'サービス管理者アカウント: admin / password123' AS admin_account,
    '管理者アカウント: seki / password123' AS seki_account,
    '全スタッフのパスワード: password123' AS staff_password;

