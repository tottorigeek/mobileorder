-- パスワードリセットトークンテーブル作成
-- パスワードリセット用のトークンを管理するテーブル

SET NAMES utf8mb4;

-- password_reset_tokensテーブル
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

