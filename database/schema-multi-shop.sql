-- 複数店舗管理対応 データベーススキーマ
-- エックスサーバーのphpMyAdminで実行してください

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

-- サンプルデータの挿入
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

