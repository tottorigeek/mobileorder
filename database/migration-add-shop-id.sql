-- 既存の単一店舗システムから複数店舗システムへの移行スクリプト
-- 既存データを保持したまま、shop_idカラムを追加します

-- 1. shopsテーブルが存在しない場合は作成
CREATE TABLE IF NOT EXISTS `shops` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL COMMENT '店舗コード（URL用）',
  `name` VARCHAR(255) NOT NULL COMMENT '店舗名',
  `description` TEXT COMMENT '店舗説明',
  `address` VARCHAR(500) COMMENT '住所',
  `phone` VARCHAR(20) COMMENT '電話番号',
  `email` VARCHAR(255) COMMENT 'メールアドレス',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `max_tables` INT(11) DEFAULT 20 COMMENT '最大テーブル数',
  `settings` JSON COMMENT '店舗設定（JSON形式）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗マスタ';

-- 2. デフォルト店舗を作成（既存データ用）
INSERT IGNORE INTO `shops` (`code`, `name`, `description`, `max_tables`) 
VALUES ('default', 'デフォルト店舗', '既存データ用のデフォルト店舗', 20);

SET @default_shop_id = (SELECT id FROM shops WHERE code = 'default' LIMIT 1);

-- 3. menusテーブルにshop_idカラムを追加（存在しない場合）
ALTER TABLE `menus` 
ADD COLUMN IF NOT EXISTS `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- MySQL 5.7以前の場合は、IF NOT EXISTSが使えないので以下のように実行
-- ALTER TABLE `menus` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- 4. 既存のメニューデータにデフォルト店舗IDを設定
UPDATE `menus` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;

-- 5. shop_idをNOT NULLに変更（外部キー制約を追加する前に）
ALTER TABLE `menus` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';

-- 6. 外部キー制約を追加（既存の制約を削除してから追加）
-- 既存の外部キー制約を確認して削除（エラーが発生する場合はスキップ）
-- ALTER TABLE `menus` DROP FOREIGN KEY IF EXISTS `fk_menus_shop`;

-- 外部キー制約を追加
ALTER TABLE `menus` 
ADD CONSTRAINT `fk_menus_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

-- 7. インデックスを追加
ALTER TABLE `menus` ADD INDEX `shop_id` (`shop_id`);

-- 8. ユニーク制約を変更（shop_idとnumberの組み合わせでユニークに）
-- 既存のnumberのユニーク制約を削除
ALTER TABLE `menus` DROP INDEX IF EXISTS `number`;

-- shop_idとnumberの組み合わせでユニーク制約を追加
ALTER TABLE `menus` ADD UNIQUE KEY `shop_number` (`shop_id`, `number`);

-- 9. ordersテーブルにshop_idカラムを追加
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- MySQL 5.7以前の場合
-- ALTER TABLE `orders` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- 10. 既存の注文データにデフォルト店舗IDを設定
UPDATE `orders` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;

-- 11. shop_idをNOT NULLに変更
ALTER TABLE `orders` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';

-- 12. 外部キー制約を追加
ALTER TABLE `orders` 
ADD CONSTRAINT `fk_orders_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

-- 13. インデックスを追加
ALTER TABLE `orders` ADD INDEX `shop_id` (`shop_id`);

-- 14. usersテーブルが存在しない場合は作成
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '所属店舗ID',
  `username` VARCHAR(100) NOT NULL COMMENT 'ユーザー名',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'パスワードハッシュ',
  `name` VARCHAR(255) NOT NULL COMMENT '表示名',
  `role` ENUM('owner', 'manager', 'staff') NOT NULL DEFAULT 'staff' COMMENT '役割',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `last_login_at` DATETIME COMMENT '最終ログイン日時',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `shop_id` (`shop_id`),
  KEY `role` (`role`),
  CONSTRAINT `fk_users_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーマスタ';

-- 15. デフォルト店舗のオーナーを作成（パスワード: password123）
-- 既に存在する場合はスキップ
INSERT IGNORE INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `role`) 
VALUES (@default_shop_id, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '管理者', 'owner');

-- 16. paymentsテーブルが存在しない場合は作成
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `order_id` INT(11) NOT NULL COMMENT '注文ID',
  `amount` INT(11) NOT NULL COMMENT '金額',
  `method` ENUM('cash', 'credit', 'electronic') NOT NULL DEFAULT 'cash' COMMENT '支払い方法',
  `paid_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `fk_payments_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会計テーブル';

-- 完了メッセージ
SELECT 'Migration completed successfully!' AS message;

