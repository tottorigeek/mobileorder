-- visitorテーブル作成
-- 来店情報を管理するテーブル

SET NAMES utf8mb4;

-- visitorテーブル
CREATE TABLE IF NOT EXISTS `visitors` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `table_id` INT(11) COMMENT 'テーブルID（着座時に設定）',
  `table_number` VARCHAR(20) NOT NULL COMMENT 'テーブル番号',
  `number_of_guests` INT(11) NOT NULL COMMENT '来店人数',
  `arrival_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '来店時間',
  `checkout_time` DATETIME COMMENT '会計時刻',
  `total_amount` INT(11) DEFAULT 0 COMMENT '合計額',
  `payment_method` ENUM('cash', 'credit', 'paypay') COMMENT '清算方法',
  `payment_status` ENUM('pending', 'completed') NOT NULL DEFAULT 'pending' COMMENT '支払いステータス',
  `is_set_completed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'テーブルセット完了フラグ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `table_id` (`table_id`),
  KEY `table_number` (`table_number`),
  KEY `payment_status` (`payment_status`),
  KEY `is_set_completed` (`is_set_completed`),
  CONSTRAINT `fk_visitors_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_visitors_table` FOREIGN KEY (`table_id`) REFERENCES `shop_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='来店情報管理テーブル';

-- shop_tablesテーブルにvisitor_idとstatusカラムを追加
ALTER TABLE `shop_tables` 
ADD COLUMN `visitor_id` INT(11) COMMENT '現在の来店者ID' AFTER `is_active`,
ADD COLUMN `status` ENUM('available', 'occupied', 'checkout_pending', 'set_pending') NOT NULL DEFAULT 'available' COMMENT 'テーブルステータス' AFTER `visitor_id`,
ADD KEY `visitor_id` (`visitor_id`),
ADD CONSTRAINT `fk_shop_tables_visitor` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE SET NULL;

-- インデックスの追加（パフォーマンス向上）
CREATE INDEX idx_visitors_shop_status ON visitors(shop_id, payment_status);
CREATE INDEX idx_visitors_table_status ON visitors(table_id, payment_status, is_set_completed);
CREATE INDEX idx_shop_tables_status ON shop_tables(shop_id, status);

