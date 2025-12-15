# ルートパス一覧

このドキュメントは、飲食店オーダーシステムで管理しているすべてのルートパスを一覧化したものです。

## トップレベル

- `/` - トップページ（店舗選択・QRコード読み取り）

## 顧客向けページ

- `/customer` - 顧客メニュー表示ページ
- `/customer/cart` - カートページ
- `/customer/order/[id]` - 注文詳細ページ（動的ルート）
- `/customer/status/[id]` - 注文ステータス確認ページ（動的ルート）

## スタッフ向けページ

### 認証・店舗選択

- `/staff/login` - スタッフログインページ
- `/staff/shop-select` - 店舗選択ページ

### 店舗管理（/shop配下）

- `/shop/dashboard` - 店舗ダッシュボード
- `/shop/users` - スタッフ管理ページ
- `/shop/users/[id]/edit` - スタッフ情報編集ページ（動的ルート）
- `/shop/users/[id]/password` - スタッフパスワード変更ページ（動的ルート）
- `/shop/users/password` - 自分のパスワード変更ページ
- `/shop/tables` - テーブル設定ページ

### 注文・テーブル管理（/staff配下）

- `/staff/orders` - 注文管理ページ
- `/staff/tables` - テーブル管理ページ（旧パス、/shop/tablesに移行推奨）

## 複数店舗管理（/multi-shop配下）

- `/multi-shop/dashboard` - 複数店舗管理ダッシュボード
- `/multi-shop/orders` - 注文一覧ページ
- `/multi-shop/menus` - メニュー管理ページ
- `/multi-shop/staff` - スタッフ管理ページ

## 会社管理（/unei配下）

### 認証

- `/unei/login` - 会社管理ログインページ

### ダッシュボード・管理

- `/unei/dashboard` - 会社管理ダッシュボード
- `/unei/shops` - 店舗一覧・管理ページ
- `/unei/shops/[id]/edit` - 店舗編集ページ（動的ルート）
- `/unei/shops/[id]/settings` - 店舗詳細設定ページ（動的ルート）
- `/unei/users` - ユーザー管理ページ
- `/unei/error-logs` - エラーログ一覧ページ

## その他

- `/shop-select` - 店舗選択ページ（顧客・スタッフ共通）

---

## パス構造の説明

### 動的ルート

- `[id]` - IDパラメータ（例: `/shop/users/123/edit` の `123`）

### パスグループ

1. **顧客向け** (`/customer/*`)
   - メニュー閲覧、注文、カート管理

2. **スタッフ向け** (`/staff/*`, `/shop/*`)
   - 店舗管理、スタッフ管理、注文管理、テーブル管理

3. **複数店舗管理** (`/multi-shop/*`)
   - 複数店舗を所有するオーナー向けの統合管理

4. **会社管理** (`/unei/*`)
   - システム全体の管理（店舗管理、ユーザー管理、エラーログ）

---

## 注意事項

- `/staff/tables` は旧パスです。新規実装では `/shop/tables` を使用してください。
- 動的ルート `[id]` は実際のID値に置き換えられます（例: `/shop/users/123/edit`）。

