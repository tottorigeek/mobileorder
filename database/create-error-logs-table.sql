-- エラーログテーブル作成
-- エックスサーバーのphpMyAdminで実行してください

-- データベースの文字コード設定
SET NAMES utf8mb4;

-- エラーログテーブル
CREATE TABLE IF NOT EXISTS `error_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `level` ENUM('error', 'warning', 'info', 'debug') NOT NULL DEFAULT 'error' COMMENT 'エラーレベル',
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

