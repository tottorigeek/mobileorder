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

## 動作確認

✅ **セットアップ完了！** システムは正常に動作しています。

詳細な動作確認チェックリストは [TEST_CHECKLIST.md](./TEST_CHECKLIST.md) を参照してください。

セットアップの完了サマリーは [SETUP_SUMMARY.md](./SETUP_SUMMARY.md) を参照してください。

## ドキュメント

- [クイックスタートガイド](./QUICK_START.md)
- [動作確認チェックリスト](./TEST_CHECKLIST.md)
- [セットアップ完了ガイド](./SETUP_COMPLETE.md)
- [API接続設定](./API_SETUP.md)
- [APIサーバー README](./api-server/README.md)

## ライセンス

MIT

