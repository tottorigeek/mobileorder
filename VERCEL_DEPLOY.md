# Vercel デプロイガイド

このプロジェクトをVercelにデプロイする手順です。

## Vercelとは

Vercelは、Nuxtアプリケーションに最適なデプロイプラットフォームです。
- 自動的なSSR（サーバーサイドレンダリング）対応
- エッジ関数のサポート
- 自動的なCI/CD
- 無料プランで十分な機能

## デプロイ手順

### 方法1: Vercelダッシュボードからデプロイ（推奨）

1. **Vercelアカウントの作成**
   - [Vercel](https://vercel.com) にアクセス
   - GitHubアカウントでサインアップ（推奨）

2. **プロジェクトのインポート**
   - Vercelダッシュボードで **Add New Project** をクリック
   - GitHubリポジトリを選択
   - リポジトリをインポート

3. **プロジェクト設定**
   - **Framework Preset**: Nuxt.js（自動検出される）
   - **Root Directory**: `./`（そのまま）
   - **Build Command**: `npm run build`（自動設定）
   - **Output Directory**: `.output`（自動設定）
   - **Install Command**: `npm install`（自動設定）

4. **環境変数の設定（オプション）**
   - **Environment Variables** セクションで以下を設定：
     - `NUXT_PUBLIC_API_BASE`: APIのベースURL（例: `https://your-api.com/api`）
     - その他の必要な環境変数

5. **デプロイ**
   - **Deploy** ボタンをクリック
   - 数分でデプロイが完了します

### 方法2: Vercel CLIからデプロイ

1. **Vercel CLIのインストール**
   ```bash
   npm install -g vercel
   ```

2. **ログイン**
   ```bash
   vercel login
   ```

3. **デプロイ**
   ```bash
   vercel
   ```
   
   初回デプロイ時は設定を聞かれます：
   - Set up and deploy? → **Y**
   - Which scope? → アカウントを選択
   - Link to existing project? → **N**（新規プロジェクトの場合）
   - Project name → プロジェクト名を入力
   - Directory → `./`（そのまま）
   - Override settings? → **N**

4. **本番環境へのデプロイ**
   ```bash
   vercel --prod
   ```

### 方法3: GitHubと連携して自動デプロイ

1. **Vercelでプロジェクトを作成**（方法1または方法2）

2. **GitHub連携の確認**
   - Vercelダッシュボードの **Settings** → **Git** で連携を確認
   - 自動的にGitHub Actionsが設定されます

3. **自動デプロイの動作**
   - `main`ブランチにプッシュ → 本番環境にデプロイ
   - その他のブランチにプッシュ → プレビュー環境にデプロイ
   - プルリクエスト作成 → プレビュー環境にデプロイ

## 環境変数の設定

### Vercelダッシュボードから設定

1. プロジェクトの **Settings** → **Environment Variables** に移動
2. 環境変数を追加：
   - **Name**: `NUXT_PUBLIC_API_BASE`
   - **Value**: `https://your-api.com/api`
   - **Environment**: Production, Preview, Development を選択
3. **Save** をクリック

### CLIから設定

```bash
vercel env add NUXT_PUBLIC_API_BASE
# 値を入力
# 環境を選択（Production, Preview, Development）
```

## カスタムドメインの設定

1. Vercelダッシュボードの **Settings** → **Domains** に移動
2. **Add Domain** をクリック
3. ドメイン名を入力
4. DNS設定でCNAMEレコードを追加（Vercelが指示を表示）

## サーバーサイドAPIの動作

Vercelでは、`server/api` ディレクトリ内のAPIルートが自動的にサーバーレス関数として動作します。

例：
- `/api/menus` → `server/api/menus/index.get.ts`
- `/api/orders` → `server/api/orders/index.post.ts`

## トラブルシューティング

### ビルドエラーが発生する場合

1. **ビルドログを確認**
   - Vercelダッシュボードの **Deployments** でログを確認

2. **よくある問題**
   - Node.jsのバージョン不一致 → `package.json`に`engines`を追加
   - 依存関係のエラー → `package-lock.json`を確認
   - 環境変数の未設定 → 環境変数を設定

### 環境変数が反映されない場合

1. 環境変数の設定を確認
2. デプロイを再実行（環境変数変更後は再デプロイが必要）
3. 変数名が`NUXT_PUBLIC_`で始まっているか確認（クライアント側で使用する場合）

### APIルートが動作しない場合

1. `server/api` ディレクトリの構造を確認
2. ファイル名が正しいか確認（例: `index.get.ts`, `index.post.ts`）
3. Vercelの関数ログを確認

## パフォーマンス最適化

Vercelは自動的に以下を最適化します：
- エッジキャッシング
- 画像最適化
- 自動的なコード分割
- プリレンダリング

## 無料プランの制限

- 100GB 帯域幅/月
- 100GB ビルド時間/月
- サーバーレス関数の実行時間制限

通常の使用では十分です。

## 参考リンク

- [Vercel Documentation](https://vercel.com/docs)
- [Nuxt on Vercel](https://vercel.com/docs/frameworks/nuxt)
- [Vercel CLI Reference](https://vercel.com/docs/cli)

