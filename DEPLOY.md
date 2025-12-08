# GitHub Pages デプロイガイド

このプロジェクトをGitHub Pagesにデプロイする手順です。

## 前提条件

- GitHubアカウント
- リポジトリがGitHubに作成されていること

## デプロイ手順

### 1. GitHubリポジトリの作成

1. GitHubで新しいリポジトリを作成
2. リポジトリ名を入力（例: `mobile-order-system`）
3. リポジトリを作成

### 2. ローカルリポジトリの初期化とプッシュ

```bash
# Gitリポジトリの初期化（まだの場合）
git init

# ファイルをステージング
git add .

# 初回コミット
git commit -m "Initial commit"

# GitHubリポジトリをリモートとして追加（YOUR_USERNAMEとREPO_NAMEを置き換え）
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git

# ブランチ名をmainに変更（必要に応じて）
git branch -M main

# GitHubにプッシュ
git push -u origin main
```

### 3. GitHub Pagesの設定

1. GitHubリポジトリのページに移動
2. **Settings** → **Pages** に移動
3. **Source** で **GitHub Actions** を選択
4. 設定を保存

### 4. 自動デプロイの確認

- `main`ブランチにプッシュすると、自動的にGitHub Actionsが実行されます
- **Actions**タブでデプロイの進行状況を確認できます
- デプロイが完了すると、`https://YOUR_USERNAME.github.io/REPO_NAME/` でアクセスできます

## カスタムドメインの設定（オプション）

1. リポジトリの **Settings** → **Pages** に移動
2. **Custom domain** にドメイン名を入力
3. DNS設定でCNAMEレコードを追加

## 環境変数の設定

本番環境でAPIのベースURLを変更する場合：

1. リポジトリの **Settings** → **Secrets and variables** → **Actions** に移動
2. **New repository secret** をクリック
3. 以下のシークレットを追加：
   - `NUXT_PUBLIC_API_BASE`: APIのベースURL（例: `https://your-api.com/api`）

## トラブルシューティング

### デプロイが失敗する場合

1. **Actions**タブでエラーログを確認
2. よくある問題：
   - Node.jsのバージョン不一致
   - 依存関係のインストールエラー
   - ビルドエラー

### ページが表示されない場合

1. GitHub Pagesの設定で **GitHub Actions** が選択されているか確認
2. デプロイが完了しているか確認（**Actions**タブ）
3. ブラウザのキャッシュをクリア

### パスが正しく動作しない場合

- Nuxtの`app.baseURL`設定を確認
- リポジトリ名が正しく設定されているか確認

## 手動デプロイ

GitHub Actionsを使わずに手動でデプロイする場合：

```bash
# 静的サイトを生成
npm run generate

# .output/public ディレクトリをGitHub Pagesのブランチにプッシュ
# （gh-pagesブランチを使用する場合）
```

## 注意事項

- 現在の設定ではSPAモード（`ssr: false`）を使用しています
- サーバーサイドAPI（`server/api`）は静的生成時には動作しません
- 本番環境では別途APIサーバーを用意する必要があります

