-- セット商品のサンプルデータ追加
-- migrate-menu-enhancements.sql と update-sample-data-for-menu-enhancements.sql を実行した後に実行してください

SET NAMES utf8mb4;

-- ============================================
-- shop001: レストラン イタリアン - セット商品
-- ============================================

SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001');

-- ピザセット（ピザ + サラダ + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop1_id, '016', 'ピザセット', 'ピザ、サラダ、ドリンクのセット', 2000, 1800, 'food', 'set', 1, 1, 15);

SET @pizza_set_id = LAST_INSERT_ID();

-- ピザセットの構成
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pizza_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop1_id AND number = '001' LIMIT 1; -- マルゲリータピザ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pizza_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop1_id AND number = '005' LIMIT 1; -- シーザーサラダ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pizza_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop1_id AND number = '008' LIMIT 1; -- エスプレッソ（オプション）

-- パスタセット（パスタ + サラダ + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop1_id, '017', 'パスタセット', 'パスタ、サラダ、ドリンクのセット', 1900, 1700, 'food', 'set', 1, 1, 12);

SET @pasta_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop1_id AND number = '002' LIMIT 1; -- カルボナーラ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop1_id AND number = '006' LIMIT 1; -- イタリアンサラダ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop1_id AND number = '009' LIMIT 1; -- カプチーノ（オプション）

-- ============================================
-- shop002: カフェ モカ - セット商品
-- ============================================

SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002');

-- モーニングセット（ドリンク + パン）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop2_id, '013', 'モーニングセット', 'ドリンクとパンのセット', 800, 700, 'food', 'set', 1, 1, 5);

SET @morning_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @morning_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop2_id AND number = '001' LIMIT 1; -- ブレンドコーヒー
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @morning_set_id, id, 1, 0, 2 FROM menus WHERE shop_id = @shop2_id AND number = '008' LIMIT 1; -- クロワッサン（オプション）

-- ランチセット（サンドイッチ + ドリンク + デザート）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop2_id, '014', 'ランチセット', 'サンドイッチ、ドリンク、デザートのセット', 1700, 1500, 'food', 'set', 1, 1, 10);

SET @lunch_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @lunch_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop2_id AND number = '007' LIMIT 1; -- サンドイッチ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @lunch_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop2_id AND number = '002' LIMIT 1; -- カフェラテ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @lunch_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop2_id AND number = '010' LIMIT 1; -- チーズケーキ（オプション）

-- ============================================
-- shop003: 和食 さくら - セット商品
-- ============================================

SET @shop3_id = (SELECT id FROM shops WHERE code = 'shop003');

-- 定食セット（メイン + ご飯 + 味噌汁）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop3_id, '014', '定食セット', 'メイン、ご飯、味噌汁のセット', 1400, 1300, 'food', 'set', 1, 1, 15);

SET @teishoku_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @teishoku_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop3_id AND number = '002' LIMIT 1; -- 天ぷら定食
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @teishoku_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop3_id AND number = '008' LIMIT 1; -- ご飯
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @teishoku_set_id, id, 1, 1, 3 FROM menus WHERE shop_id = @shop3_id AND number = '007' LIMIT 1; -- 味噌汁

-- ============================================
-- shop004: バーガーショップ ビーフ - セット商品
-- ============================================

SET @shop4_id = (SELECT id FROM shops WHERE code = 'shop004');

-- バーガーセット（バーガー + ポテト + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop4_id, '014', 'バーガーセット', 'バーガー、ポテト、ドリンクのセット', 1500, 1300, 'food', 'set', 1, 1, 10);

SET @burger_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @burger_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop4_id AND number = '001' LIMIT 1; -- クラシックバーガー
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @burger_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop4_id AND number = '006' LIMIT 1; -- フライドポテト
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @burger_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop4_id AND number = '009' LIMIT 1; -- コーラ（オプション）

-- チーズバーガーセット（チーズバーガー + ポテト + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop4_id, '015', 'チーズバーガーセット', 'チーズバーガー、ポテト、ドリンクのセット', 1600, 1400, 'food', 'set', 1, 1, 10);

SET @cheese_burger_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @cheese_burger_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop4_id AND number = '002' LIMIT 1; -- チーズバーガー
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @cheese_burger_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop4_id AND number = '006' LIMIT 1; -- フライドポテト
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @cheese_burger_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop4_id AND number = '009' LIMIT 1; -- コーラ（オプション）

