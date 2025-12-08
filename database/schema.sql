-- モバイルオーダーシステム データベーススキーマ
-- エックスサーバーのphpMyAdminで実行してください

-- データベースの文字コード設定
SET NAMES utf8mb4;

-- メニューテーブル
CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `number` VARCHAR(10) NOT NULL COMMENT 'メニュー番号（例: 001）',
  `name` VARCHAR(255) NOT NULL COMMENT 'メニュー名',
  `description` TEXT COMMENT '説明',
  `price` INT(11) NOT NULL COMMENT '価格',
  `category` ENUM('drink', 'food', 'dessert', 'other') NOT NULL DEFAULT 'other' COMMENT 'カテゴリ',
  `image_url` VARCHAR(500) COMMENT '画像URL',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '利用可能フラグ',
  `is_recommended` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'おすすめフラグ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `category` (`category`),
  KEY `is_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='メニューマスタ';

-- 注文テーブル
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL COMMENT '注文番号',
  `table_number` VARCHAR(20) NOT NULL COMMENT 'テーブル番号',
  `status` ENUM('pending', 'accepted', 'cooking', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'ステータス',
  `total_amount` INT(11) NOT NULL COMMENT '合計金額',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `table_number` (`table_number`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
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

-- サンプルデータの挿入
INSERT INTO `menus` (`number`, `name`, `description`, `price`, `category`, `is_available`, `is_recommended`) VALUES
('001', 'ハンバーガー', 'ジューシーなハンバーガー', 800, 'food', 1, 1),
('002', 'フライドポテト', 'カリッと揚げたポテト', 400, 'food', 1, 0),
('003', 'コーラ', '冷たいコーラ', 300, 'drink', 1, 0),
('004', 'オレンジジュース', 'フレッシュなオレンジジュース', 350, 'drink', 1, 0),
('005', 'チーズバーガー', 'チーズたっぷりのハンバーガー', 900, 'food', 1, 1),
('006', 'アイスクリーム', 'バニラアイスクリーム', 400, 'dessert', 1, 0);

