-- 複数店舗オーナー対応の追加スキーマ
-- 既存のschema-multi-shop.sqlを実行した後に実行してください

-- 店舗ユーザー関連テーブル（多対多の関係）
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

-- 既存のusersテーブルのshop_idをNULL許可に変更（複数店舗対応）
-- 注意: 既存データがある場合は、先にshop_usersテーブルに移行してから実行してください
-- ALTER TABLE `users` MODIFY COLUMN `shop_id` INT(11) NULL COMMENT '主店舗ID（オプション）';

-- サンプルデータ: 複数店舗を持つオーナー
-- 店舗1と店舗2の両方を管理するオーナーを作成

-- まず、複数店舗オーナー用のユーザーを作成（shop_idはNULLまたは最初の店舗ID）
SET @multi_owner_shop_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);

INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `role`, `email`) 
VALUES (
  @multi_owner_shop_id,
  'multiowner', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  '複数店舗オーナー', 
  'owner',
  'multiowner@example.com'
)
ON DUPLICATE KEY UPDATE `name` = `name`;

SET @multi_owner_id = (SELECT id FROM users WHERE username = 'multiowner' LIMIT 1);

-- 店舗1に所属（オーナー）
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);
INSERT INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) 
VALUES (@shop1_id, @multi_owner_id, 'owner', 1)
ON DUPLICATE KEY UPDATE `role` = `role`;

-- 店舗2に所属（オーナー）
SET @shop2_id = (SELECT id FROM shops WHERE code = 'shop002' LIMIT 1);
INSERT INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`) 
VALUES (@shop2_id, @multi_owner_id, 'owner', 0)
ON DUPLICATE KEY UPDATE `role` = `role`;

-- 既存のユーザーをshop_usersテーブルに移行（既存データがある場合）
-- このSQLは既存のusersテーブルのデータをshop_usersテーブルにコピーします
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
SELECT shop_id, id, role, 1
FROM users
WHERE shop_id IS NOT NULL;

-- 完了メッセージ
SELECT 'Multi-shop owner support added successfully!' AS message;