-- ============================================
-- shop005: パスタハウス マンマ - セット商品
-- ============================================

SET @shop5_id = (SELECT id FROM shops WHERE code = 'shop005');

-- パスタセット（パスタ + サラダ + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop5_id, '013', 'パスタセット', 'パスタ、サラダ、ドリンクのセット', 2000, 1800, 'food', 'set', 1, 1, 12);

SET @pasta_set2_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set2_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop5_id AND number = '001' LIMIT 1; -- ボロネーゼ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set2_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop5_id AND number = '008' LIMIT 1; -- シーザーサラダ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @pasta_set2_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop5_id AND number = '009' LIMIT 1; -- ワイン（オプション）

-- ============================================
-- shop006: ステーキハウス プレミアム - セット商品
-- ============================================

SET @shop6_id = (SELECT id FROM shops WHERE code = 'shop006');

-- ステーキセット（ステーキ + サラダ + ライス + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop6_id, '014', 'ステーキセット', 'ステーキ、サラダ、ライス、ドリンクのセット', 5000, 4500, 'food', 'set', 1, 1, 25);

SET @steak_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @steak_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop6_id AND number = '001' LIMIT 1; -- サーロインステーキ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @steak_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop6_id AND number = '005' LIMIT 1; -- シーザーサラダ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @steak_set_id, id, 1, 1, 3 FROM menus WHERE shop_id = @shop6_id AND number = '007' LIMIT 1; -- ガーリックライス
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @steak_set_id, id, 1, 0, 4 FROM menus WHERE shop_id = @shop6_id AND number = '011' LIMIT 1; -- コーラ（オプション）

-- ============================================
-- shop007: ラーメン屋 こだわり - セット商品
-- ============================================

SET @shop7_id = (SELECT id FROM shops WHERE code = 'shop007');

-- ラーメンセット（ラーメン + 餃子 + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop7_id, '012', 'ラーメンセット', 'ラーメン、餃子、ドリンクのセット', 1750, 1600, 'food', 'set', 1, 1, 10);

SET @ramen_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @ramen_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop7_id AND number = '001' LIMIT 1; -- 醤油ラーメン
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @ramen_set_id, id, 1, 1, 2 FROM menus WHERE shop_id = @shop7_id AND number = '008' LIMIT 1; -- 餃子
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @ramen_set_id, id, 1, 0, 3 FROM menus WHERE shop_id = @shop7_id AND number = '010' LIMIT 1; -- ビール（オプション）

-- ============================================
-- shop008: スイーツカフェ スイート - セット商品
-- ============================================

SET @shop8_id = (SELECT id FROM shops WHERE code = 'shop008');

-- スイーツセット（ケーキ + ドリンク）
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `set_price`, `category`, `menu_type`, `is_available`, `is_recommended`, `estimated_prep_time`) 
VALUES (@shop8_id, '012', 'スイーツセット', 'ケーキとドリンクのセット', 1200, 1000, 'dessert', 'set', 1, 1, 5);

SET @sweets_set_id = LAST_INSERT_ID();

INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @sweets_set_id, id, 1, 1, 1 FROM menus WHERE shop_id = @shop8_id AND number = '001' LIMIT 1; -- ショートケーキ
INSERT INTO `menu_set_items` (`set_menu_id`, `menu_id`, `quantity`, `is_required`, `display_order`) 
SELECT @sweets_set_id, id, 1, 0, 2 FROM menus WHERE shop_id = @shop8_id AND number = '008' LIMIT 1; -- ブレンドコーヒー（オプション）

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT 
    'セット商品のサンプルデータ追加が完了しました！' AS message,
    COUNT(*) AS set_menu_count
FROM `menus`
WHERE `menu_type` = 'set';

SELECT 
    s.name AS shop_name,
    m.number,
    m.name AS set_menu_name,
    m.set_price,
    COUNT(msi.id) AS item_count
FROM `menus` m
INNER JOIN `shops` s ON m.shop_id = s.id
LEFT JOIN `menu_set_items` msi ON m.id = msi.set_menu_id
WHERE m.menu_type = 'set'
GROUP BY m.id, s.name, m.number, m.name, m.set_price
ORDER BY s.name, m.number;

