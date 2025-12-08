# GitHub → Vercel デプロイ手順

GitHubリポジトリ: https://github.com/tottorigeek/mobileorder.git

## ✅ 完了した作業

- [x] GitHubリポジトリへのコードプッシュ完了

## Vercelでのデプロイ手順

### 1. Vercelアカウントの準備

1. [Vercel](https://vercel.com) にアクセス
2. **Sign Up** をクリック
3. **Continue with GitHub** を選択してGitHubアカウントでログイン
4. VercelがGitHubリポジトリへのアクセスを要求するので、**Authorize Vercel** をクリック

### 2. プロジェクトのインポート

1. Vercelダッシュボードで **Add New Project** をクリック
2. **Import Git Repository** セクションで **mobileorder** リポジトリを検索・選択
3. **Import** をクリック

### 3. プロジェクト設定

Vercelが自動的にNuxtプロジェクトを検出します。以下の設定を確認：

- **Framework Preset**: `Nuxt.js` （自動検出）
- **Root Directory**: `./` （そのまま）
- **Build Command**: `npm run build` （自動設定）
- **Output Directory**: `.output` （自動設定）
- **Install Command**: `npm install` （自動設定）

### 4. 環境変数の設定（オプション）

必要に応じて環境変数を設定：

1. **Environment Variables** セクションを展開
2. 以下の変数を追加（必要に応じて）：
   - **Name**: `NUXT_PUBLIC_API_BASE`
   - **Value**: `https://your-api-domain.com/api`
   - **Environment**: Production, Preview, Development すべてにチェック

### 5. デプロイの実行

1. **Deploy** ボタンをクリック
2. ビルドが開始されます（1-3分程度）
3. デプロイが完了すると、以下のURLが表示されます：
   - **Production URL**: `https://mobileorder-xxxxx.vercel.app`
   - カスタムドメインも設定可能

### 6. 自動デプロイの確認

GitHubと連携しているため、以下の操作で自動的にデプロイされます：

- **`main`ブランチにプッシュ** → 本番環境に自動デプロイ
- **他のブランチにプッシュ** → プレビュー環境に自動デプロイ
- **プルリクエスト作成** → プレビュー環境に自動デプロイ

## デプロイ後の確認事項

### ✅ 動作確認

1. デプロイされたURLにアクセス
2. トップページが表示されるか確認
3. 顧客側ページ（`/customer`）が動作するか確認
4. 店舗側ページ（`/staff/login`）が動作するか確認

### 🔧 トラブルシューティング

#### ビルドエラーが発生する場合

1. Vercelダッシュボードの **Deployments** タブを開く
2. 失敗したデプロイをクリック
3. **Build Logs** を確認してエラー内容を確認

よくある問題：
- Node.jsのバージョン不一致 → `package.json`の`engines`を確認
- 依存関係のエラー → `package-lock.json`が正しくプッシュされているか確認

#### ページが表示されない場合

1. デプロイが完了しているか確認
2. ビルドログにエラーがないか確認
3. ブラウザのコンソールでエラーを確認

#### APIルートが動作しない場合

- `server/api` ディレクトリ内のAPIルートは自動的にサーバーレス関数として動作します
- Vercelの関数ログで確認できます

## カスタムドメインの設定（オプション）

1. Vercelダッシュボードの **Settings** → **Domains** に移動
2. **Add Domain** をクリック
3. ドメイン名を入力（例: `mobileorder.yourdomain.com`）
4. DNS設定でCNAMEレコードを追加（Vercelが指示を表示）

## 次のステップ

- [ ] デプロイの確認
- [ ] カスタムドメインの設定（必要に応じて）
- [ ] 環境変数の設定（APIのベースURLなど）
- [ ] データベースの接続（必要に応じて）

## 参考リンク

- [Vercel Documentation](https://vercel.com/docs)
- [Nuxt on Vercel](https://vercel.com/docs/frameworks/nuxt)
- [GitHub Repository](https://github.com/tottorigeek/mobileorder)

