-- サンプルデータ（ダミーデータ）
-- ユーザー5人、店舗8店舗、メニュー100点
-- 既存のスキーマを実行した後に実行してください

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
-- 既存のsekiユーザーを削除（存在する場合）
SET @existing_seki_id = (SELECT id FROM `users` WHERE `username` = 'seki' LIMIT 1);
DELETE FROM `shop_users` WHERE `user_id` = @existing_seki_id;
DELETE FROM `users` WHERE `username` = 'seki';

-- sekiユーザーを追加
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (@shop1_id, 'seki', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '関 力仁', 'seki_r@yamata.co.jp', 'owner', 1);
SET @seki_id = LAST_INSERT_ID();

-- sekiユーザーのパスワードと状態を確実に設定
UPDATE `users` 
SET `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `is_active` = 1,
    `shop_id` = COALESCE(`shop_id`, @shop1_id),
    `updated_at` = NOW()
WHERE `username` = 'seki';

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
-- 完了メッセージ
-- ============================================

SELECT 
    'サンプルデータの挿入が完了しました！' AS message,
    (SELECT COUNT(*) FROM shops WHERE code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008')) AS shop_count,
    (SELECT COUNT(*) FROM users WHERE username IN ('admin', 'owner_multi', 'owner_sakura', 'manager_beef', 'staff_mamma', 'owner_premium', 'seki')) AS user_count,
    (SELECT COUNT(*) FROM menus) AS menu_count,
    'サービス管理者アカウント: admin / password123' AS admin_account,
    '管理者アカウント: seki / password123' AS seki_account;

