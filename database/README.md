# データベース README

このフォルダには、データベーススキーマとサンプルデータのSQLファイルが含まれています。

## ファイル一覧

### スキーマファイル

- **`schema-multi-shop.sql`** - 複数店舗対応スキーマ（推奨）
- **`schema-multi-shop-owner.sql`** - 複数店舗オーナー対応の追加スキーマ（`shop_users`テーブル追加）

### テーブル作成ファイル

- **`create-error-logs-table.sql`** - エラーログテーブル作成
- **`create-shop-categories-table.sql`** - 店舗カテゴリテーブル作成
- **`create-shop-tables.sql`** - 店舗テーブル（座席）テーブル作成
- **`create-visitor-table.sql`** - 来店者テーブル作成

### サンプルデータ

- **`sample-data.sql`** - 最新のサンプルデータ（**推奨**）
  - ユーザー: 7人（admin, owner_multi, owner_sakura, manager_beef, staff_mamma, owner_premium, seki）
  - 店舗: 8店舗
  - メニュー: 100点
  - カテゴリ: 日本語ラベル付き

### 追加データ

- **`add-dummy-staff.sql`** - ダミースタッフ追加スクリプト
  - 各店舗に2-3人のスタッフを追加
  - 合計19人のスタッフを追加
  - パスワード: `password123`

- **`add-dummy-tables.sql`** - 店舗テーブル（座席）データ追加
  - 各店舗に8-18席のテーブルを追加
  - QRコードURLを含む

- **`add-shop-business-hours.sql`** - 営業時間・定休日データ追加
  - 各店舗の営業時間と定休日を設定

- **`add-error-logs-sample.sql`** - エラーログサンプルデータ追加
  - テスト用のエラーログサンプルデータ

## セットアップ手順

### 1. 新規セットアップ（推奨）

```sql
-- 1. 複数店舗対応スキーマを実行
SOURCE schema-multi-shop.sql;

-- 2. 複数店舗オーナー対応スキーマを実行（オプション）
SOURCE schema-multi-shop-owner.sql;

-- 3. 追加テーブルを作成（必要に応じて）
SOURCE create-error-logs-table.sql;
SOURCE create-shop-categories-table.sql;
SOURCE create-shop-tables.sql;
SOURCE create-visitor-table.sql;

-- 4. サンプルデータを挿入
SOURCE sample-data.sql;

-- 5. 追加データを挿入（オプション）
SOURCE add-dummy-staff.sql;
SOURCE add-dummy-tables.sql;
SOURCE add-shop-business-hours.sql;
SOURCE add-error-logs-sample.sql;
```

### 2. 既存データベースからの移行

既存のデータベースがある場合は、`schema-multi-shop.sql`を実行してから、必要に応じて各テーブル作成ファイルと`sample-data.sql`を実行してください。

## サンプルデータの内容

### ユーザー（7人）

1. **admin** - システム管理者（全店舗にアクセス可能）
2. **seki** - 管理者（全店舗にアクセス可能）
3. **owner_multi** - 複数店舗オーナー（shop001, shop002）
4. **owner_sakura** - 和食店オーナー（shop003）
5. **manager_beef** - バーガーショップマネージャー（shop004）
6. **staff_mamma** - パスタハウススタッフ（shop005）
7. **owner_premium** - 複数店舗オーナー（shop006, shop007, shop008）

**全ユーザーのパスワード**: `password123`

### ダミースタッフ（19人）

`add-dummy-staff.sql`を実行すると、各店舗に以下のスタッフが追加されます：

- **shop001**: 3人（佐々木 健太、高橋 美咲、中村 翔太）
- **shop002**: 2人（渡辺 さくら、伊藤 大輔）
- **shop003**: 3人（山本 優香、松本 健一、井上 麻衣）
- **shop004**: 2人（木村 拓也、林 由美）
- **shop005**: 2人（斎藤 真一、加藤 愛美）
- **shop006**: 3人（吉田 雄一、後藤 美穂、近藤 翔）
- **shop007**: 2人（長谷川 誠、橋本 彩）
- **shop008**: 2人（石川 美咲、前田 健太）

**全スタッフのパスワード**: `password123`

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

### カテゴリ

各店舗には日本語ラベルのカテゴリが設定されています：
- **shop001**: 食べ物、飲み物、デザート
- **shop002**: ドリンク、軽食、スイーツ
- **shop003**: 和食、飲み物、和菓子
- **shop004**: バーガー、ドリンク、デザート
- **shop005**: パスタ、ドリンク、デザート
- **shop006**: ステーキ、ドリンク、デザート
- **shop007**: ラーメン、ドリンク
- **shop008**: スイーツ、ドリンク

## 注意事項

- サンプルデータを実行する前に、既存のデータをバックアップしてください
- 本番環境では、必ずパスワードを変更してください
- サンプルデータは開発・テスト環境でのみ使用してください

## 関連ドキュメント

- [管理画面アカウント情報](../ACCOUNT_INFO.md)
- [複数店舗オーナー機能](../MULTI_SHOP_OWNER.md)
- [APIサーバー README](../api-server/README.md)
