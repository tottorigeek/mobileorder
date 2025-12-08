# 複数店舗オーナー管理機能

## 概要

複数の店舗を所有・管理するオーナー向けの統合管理画面を追加しました。店舗追加機能は含まれていませんが、その他の一般的な管理機能を一元化して提供します。

## データベース構造

### 新規テーブル: `shop_users`

ユーザーが複数の店舗に所属できるようにする多対多の関連テーブルです。

```sql
CREATE TABLE `shop_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shop_id` INT(11) NOT NULL COMMENT '店舗ID',
  `user_id` INT(11) NOT NULL COMMENT 'ユーザーID',
  `role` ENUM('owner', 'manager', 'staff') NOT NULL DEFAULT 'staff' COMMENT '店舗での役割',
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '主店舗フラグ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_user` (`shop_id`, `user_id`),
  ...
)
```

### セットアップ

1. **データベーススキーマの実行**

   `database/schema-multi-shop-owner.sql` を実行してください。このSQLファイルは：
   - `shop_users` テーブルを作成
   - 既存のユーザーを `shop_users` テーブルに移行
   - 複数店舗オーナーのサンプルデータを作成

2. **サンプルアカウント**

   複数店舗オーナー用のサンプルアカウントが作成されます：
   - **ユーザー名**: `multiowner`
   - **パスワード**: `password123`
   - **所属店舗**: shop001（レストランA）、shop002（カフェB）
   - **役割**: 両店舗でオーナー

## 機能一覧

### 1. ダッシュボード (`/multi-shop/dashboard`)

- **統計情報**
  - 今日の売上（全店舗合計）
  - 今日の注文数
  - 受付待ち注文数
  - 調理中注文数
  - 完成注文数

- **店舗切り替え**
  - ドロップダウンで表示する店舗を選択
  - 「すべての店舗」を選択すると全店舗のデータを表示

- **店舗別統計**
  - 各店舗の今日の売上
  - 各店舗の注文数
  - 店舗カードをクリックしてその店舗に切り替え

- **最近の注文**
  - 最新10件の注文を表示
  - 店舗名、注文番号、テーブル番号、ステータス、金額を表示

### 2. 注文一覧 (`/multi-shop/orders`)

- **フィルター機能**
  - 店舗で絞り込み
  - ステータスで絞り込み（受付待ち、受付済み、調理中、完成、キャンセル）

- **注文管理**
  - 注文詳細の表示
  - ステータス更新（受付、調理開始、完成、キャンセル）
  - 店舗名の表示

### 3. メニュー管理 (`/multi-shop/menus`)

- **フィルター機能**
  - 店舗で絞り込み
  - カテゴリで絞り込み（フード、ドリンク、デザート、その他）

- **メニュー一覧**
  - 全店舗のメニューを一覧表示
  - 店舗名、メニュー番号、名前、価格、利用可能フラグを表示

### 4. スタッフ管理 (`/multi-shop/staff`)

- **フィルター機能**
  - 店舗で絞り込み

- **スタッフ一覧**
  - 全店舗のスタッフを一覧表示
  - スタッフ名、役割、所属店舗、最終ログイン日時を表示
  - スタッフ編集へのリンク

## API エンドポイント

### GET `/api/my-shops`

ログインユーザーが所属する全店舗を取得します。

**認証**: 必須

**レスポンス例**:
```json
[
  {
    "id": "1",
    "code": "shop001",
    "name": "レストランA",
    "description": "美味しいイタリアン",
    "address": "東京都渋谷区1-1-1",
    "phone": "03-1234-5678",
    "email": "shop001@example.com",
    "maxTables": 20,
    "isActive": true,
    "shopRole": "owner",
    "isPrimary": true,
    "createdAt": "2023-01-01 00:00:00",
    "updatedAt": "2023-01-01 00:00:00"
  },
  {
    "id": "2",
    "code": "shop002",
    "name": "カフェB",
    "description": "コーヒーと軽食",
    "address": "東京都新宿区2-2-2",
    "phone": "03-2345-6789",
    "email": "shop002@example.com",
    "maxTables": 15,
    "isActive": true,
    "shopRole": "owner",
    "isPrimary": false,
    "createdAt": "2023-01-01 00:00:00",
    "updatedAt": "2023-01-01 00:00:00"
  }
]
```

## ログインフロー

1. `/staff/login` でログイン
2. ログイン成功後、所属店舗数を確認
3. **複数店舗の場合**: `/multi-shop/dashboard` にリダイレクト
4. **単一店舗の場合**: `/staff/shop-select` にリダイレクト（既存の動作）

## 既存機能との互換性

- 単一店舗のオーナー・管理者・スタッフは既存の管理画面（`/admin/dashboard`）を使用
- 複数店舗のオーナーのみ新しい管理画面（`/multi-shop/*`）を使用
- 店舗追加機能は含まれていません（会社管理画面で行う想定）

## セキュリティ

- 認証チェック: すべてのページで認証状態を確認
- 権限チェック: 各店舗での役割（owner/manager/staff）を確認
- データ分離: ユーザーが所属する店舗のデータのみアクセス可能

## 今後の拡張予定

- [ ] 店舗別の詳細レポート
- [ ] 店舗間の比較機能
- [ ] 一括操作機能（複数店舗の注文を一括更新など）
- [ ] 店舗別の設定管理
- [ ] 店舗別の売上グラフ

## 関連ファイル

- `database/schema-multi-shop-owner.sql` - データベーススキーマ
- `api-server/api/my-shops.php` - 所属店舗取得API
- `stores/shop.ts` - 店舗ストア（`fetchMyShops` アクション追加）
- `pages/multi-shop/dashboard.vue` - ダッシュボード
- `pages/multi-shop/orders.vue` - 注文一覧
- `pages/multi-shop/menus.vue` - メニュー管理
- `pages/multi-shop/staff.vue` - スタッフ管理
- `pages/staff/login.vue` - ログイン画面（複数店舗判定追加）

