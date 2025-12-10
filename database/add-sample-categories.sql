-- サンプルカテゴリデータ（日本語ラベル）
-- 各店舗に基本的なカテゴリを追加

SET NAMES utf8mb4;

-- 店舗IDを変数に保存
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001');
SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002');
SET @shop3_id = (SELECT id FROM shops WHERE code = 'shop003');
SET @shop4_id = (SELECT id FROM shops WHERE code = 'shop004');
SET @shop5_id = (SELECT id FROM shops WHERE code = 'shop005');
SET @shop6_id = (SELECT id FROM shops WHERE code = 'shop006');
SET @shop7_id = (SELECT id FROM shops WHERE code = 'shop007');
SET @shop8_id = (SELECT id FROM shops WHERE code = 'shop008');

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

-- 完了メッセージ
SELECT 
    'サンプルカテゴリデータの挿入が完了しました！' AS message,
    (SELECT COUNT(*) FROM shop_categories WHERE shop_id IN (@shop1_id, @shop2_id, @shop3_id, @shop4_id, @shop5_id, @shop6_id, @shop7_id, @shop8_id)) AS category_count;

