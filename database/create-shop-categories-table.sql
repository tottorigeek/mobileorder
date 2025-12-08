-- 店舗独自カテゴリテーブル作成
-- 各店舗が独自のメニュー分類を設定できるようにする

SET NAMES utf8mb4;

-- 店舗カテゴリテーブル
CREATE TABLE IF NOT EXISTS `shop_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `code` VARCHAR(50) NOT NULL COMMENT 'カテゴリコード（店舗内で一意）',
  `name` VARCHAR(255) NOT NULL COMMENT 'カテゴリ名',
  `display_order` INT(11) DEFAULT 0 COMMENT '表示順',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_code` (`shop_id`, `code`),
  KEY `shop_id` (`shop_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `fk_shop_categories_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗独自カテゴリ';

-- インデックスの追加（パフォーマンス向上）
CREATE INDEX idx_shop_categories_shop_active ON shop_categories(shop_id, is_active);

