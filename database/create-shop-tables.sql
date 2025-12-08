-- 店舗テーブル管理用テーブル作成
-- QRコードで店舗とテーブルを一意に特定できるようにする

SET NAMES utf8mb4;

-- 店舗テーブル（座席）管理テーブル
CREATE TABLE IF NOT EXISTS `shop_tables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `table_number` VARCHAR(20) NOT NULL COMMENT 'テーブル番号（例: 1, 2, A1, B2）',
  `name` VARCHAR(255) COMMENT 'テーブル名（オプション）',
  `capacity` INT(11) DEFAULT 4 COMMENT '定員数',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `qr_code_url` VARCHAR(500) COMMENT 'QRコードURL（自動生成）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_table_number` (`shop_id`, `table_number`),
  KEY `shop_id` (`shop_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `fk_shop_tables_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗テーブル（座席）管理';

-- インデックスの追加（パフォーマンス向上）
CREATE INDEX idx_shop_tables_shop_active ON shop_tables(shop_id, is_active);

