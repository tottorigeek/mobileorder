-- usersテーブルにemailカラムを追加するマイグレーション

-- emailカラムを追加（既に存在する場合はエラーになるが、続行可能）
ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) COMMENT 'メールアドレス' AFTER `name`;

-- 完了
SELECT 'Email column added successfully!' AS message;

