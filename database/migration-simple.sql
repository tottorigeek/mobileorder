-- 簡易版：既存テーブルにshop_idを追加するだけのスクリプト
-- MySQL 5.7以前でも動作するように、IF NOT EXISTSを使わない版

-- 1. デフォルト店舗を作成
INSERT INTO `shops` (`code`, `name`, `description`, `max_tables`) 
VALUES ('default', 'デフォルト店舗', '既存データ用のデフォルト店舗', 20)
ON DUPLICATE KEY UPDATE `code` = `code`;

SET @default_shop_id = (SELECT id FROM shops WHERE code = 'default' LIMIT 1);

-- 2. menusテーブルにshop_idカラムを追加
-- エラーが出る場合は既に追加されているので、そのまま続行
ALTER TABLE `menus` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- 3. 既存のメニューデータにデフォルト店舗IDを設定
UPDATE `menus` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;

-- 4. shop_idをNOT NULLに変更
ALTER TABLE `menus` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';

-- 5. ordersテーブルにshop_idカラムを追加
ALTER TABLE `orders` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- 6. 既存の注文データにデフォルト店舗IDを設定
UPDATE `orders` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;

-- 7. shop_idをNOT NULLに変更
ALTER TABLE `orders` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';

-- 完了
SELECT 'Migration completed!' AS message;

