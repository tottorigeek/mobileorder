# モバイルオーダーシステム

番号入力式のモバイルオーダーシステムです。

## 技術スタック

- Nuxt 3
- Vue 3
- TypeScript
- Tailwind CSS
- Nuxt UI
- Pinia (状態管理)

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

## デプロイ

### Vercelへのデプロイ（推奨）

このプロジェクトはVercelに最適化されています。

**クイックスタート:**
1. [Vercel](https://vercel.com) にアクセス
2. GitHubアカウントでサインアップ
3. リポジトリをインポート
4. 自動的にデプロイされます

詳細は [VERCEL_DEPLOY.md](./VERCEL_DEPLOY.md) を参照してください。

### GitHub Pagesへのデプロイ

GitHub Pagesへのデプロイも可能です。

詳細は [DEPLOY.md](./DEPLOY.md) を参照してください。

## プロジェクト構造

```
├── assets/          # 静的アセット（CSS、画像など）
├── components/      # Vueコンポーネント
├── composables/     # コンポーザブル関数
├── layouts/         # レイアウトコンポーネント
├── pages/           # ページコンポーネント（自動ルーティング）
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

## ライセンス

MIT

