-- 複数店舗管理対応 データベーススキーマ（完全統合版）
-- エックスサーバーのphpMyAdminで実行してください
--
-- このファイルには以下のテーブル定義が含まれています：
-- - 基本テーブル: shops, users, menus, orders, order_items, payments
-- - 追加テーブル: shop_tables, visitors, shop_categories, error_logs, password_reset_tokens, shop_users
-- 
-- 注意: このファイルを実行すると、すべてのテーブルが作成されます。
-- 既存のテーブルがある場合は、CREATE TABLE IF NOT EXISTSによりスキップされます。
-- create-table系のSQLファイル（create-shop-tables.sql等）は、このファイルに統合されています。

-- データベースの文字コード設定
SET NAMES utf8mb4;

-- 店舗テーブル
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

-- ユーザーテーブル（店舗オーナー・スタッフ）
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '所属店舗ID',
  `username` VARCHAR(100) NOT NULL COMMENT 'ユーザー名',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'パスワードハッシュ',
  `name` VARCHAR(255) NOT NULL COMMENT '表示名',
  `email` VARCHAR(255) COMMENT 'メールアドレス',
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

-- メニューテーブル（店舗ごと）
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `number` VARCHAR(10) NOT NULL COMMENT 'メニュー番号（例: 001）',
  `name` VARCHAR(255) NOT NULL COMMENT 'メニュー名',
  `description` TEXT COMMENT '説明',
  `price` INT(11) NOT NULL COMMENT '価格',
  `category` ENUM('drink', 'food', 'dessert', 'other') NOT NULL DEFAULT 'other' COMMENT 'カテゴリ',
  `image_url` VARCHAR(500) COMMENT '画像URL',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '利用可能フラグ',
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'おすすめフラグ',
  `display_order` INT(11) DEFAULT 0 COMMENT '表示順',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_number` (`shop_id`, `number`),
  KEY `shop_id` (`shop_id`),
  KEY `category` (`category`),
  KEY `is_available` (`is_available`),
  CONSTRAINT `fk_menus_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='メニューマスタ';

