# モバイルオーダーシステム

番号入力式のモバイルオーダーシステムです。

**GitHubリポジトリ**: [https://github.com/tottorigeek/mobileorder](https://github.com/tottorigeek/mobileorder)

## 技術スタック

- Nuxt 3
- Vue 3
- TypeScript
- Tailwind CSS
- Nuxt UI
- Pinia (状態管理)

## クイックスタート

```bash
# 依存関係のインストール
npm install

# 開発サーバーの起動
npm run dev
```

ブラウザで `http://localhost:3000` にアクセスしてください。

詳細は [QUICK_START.md](./QUICK_START.md) を参照してください。

## セットアップ

```bash
# 依存関係のインストール
npm install

# 開発サーバーの起動
npm run dev

# ビルド
npm run build

# 静的サイト生成
npm run generate

# プレビュー
npm run preview
```

## APIサーバー

エックスサーバーにPHPベースのAPIサーバーを設置します。

詳細は `api-server/README.md` と `api-server/DEPLOY.md` を参照してください。

### API接続設定

NuxtアプリとAPIサーバーを接続する設定は [API_SETUP.md](./API_SETUP.md) を参照してください。

## デプロイ

### GitHub Pagesへのデプロイ

GitHub Pagesへのデプロイが可能です。

詳細は [DEPLOY.md](./DEPLOY.md) を参照してください。

## プロジェクト構造

```
├── assets/          # 静的アセット（CSS、画像など）
├── components/      # Vueコンポーネント
├── composables/     # コンポーザブル関数
├── database/        # データベーススキーマとサンプルデータ
│   ├── schema-multi-shop.sql   # 複数店舗対応スキーマ（推奨）
│   ├── schema-multi-shop-owner.sql  # 複数店舗オーナー対応スキーマ
│   ├── sample-data.sql         # サンプルデータ
│   └── create-shop-tables.sql  # 店舗テーブル作成
├── layouts/         # レイアウトコンポーネント
├── pages/           # ページコンポーネント（自動ルーティング）
│   ├── multi-shop/  # 複数店舗オーナー向け管理画面（新規）
├── stores/          # Piniaストア
├── server/          # サーバーサイドAPI
└── types/           # TypeScript型定義
```

## 機能

### 顧客側
- 番号入力による注文
- メニュー一覧表示
- カート機能
- 注文状況確認

### 店舗側
- 注文管理
- メニュー管理
- 会計機能
- レポート

### 複数店舗オーナー向け（新規）
- 統合ダッシュボード（全店舗の売上・注文状況を一元表示）
- 店舗切り替え機能
- 全店舗の注文一覧・管理
- 全店舗のメニュー管理
- 全店舗のスタッフ管理

## 動作確認

✅ **セットアップ完了！** システムは正常に動作しています。

詳細な動作確認チェックリストは [TEST_CHECKLIST.md](./TEST_CHECKLIST.md) を参照してください。


## ドキュメント

- [クイックスタートガイド](./QUICK_START.md)
- [動作確認チェックリスト](./TEST_CHECKLIST.md)
- [API接続設定](./API_SETUP.md)
- [APIサーバー README](./api-server/README.md)
- [データベース README](./database/README.md)
- [複数店舗オーナー機能](./MULTI_SHOP_OWNER.md) - 複数店舗オーナー向け統合管理画面
- [管理画面アカウント情報](./ACCOUNT_INFO.md) - デフォルトアカウント情報

## 複数店舗オーナー機能

複数の店舗を所有・管理するオーナー向けの統合管理画面を追加しました。

### 主な機能

- **統合ダッシュボード**: 全店舗の売上・注文状況を一元表示
- **店舗切り替え**: ドロップダウンで表示する店舗を選択
- **注文管理**: 全店舗の注文を一覧表示・管理
- **メニュー管理**: 全店舗のメニューを一覧表示
- **スタッフ管理**: 全店舗のスタッフを一覧表示

### セットアップ

1. `database/schema-multi-shop-owner.sql` を実行
2. サンプルアカウントでログイン:
   - ユーザー名: `multiowner`
   - パスワード: `password123`
3. 自動的に `/multi-shop/dashboard` にリダイレクトされます

詳細は [MULTI_SHOP_OWNER.md](./MULTI_SHOP_OWNER.md) を参照してください。

## ライセンス

MIT

