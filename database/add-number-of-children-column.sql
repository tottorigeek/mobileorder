-- visitorsテーブルにnumber_of_childrenカラムを追加
-- 子どもの人数を保存するためのカラム

SET NAMES utf8mb4;

-- number_of_childrenカラムを追加（既に存在する場合はエラーを無視）
ALTER TABLE `visitors` 
ADD COLUMN `number_of_children` INT(11) NOT NULL DEFAULT 0 COMMENT '子どもの人数' AFTER `number_of_guests`;

