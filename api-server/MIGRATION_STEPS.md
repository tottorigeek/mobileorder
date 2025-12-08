# データベース移行手順

## エラーが発生した場合の対処法

### エラー: 「列 'shop_id' は 'field list' にはありません」

このエラーは、`menus`テーブルに`shop_id`カラムがまだ追加されていないことを意味します。

## 解決方法

### 方法1: 簡易マイグレーションスクリプトを使用（推奨）

1. **`database/migration-simple.sql`を実行**
   - phpMyAdminで`database/migration-simple.sql`の内容を実行
   - または、コマンドラインから実行

2. **実行後の確認**
   ```sql
   -- menusテーブルの構造を確認
   DESCRIBE menus;
   
   -- shop_idカラムが追加されているか確認
   SELECT shop_id FROM menus LIMIT 1;
   ```

3. **その後、`database/schema-multi-shop.sql`のINSERT文を実行**

### 方法2: 手動でカラムを追加

phpMyAdminのSQLタブで以下を実行：

```sql
-- 1. デフォルト店舗を作成
INSERT INTO `shops` (`code`, `name`, `description`, `max_tables`) 
VALUES ('default', 'デフォルト店舗', '既存データ用', 20)
ON DUPLICATE KEY UPDATE `code` = `code`;

-- 2. デフォルト店舗のIDを取得
SET @default_shop_id = (SELECT id FROM shops WHERE code = 'default' LIMIT 1);

-- 3. menusテーブルにshop_idカラムを追加
ALTER TABLE `menus` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;

-- 4. 既存データにデフォルト店舗IDを設定
UPDATE `menus` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;

-- 5. shop_idをNOT NULLに変更
ALTER TABLE `menus` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';

-- 6. ordersテーブルにも同様に追加
ALTER TABLE `orders` ADD COLUMN `shop_id` INT(11) NULL COMMENT '店舗ID' AFTER `id`;
UPDATE `orders` SET `shop_id` = @default_shop_id WHERE `shop_id` IS NULL;
ALTER TABLE `orders` MODIFY COLUMN `shop_id` INT(11) NOT NULL COMMENT '店舗ID';
```

### 方法3: 完全なマイグレーションスクリプトを使用

`database/migration-add-shop-id.sql`を実行すると、すべての必要な変更が自動的に適用されます。

## 実行順序

1. **既存データのバックアップ**（重要！）
   ```sql
   CREATE TABLE menus_backup AS SELECT * FROM menus;
   CREATE TABLE orders_backup AS SELECT * FROM orders;
   ```

2. **shopsテーブルの作成**（`database/schema-multi-shop.sql`の最初の部分）

3. **マイグレーションスクリプトの実行**
   - `database/migration-simple.sql`または`database/migration-add-shop-id.sql`

4. **サンプルデータの挿入**（`database/schema-multi-shop.sql`のINSERT文）

## 確認方法

移行が成功したか確認：

```sql
-- shopsテーブルが存在するか
SELECT * FROM shops;

-- menusテーブルにshop_idカラムがあるか
DESCRIBE menus;

-- 既存のメニューにshop_idが設定されているか
SELECT id, shop_id, number, name FROM menus LIMIT 5;

-- ordersテーブルにshop_idカラムがあるか
DESCRIBE orders;
```

## トラブルシューティング

### 「Duplicate column name 'shop_id'」エラー

既に`shop_id`カラムが存在している場合：
- そのまま続行して、INSERT文を実行してください

### 「Table 'shops' doesn't exist」エラー

`shops`テーブルが存在しない場合：
- `database/schema-multi-shop.sql`の最初の部分（shopsテーブルの作成）を先に実行してください

### 外部キー制約エラー

外部キー制約でエラーが出る場合：
- まず`shop_id`カラムを追加してデータを設定
- その後、外部キー制約を追加してください

