# データベース README

このフォルダには、データベーススキーマとサンプルデータのSQLファイルが含まれています。

## ファイル一覧

### スキーマファイル

- **`schema.sql`** - 単一店舗対応の基本スキーマ
- **`schema-multi-shop.sql`** - 複数店舗対応スキーマ（推奨）
- **`schema-multi-shop-owner.sql`** - 複数店舗オーナー対応の追加スキーマ（`shop_users`テーブル追加）

### マイグレーションファイル

- **`migration-simple.sql`** - 簡易マイグレーション（既存データベースに`shop_id`を追加）
- **`migration-add-shop-id.sql`** - 完全マイグレーション（店舗・ユーザーテーブル作成と`shop_id`追加）
- **`migration-add-email.sql`** - `users`テーブルに`email`カラムを追加

### サンプルデータ

- **`sample-data.sql`** - 最新のサンプルデータ（**推奨**）
  - ユーザー: 5人
  - 店舗: 8店舗
  - メニュー: 100点（店舗を横断）

## セットアップ手順

### 1. 新規セットアップ（推奨）

```sql
-- 1. 複数店舗対応スキーマを実行
SOURCE schema-multi-shop.sql;

-- 2. 複数店舗オーナー対応スキーマを実行（オプション）
SOURCE schema-multi-shop-owner.sql;

-- 3. サンプルデータを挿入
SOURCE sample-data.sql;
```

### 2. 既存データベースからの移行

```sql
-- 1. 完全マイグレーションを実行
SOURCE migration-add-shop-id.sql;

-- 2. emailカラムを追加（必要に応じて）
SOURCE migration-add-email.sql;

-- 3. 複数店舗オーナー対応（オプション）
SOURCE schema-multi-shop-owner.sql;

-- 4. サンプルデータを挿入
SOURCE sample-data.sql;
```

## サンプルデータの内容

### ユーザー（5人）

1. **owner_multi** - 複数店舗オーナー（shop001, shop002）
2. **owner_sakura** - 和食店オーナー（shop003）
3. **manager_beef** - バーガーショップマネージャー（shop004）
4. **staff_mamma** - パスタハウススタッフ（shop005）
5. **owner_premium** - 複数店舗オーナー（shop006, shop007, shop008）

**全ユーザーのパスワード**: `password123`

### 店舗（8店舗）

1. **shop001**: レストラン イタリアン（15メニュー）
2. **shop002**: カフェ モカ（12メニュー）
3. **shop003**: 和食 さくら（13メニュー）
4. **shop004**: バーガーショップ ビーフ（13メニュー）
5. **shop005**: パスタハウス マンマ（12メニュー）
6. **shop006**: ステーキハウス プレミアム（13メニュー）
7. **shop007**: ラーメン屋 こだわり（11メニュー）
8. **shop008**: スイーツカフェ スイート（11メニュー）

**合計**: 100メニュー

### メニューの内訳

- **フード**: 約70点
- **ドリンク**: 約20点
- **デザート**: 約10点

## 注意事項

- サンプルデータを実行する前に、既存のデータをバックアップしてください
- 本番環境では、必ずパスワードを変更してください
- サンプルデータは開発・テスト環境でのみ使用してください

## 関連ドキュメント

- [管理画面アカウント情報](../ACCOUNT_INFO.md)
- [複数店舗オーナー機能](../MULTI_SHOP_OWNER.md)
- [APIサーバー README](../api-server/README.md)
