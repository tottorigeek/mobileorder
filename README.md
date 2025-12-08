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

### GitHub Pagesへのデプロイ

GitHub Pagesへのデプロイが可能です。

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

