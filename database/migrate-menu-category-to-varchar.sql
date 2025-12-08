-- メニューテーブルのcategoryカラムをENUMからVARCHARに変更
-- 店舗独自カテゴリに対応するため

SET NAMES utf8mb4;

-- 既存のデータを保持したままカラムを変更
-- 注意: このマイグレーションは既存のENUM値を文字列として保持します

-- 1. 一時カラムを作成
ALTER TABLE `menus` 
ADD COLUMN `category_temp` VARCHAR(50) NULL COMMENT 'カテゴリ（一時）' AFTER `category`;

-- 2. 既存データをコピー
UPDATE `menus` SET `category_temp` = `category`;

-- 3. 元のカラムを削除
ALTER TABLE `menus` DROP COLUMN `category`;

-- 4. 一時カラムを正式なカラムにリネーム
ALTER TABLE `menus` 
CHANGE COLUMN `category_temp` `category` VARCHAR(50) NOT NULL DEFAULT 'other' COMMENT 'カテゴリ（店舗独自カテゴリコード）';

-- 5. インデックスを再作成
CREATE INDEX `idx_menus_category` ON `menus` (`category`);

