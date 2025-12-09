-- メニュー機能拡張マイグレーション
-- セット商品・提供タイミング対応
-- 実行前にバックアップを取ることを推奨します

SET NAMES utf8mb4;

-- ============================================
-- ステップ1: メニューテーブルの拡張
-- ============================================

-- メニュータイプの追加
ALTER TABLE `menus` 
ADD COLUMN `menu_type` ENUM('single', 'set', 'option_group') NOT NULL DEFAULT 'single' COMMENT 'メニュータイプ' AFTER `category`,
ADD COLUMN `set_price` INT(11) NULL COMMENT 'セット価格（セット商品の場合）' AFTER `price`,
ADD COLUMN `estimated_prep_time` INT(11) NULL COMMENT '調理時間（分）' AFTER `set_price`;

-- ============================================
-- ステップ2: セット商品構成テーブルの作成
-- ============================================

CREATE TABLE IF NOT EXISTS `menu_set_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `set_menu_id` INT(11) NOT NULL COMMENT 'セットメニューID',
  `menu_id` INT(11) NOT NULL COMMENT '含まれるメニューID',
  `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT '数量',
  `is_required` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '必須フラグ（0: オプション選択可）',
  `display_order` INT(11) DEFAULT 0 COMMENT '表示順',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `set_menu_id` (`set_menu_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `fk_set_items_set` FOREIGN KEY (`set_menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_set_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='セット商品構成テーブル';

-- ============================================
-- ステップ3: 注文アイテムテーブルの拡張
-- ============================================

-- 提供タイミングとステータスの追加
ALTER TABLE `order_items` 
ADD COLUMN `serve_time_type` ENUM('immediate', 'after_main', 'after_dessert', 'custom') NOT NULL DEFAULT 'immediate' COMMENT '提供時間タイプ' AFTER `price`,
ADD COLUMN `serve_time_minutes` INT(11) NULL COMMENT 'カスタム提供時間（分後）' AFTER `serve_time_type`,
ADD COLUMN `item_status` ENUM('pending', 'accepted', 'cooking', 'ready', 'served', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'アイテムステータス' AFTER `serve_time_minutes`,
ADD COLUMN `serve_sequence` INT(11) NULL COMMENT '提供順序' AFTER `item_status`,
ADD COLUMN `set_menu_id` INT(11) NULL COMMENT 'セットメニューID（セット商品の場合）' AFTER `serve_sequence`,
ADD COLUMN `original_price` INT(11) NULL COMMENT '元の価格（個別価格の合計、セット商品の場合）' AFTER `set_menu_id`,
ADD COLUMN `discount_amount` INT(11) DEFAULT 0 COMMENT '値引き額（内部管理用）' AFTER `original_price`,
ADD KEY `item_status` (`item_status`),
ADD KEY `serve_time_type` (`serve_time_type`),
ADD KEY `set_menu_id` (`set_menu_id`),
ADD CONSTRAINT `fk_order_items_set_menu` FOREIGN KEY (`set_menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

-- ============================================
-- ステップ4: 注文テーブルのステータス拡張
-- ============================================

-- 一部完成状態を追加
ALTER TABLE `orders` 
MODIFY COLUMN `status` ENUM('pending', 'accepted', 'cooking', 'partially_completed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'ステータス';

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT 'マイグレーションが完了しました！' AS message;