-- 注文テーブル（店舗ごと）
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `order_number` VARCHAR(50) NOT NULL COMMENT '注文番号',
  `table_number` VARCHAR(20) NOT NULL COMMENT 'テーブル番号',
  `status` ENUM('pending', 'accepted', 'cooking', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'ステータス',
  `total_amount` INT(11) NOT NULL COMMENT '合計金額',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `shop_id` (`shop_id`),
  KEY `table_number` (`table_number`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_orders_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='注文テーブル';

-- 注文アイテムテーブル
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL COMMENT '注文ID',
  `menu_id` INT(11) NOT NULL COMMENT 'メニューID',
  `menu_number` VARCHAR(10) NOT NULL COMMENT 'メニュー番号',
  `menu_name` VARCHAR(255) NOT NULL COMMENT 'メニュー名',
  `quantity` INT(11) NOT NULL COMMENT '数量',
  `price` INT(11) NOT NULL COMMENT '単価',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='注文アイテムテーブル';

-- 会計テーブル（店舗ごと）
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

-- ============================================
-- 追加テーブル（create-table系から統合）
-- ============================================

-- 店舗テーブル（座席）管理テーブル
-- QRコードで店舗とテーブルを一意に特定できるようにする
CREATE TABLE IF NOT EXISTS `shop_tables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `table_number` VARCHAR(20) NOT NULL COMMENT 'テーブル番号（例: 1, 2, A1, B2）',
  `name` VARCHAR(255) COMMENT 'テーブル名（オプション）',
  `capacity` INT(11) DEFAULT 4 COMMENT '定員数',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `visitor_id` INT(11) COMMENT '現在の来店者ID',
  `status` ENUM('available', 'occupied', 'checkout_pending', 'set_pending') NOT NULL DEFAULT 'available' COMMENT 'テーブルステータス',
  `qr_code_url` VARCHAR(500) COMMENT 'QRコードURL（自動生成）',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_table_number` (`shop_id`, `table_number`),
  KEY `shop_id` (`shop_id`),
  KEY `is_active` (`is_active`),
  KEY `visitor_id` (`visitor_id`),
  KEY `idx_shop_tables_shop_active` (`shop_id`, `is_active`),
  KEY `idx_shop_tables_status` (`shop_id`, `status`),
  CONSTRAINT `fk_shop_tables_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗テーブル（座席）管理';

-- 来店情報管理テーブル
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
  KEY `idx_visitors_shop_status` (`shop_id`, `payment_status`),
  KEY `idx_visitors_table_status` (`table_id`, `payment_status`, `is_set_completed`),
  CONSTRAINT `fk_visitors_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_visitors_table` FOREIGN KEY (`table_id`) REFERENCES `shop_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='来店情報管理テーブル';

-- 【コメントアウト】既存のshop_tablesテーブルにvisitor_idとstatusカラムを追加する処理
-- 注意: このSQLファイルのshop_tablesテーブル定義には既にvisitor_idとstatusカラムが含まれています
-- 既存のデータベースで、shop_tablesテーブルにvisitor_idとstatusカラムがない場合のみ、以下のコメントを解除して実行してください
-- 
-- ALTER TABLE `shop_tables` 
-- ADD COLUMN `visitor_id` INT(11) COMMENT '現在の来店者ID' AFTER `is_active`,
-- ADD COLUMN `status` ENUM('available', 'occupied', 'checkout_pending', 'set_pending') NOT NULL DEFAULT 'available' COMMENT 'テーブルステータス' AFTER `visitor_id`,
-- ADD KEY `visitor_id` (`visitor_id`),
-- ADD CONSTRAINT `fk_shop_tables_visitor` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE SET NULL;

-- 店舗独自カテゴリテーブル
-- 各店舗が独自のメニュー分類を設定できるようにする
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
  KEY `idx_shop_categories_shop_active` (`shop_id`, `is_active`),
  CONSTRAINT `fk_shop_categories_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗独自カテゴリ';

-- エラーログテーブル（オプション）
-- エラーログ機能を使用する場合に作成されます
CREATE TABLE IF NOT EXISTS `error_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `level` ENUM('error', 'warning', 'info', 'debug') NOT NULL DEFAULT 'error' COMMENT 'エラーレベル',
  `environment` VARCHAR(50) COMMENT '環境（development, production等）',
  `message` TEXT NOT NULL COMMENT 'エラーメッセージ',
  `file` VARCHAR(500) COMMENT 'ファイルパス',
  `line` INT(11) COMMENT '行番号',
  `trace` JSON COMMENT 'スタックトレース（JSON形式）',
  `user_id` INT(11) COMMENT 'ユーザーID',
  `shop_id` INT(11) COMMENT '店舗ID',
  `request_method` VARCHAR(10) COMMENT 'HTTPメソッド',
  `request_uri` VARCHAR(500) COMMENT 'リクエストURI',
  `ip_address` VARCHAR(45) COMMENT 'IPアドレス',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`),
  KEY `shop_id` (`shop_id`),
  CONSTRAINT `fk_error_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_error_logs_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='エラーログテーブル';

-- パスワードリセットトークンテーブル
-- パスワードリセット用のトークンを管理するテーブル
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT 'ユーザーID',
  `token` VARCHAR(255) NOT NULL COMMENT 'リセットトークン',
  `expires_at` DATETIME NOT NULL COMMENT '有効期限',
  `used_at` DATETIME NULL COMMENT '使用日時',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `fk_password_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='パスワードリセットトークンテーブル';

-- 店舗ユーザー関連テーブル（複数店舗オーナー対応）
-- 1人のユーザーが複数の店舗に所属できるようにする多対多の関係テーブル
CREATE TABLE IF NOT EXISTS `shop_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `user_id` INT(11) NOT NULL COMMENT 'ユーザーID',
  `role` ENUM('owner', 'manager', 'staff') NOT NULL DEFAULT 'staff' COMMENT '店舗での役割',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '主店舗フラグ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_user` (`shop_id`, `user_id`),
  KEY `shop_id` (`shop_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_shop_users_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shop_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='店舗ユーザー関連テーブル';

-- 【コメントアウト】既存のusersテーブルのshop_idをNULL許可に変更（複数店舗対応）
-- 注意: 既存データがある場合は、先にshop_usersテーブルに移行してから実行してください
-- この変更により、usersテーブルのshop_idは主店舗IDとして扱われ、複数店舗への所属はshop_usersテーブルで管理されます
-- 
-- ALTER TABLE `users` MODIFY COLUMN `shop_id` INT(11) NULL COMMENT '主店舗ID（オプション）';

-- 【コメントアウト】既存のユーザーをshop_usersテーブルに移行する処理
-- 注意: 既存のusersテーブルのデータをshop_usersテーブルにコピーする場合に使用します
-- このSQLは既存のusersテーブルのshop_idとroleをshop_usersテーブルに移行します
-- 
-- INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
-- SELECT shop_id, id, role, 1
-- FROM users
-- WHERE shop_id IS NOT NULL;

-- ============================================
-- サンプルデータの挿入
-- ============================================
-- 店舗1
INSERT INTO `shops` (`code`, `name`, `description`, `address`, `phone`, `max_tables`) VALUES
('shop001', 'レストランA', '美味しいイタリアン', '東京都渋谷区1-1-1', '03-1234-5678', 20);

SET @shop1_id = LAST_INSERT_ID();

-- 店舗1のメニュー
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop1_id, '001', 'ハンバーガー', 'ジューシーなハンバーガー', 800, 'food', 1, 1),
(@shop1_id, '002', 'フライドポテト', 'カリッと揚げたポテト', 400, 'food', 1, 0),
(@shop1_id, '003', 'コーラ', '冷たいコーラ', 300, 'drink', 1, 0),
(@shop1_id, '004', 'オレンジジュース', 'フレッシュなオレンジジュース', 350, 'drink', 1, 0),
(@shop1_id, '005', 'チーズバーガー', 'チーズたっぷりのハンバーガー', 900, 'food', 1, 1),
(@shop1_id, '006', 'アイスクリーム', 'バニラアイスクリーム', 400, 'dessert', 1, 0);

-- 店舗1のオーナー（パスワード: password123）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `role`) VALUES
(@shop1_id, 'owner1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'オーナー1', 'owner');

-- 店舗2（サンプル）
INSERT INTO `shops` (`code`, `name`, `description`, `address`, `phone`, `max_tables`) VALUES
('shop002', 'カフェB', 'コーヒーと軽食', '東京都新宿区2-2-2', '03-2345-6789', 15);

SET @shop2_id = LAST_INSERT_ID();

-- 店舗2のメニュー
INSERT INTO `menus` (`shop_id`, `number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
(@shop2_id, '001', 'コーヒー', 'こだわりのコーヒー', 500, 'drink', 1, 1),
(@shop2_id, '002', '紅茶', 'アールグレイ', 450, 'drink', 1, 0),
(@shop2_id, '003', 'サンドイッチ', 'ハムとチーズ', 600, 'food', 1, 1),
(@shop2_id, '004', 'ケーキ', 'チョコレートケーキ', 550, 'dessert', 1, 0);

-- 店舗2のオーナー（パスワード: password123）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `role`) VALUES
(@shop2_id, 'owner2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'オーナー2', 'owner');

