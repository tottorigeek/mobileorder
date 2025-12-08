-- 店舗テーブル（座席）ダミーデータ追加スクリプト
-- 各店舗に複数のテーブル（座席）を追加します
-- sample-data.sqlで作成された店舗データを使用します

SET NAMES utf8mb4;

-- 既存のテーブルデータを削除（重複を避けるため）
DELETE st FROM `shop_tables` st
INNER JOIN `shops` s ON st.shop_id = s.id
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');

-- 店舗IDを変数に保存
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);
SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002' LIMIT 1);
SET @shop3_id = (SELECT id FROM shops WHERE code = 'shop003' LIMIT 1);
SET @shop4_id = (SELECT id FROM shops WHERE code = 'shop004' LIMIT 1);
SET @shop5_id = (SELECT id FROM shops WHERE code = 'shop005' LIMIT 1);
SET @shop6_id = (SELECT id FROM shops WHERE code = 'shop006' LIMIT 1);
SET @shop7_id = (SELECT id FROM shops WHERE code = 'shop007' LIMIT 1);
SET @shop8_id = (SELECT id FROM shops WHERE code = 'shop008' LIMIT 1);

-- QRコードURLのベースURL（環境に応じて変更してください）
SET @base_url = 'http://localhost:3000';

-- ============================================
-- shop001: レストラン イタリアン（15テーブル）
-- ============================================
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

-- ============================================
-- shop002: カフェ モカ（12テーブル）
-- ============================================
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

-- ============================================
-- shop003: 和食 さくら（18テーブル）
-- ============================================
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

-- ============================================
-- shop004: バーガーショップ ビーフ（10テーブル）
-- ============================================
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

-- ============================================
-- shop005: パスタハウス マンマ（12テーブル）
-- ============================================
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

-- ============================================
-- shop006: ステーキハウス プレミアム（10テーブル）
-- ============================================
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

-- ============================================
-- shop007: ラーメン屋 こだわり（8テーブル）
-- ============================================
INSERT INTO `shop_tables` (`shop_id`, `table_number`, `name`, `capacity`, `is_active`, `qr_code_url`) VALUES
(@shop7_id, '1', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=1')),
(@shop7_id, '2', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=2')),
(@shop7_id, '3', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=3')),
(@shop7_id, '4', 'カウンター', 1, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=4')),
(@shop7_id, '5', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=5')),
(@shop7_id, '6', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=6')),
(@shop7_id, '7', 'テーブル席', 4, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=7')),
(@shop7_id, '8', 'テーブル席', 6, 1, CONCAT(@base_url, '/shop-select?shop=shop007&table=8'));

-- ============================================
-- shop008: スイーツカフェ スイート（10テーブル）
-- ============================================
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
-- 完了メッセージ
-- ============================================
SELECT 
    '店舗テーブル（座席）のダミーデータ追加が完了しました！' AS message,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id)) AS total_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop1_id) AS shop001_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop2_id) AS shop002_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop3_id) AS shop003_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop4_id) AS shop004_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop5_id) AS shop005_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop6_id) AS shop006_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop7_id) AS shop007_tables,
    (SELECT COUNT(*) FROM shop_tables WHERE shop_id = @shop8_id) AS shop008_tables;

