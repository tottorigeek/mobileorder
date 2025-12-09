-- 既存サンプルデータの更新
-- 新しいカラム（menu_type, set_price, estimated_prep_time）に対応
-- migrate-menu-enhancements.sql を実行した後に実行してください

SET NAMES utf8mb4;

-- ============================================
-- 既存メニューの調理時間を設定
-- ============================================

-- shop001: レストラン イタリアン
UPDATE `menus` SET `estimated_prep_time` = 15 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'food' AND `name` LIKE '%ピザ%';
UPDATE `menus` SET `estimated_prep_time` = 12 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'food' AND `name` LIKE '%パスタ%';
UPDATE `menus` SET `estimated_prep_time` = 20 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'food' AND `name` LIKE '%カツレツ%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'food' AND `name` LIKE '%サラダ%';
UPDATE `menus` SET `estimated_prep_time` = 10 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'food' AND `name` LIKE '%スープ%';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop001') AND `category` = 'dessert';

-- shop002: カフェ モカ
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop002') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 8 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop002') AND `category` = 'food' AND `name` LIKE '%サンドイッチ%';
UPDATE `menus` SET `estimated_prep_time` = 15 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop002') AND `category` = 'food' AND `name` LIKE '%パンケーキ%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop002') AND `category` = 'dessert';

-- shop003: 和食 さくら
UPDATE `menus` SET `estimated_prep_time` = 10 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `name` LIKE '%刺身%';
UPDATE `menus` SET `estimated_prep_time` = 15 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `name` LIKE '%天ぷら%';
UPDATE `menus` SET `estimated_prep_time` = 20 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `name` LIKE '%寿司%';
UPDATE `menus` SET `estimated_prep_time` = 25 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `name` LIKE '%うな重%';
UPDATE `menus` SET `estimated_prep_time` = 12 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `name` LIKE '%丼%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop003') AND `category` = 'dessert';

-- shop004: バーガーショップ ビーフ
UPDATE `menus` SET `estimated_prep_time` = 10 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop004') AND `name` LIKE '%バーガー%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop004') AND `name` LIKE '%ポテト%' OR `name` LIKE '%リング%' OR `name` LIKE '%ナゲット%';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop004') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop004') AND `category` = 'dessert';

-- shop005: パスタハウス マンマ
UPDATE `menus` SET `estimated_prep_time` = 12 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop005') AND `name` LIKE '%パスタ%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop005') AND `name` LIKE '%トースト%' OR `name` LIKE '%サラダ%';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop005') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop005') AND `category` = 'dessert';

-- shop006: ステーキハウス プレミアム
UPDATE `menus` SET `estimated_prep_time` = 25 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop006') AND `name` LIKE '%ステーキ%';
UPDATE `menus` SET `estimated_prep_time` = 15 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop006') AND `name` LIKE '%ハンバーグ%';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop006') AND `name` LIKE '%サラダ%' OR `name` LIKE '%スープ%' OR `name` LIKE '%ライス%' OR `name` LIKE '%ポテト%';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop006') AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop006') AND `category` = 'dessert';

-- shop007: ラーメン屋 こだわり
UPDATE `menus` SET `estimated_prep_time` = 8 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop007') AND `name` LIKE '%ラーメン%';
UPDATE `menus` SET `estimated_prep_time` = 10 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop007') AND `name` LIKE '%丼%';
UPDATE `menus` SET `estimated_prep_time` = 12 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop007') AND `name` LIKE '%餃子%';
UPDATE `menus` SET `estimated_prep_time` = 2 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop007') AND `name` LIKE '%ライス%';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop007') AND `category` = 'drink';

-- shop008: スイーツカフェ スイート
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop008') AND `category` = 'dessert';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `shop_id` = (SELECT id FROM shops WHERE code = 'shop008') AND `category` = 'drink';

-- デフォルト値の設定（未設定のメニュー）
UPDATE `menus` SET `estimated_prep_time` = 10 WHERE `estimated_prep_time` IS NULL AND `category` = 'food';
UPDATE `menus` SET `estimated_prep_time` = 3 WHERE `estimated_prep_time` IS NULL AND `category` = 'drink';
UPDATE `menus` SET `estimated_prep_time` = 5 WHERE `estimated_prep_time` IS NULL AND `category` = 'dessert';

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT 
    '既存サンプルデータの更新が完了しました！' AS message,
    COUNT(*) AS updated_menus
FROM `menus`
WHERE `estimated_prep_time` IS NOT NULL;

