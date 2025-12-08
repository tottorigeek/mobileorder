# API接続設定ガイド

NuxtアプリケーションとAPIサーバーを接続するための設定ガイドです。

## APIサーバーのURL

APIサーバー:
```
https://api.towndx.com/radish/v1
```

## 設定方法

### 方法1: 環境変数で設定（推奨）

`.env`ファイルを作成（`.env.example`をコピー）:

```bash
cp .env.example .env
```

`.env`ファイルを編集:

```bash
NUXT_PUBLIC_API_BASE=https://api.towndx.com/radish/v1
```

### 方法2: nuxt.config.tsで直接設定

`nuxt.config.ts`の`runtimeConfig`で設定（既に設定済み）:

```typescript
runtimeConfig: {
  public: {
    apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.towndx.com/radish/v1'
  }
}
```

## APIエンドポイント

### メニューAPI

- **GET** `/api/menus` - メニュー一覧取得
  - クエリパラメータ: `category` (オプション)

### 注文API

- **GET** `/api/orders` - 注文一覧取得
  - クエリパラメータ: `status` (オプション)
- **GET** `/api/orders/{id}` - 注文取得（単一）
- **POST** `/api/orders` - 注文作成
- **PUT** `/api/orders/{id}` - 注文ステータス更新

## 動作確認

1. APIサーバーが正常に動作しているか確認:
   ```
   https://api.towndx.com/radish/v1/api/menus
   ```
   ブラウザでアクセスしてJSONが返ることを確認

2. Nuxtアプリを起動:
   ```bash
   npm run dev
   ```

3. メニュー一覧ページでメニューが表示されることを確認

## トラブルシューティング

### CORSエラーが発生する場合

APIサーバーの`.htaccess`ファイルでCORS設定が正しく設定されているか確認してください。
`api-server/radish/v1/config.php`の許可オリジンリストにフロントエンドのドメインが含まれているか確認してください。

### API接続エラーが発生する場合

1. APIサーバーが正常に動作しているか確認
2. `NUXT_PUBLIC_API_BASE`のURLが正しいか確認
3. ブラウザの開発者ツールのネットワークタブでエラーを確認
4. Mixed Contentエラーが発生していないか確認（HTTPSページからHTTP APIへのリクエストはブロックされます）

### データが取得できない場合

1. データベースにデータが存在するか確認
2. APIサーバーのエラーログを確認
3. `api-server/radish/v1/config.php`のデータベース接続情報が正しいか確認

