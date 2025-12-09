# メニュー機能拡張 - セット商品・提供タイミング対応

## 概要

このディレクトリには、メニュー機能を拡張するためのSQLファイルが含まれています。
以下の機能が追加されます：

1. **セット商品機能**: 複数のメニューを組み合わせたセット商品の作成・管理
2. **提供タイミング指定**: 注文アイテムごとに提供タイミングを指定可能
3. **アイテム単位のステータス管理**: 注文内の各アイテムを個別にステータス管理

## 実行順序

**重要**: 以下の順序で実行してください。

### 1. マイグレーション実行
```sql
-- データベース構造の変更
SOURCE database/migrate-menu-enhancements.sql;
```

このファイルで以下が実行されます：
- `menus`テーブルに`menu_type`, `set_price`, `estimated_prep_time`カラムを追加
- `menu_set_items`テーブルを作成（セット商品の構成管理）
- `order_items`テーブルに提供タイミング・ステータス関連カラムを追加
- `orders`テーブルのステータスに`partially_completed`を追加

### 2. 既存サンプルデータの更新
```sql
-- 既存メニューの調理時間を設定
SOURCE database/update-sample-data-for-menu-enhancements.sql;
```

このファイルで以下が実行されます：
- 既存のメニューに`estimated_prep_time`（調理時間）を設定
- カテゴリ別にデフォルトの調理時間を設定

### 3. セット商品のサンプルデータ追加
```sql
-- セット商品のサンプルデータを追加
SOURCE database/add-set-menu-sample-data.sql;
```

このファイルで以下が実行されます：
- 各店舗にセット商品のサンプルデータを追加
- セット商品の構成（`menu_set_items`）を設定

## ファイル説明

### migrate-menu-enhancements.sql
データベース構造の変更を行うマイグレーションファイルです。
- テーブル構造の変更
- 新しいテーブルの作成
- 外部キー制約の追加

### update-sample-data-for-menu-enhancements.sql
既存のサンプルデータを新しい構造に対応させるファイルです。
- 既存メニューの調理時間設定
- カテゴリ別のデフォルト値設定

### add-set-menu-sample-data.sql
セット商品のサンプルデータを追加するファイルです。
- 各店舗にセット商品を追加
- セット商品の構成を設定
- 値引き価格の設定

## 追加されるセット商品の例

### shop001: レストラン イタリアン
- **ピザセット**: ピザ + サラダ + ドリンク（¥2,000 → ¥1,800）
- **パスタセット**: パスタ + サラダ + ドリンク（¥1,900 → ¥1,700）

### shop002: カフェ モカ
- **モーニングセット**: ドリンク + パン（¥800 → ¥700）
- **ランチセット**: サンドイッチ + ドリンク + デザート（¥1,700 → ¥1,500）

### shop004: バーガーショップ ビーフ
- **バーガーセット**: バーガー + ポテト + ドリンク（¥1,500 → ¥1,300）
- **チーズバーガーセット**: チーズバーガー + ポテト + ドリンク（¥1,600 → ¥1,400）

その他、各店舗に適したセット商品を追加しています。

## 注意事項

1. **バックアップ**: 実行前に必ずデータベースのバックアップを取ってください
2. **実行順序**: 必ず上記の順序で実行してください
3. **既存データ**: 既存の注文データには影響しませんが、新しいカラムはNULLまたはデフォルト値が設定されます
4. **外部キー制約**: 外部キー制約が有効な場合、関連するテーブルが存在することを確認してください

## ロールバック方法

マイグレーションをロールバックする場合は、以下のSQLを実行してください：

```sql
-- 注文アイテムテーブルの変更をロールバック
ALTER TABLE `order_items` 
DROP COLUMN `discount_amount`,
DROP COLUMN `original_price`,
DROP COLUMN `set_menu_id`,
DROP COLUMN `serve_sequence`,
DROP COLUMN `item_status`,
DROP COLUMN `serve_time_minutes`,
DROP COLUMN `serve_time_type`;

-- 注文テーブルのステータスを元に戻す
ALTER TABLE `orders` 
MODIFY COLUMN `status` ENUM('pending', 'accepted', 'cooking', 'completed', 'cancelled') NOT NULL DEFAULT 'pending';

-- セット商品構成テーブルを削除
DROP TABLE IF EXISTS `menu_set_items`;

-- メニューテーブルの変更をロールバック
ALTER TABLE `menus` 
DROP COLUMN `estimated_prep_time`,
DROP COLUMN `set_price`,
DROP COLUMN `menu_type`;
```

## 確認方法

マイグレーションが正常に完了したか確認するには：

```sql
-- メニューテーブルの構造確認
DESCRIBE `menus`;

-- セット商品の確認
SELECT * FROM `menus` WHERE `menu_type` = 'set';

-- セット商品の構成確認
SELECT 
    m.name AS set_menu_name,
    m.set_price,
    mi.name AS item_name,
    msi.quantity,
    msi.is_required
FROM `menu_set_items` msi
INNER JOIN `menus` m ON msi.set_menu_id = m.id
INNER JOIN `menus` mi ON msi.menu_id = mi.id
ORDER BY m.name, msi.display_order;
```

## トラブルシューティング

### エラー: Column 'menu_type' already exists
→ 既にマイグレーションが実行されています。次のステップに進んでください。

### エラー: Table 'menu_set_items' already exists
→ 既にテーブルが作成されています。次のステップに進んでください。

### エラー: Foreign key constraint fails
→ 既存のデータに問題がある可能性があります。バックアップから復元して再実行してください。

