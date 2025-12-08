-- エラーログテーブルに環境カラムを追加
-- エックスサーバーのphpMyAdminで実行してください

SET NAMES utf8mb4;

-- 環境カラムを追加
ALTER TABLE `error_logs` 
ADD COLUMN `environment` ENUM('development', 'production', 'staging') DEFAULT 'development' COMMENT '環境（development/production/staging）' 
AFTER `level`;

-- 環境カラムにインデックスを追加（フィルター用）
ALTER TABLE `error_logs` 
ADD INDEX `idx_environment` (`environment`);

-- 既存データの環境を設定（必要に応じて変更）
-- UPDATE error_logs SET environment = 'development' WHERE environment IS NULL;

