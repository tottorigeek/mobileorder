# 環境変数設定ガイド

このプロジェクトでは、重要なセキュリティ情報（データベース接続情報、JWT秘密鍵など）を`.env`ファイルから読み込むように設定されています。

## セットアップ手順

### 1. `.env`ファイルの作成

`api-server/radish/`ディレクトリに`.env`ファイルを作成します：

```bash
cd api-server/radish
cp env.example .env
```

### 2. `.env`ファイルの編集

`.env`ファイルを開いて、実際の値を設定してください：

```env
# データベース接続情報
DB_HOST=localhost
DB_NAME=mameq_radish
DB_USER=mameq_radish
DB_PASS=your_actual_database_password
DB_CHARSET=utf8mb4

# 環境設定
ENVIRONMENT=production
# 開発環境の場合は: ENVIRONMENT=development

# JWT設定
# 本番環境では必ず強力な秘密鍵に変更してください
# 生成コマンド: openssl rand -base64 32
JWT_SECRET=your_strong_jwt_secret_here
JWT_ALGORITHM=HS256
JWT_EXPIRATION=604800

# フロントエンドのベースURL
FRONTEND_BASE_URL=https://mameq.xsrv.jp

# メール送信設定
MAIL_FROM=noreply@towndx.com
```

### 3. セキュリティ確認

- `.env`ファイルは`.gitignore`に含まれているため、Gitリポジトリにコミットされません
- `.env`ファイルには機密情報が含まれているため、**絶対に**Gitにコミットしないでください
- 本番環境では、`.env`ファイルのパーミッションを適切に設定してください（例: `chmod 600 .env`）

## 環境変数の説明

### データベース接続情報

- `DB_HOST`: データベースサーバーのホスト名（通常は`localhost`）
- `DB_NAME`: データベース名
- `DB_USER`: データベースユーザー名
- `DB_PASS`: データベースパスワード
- `DB_CHARSET`: 文字コード（通常は`utf8mb4`）

### 環境設定

- `ENVIRONMENT`: 実行環境（`development`または`production`）
  - `development`: 開発環境（デバッグモード有効）
  - `production`: 本番環境（デバッグモード無効）

### JWT設定

- `JWT_SECRET`: JWTトークンの署名に使用する秘密鍵
  - **重要**: 本番環境では必ず強力な秘密鍵に変更してください
  - 生成コマンド: `openssl rand -base64 32`
- `JWT_ALGORITHM`: JWTアルゴリズム（通常は`HS256`）
- `JWT_EXPIRATION`: トークンの有効期限（秒単位、デフォルトは7日間）

### フロントエンド設定

- `FRONTEND_BASE_URL`: フロントエンドアプリケーションのベースURL
  - パスワードリセットメールのリンクに使用されます

### メール設定

- `MAIL_FROM`: メール送信元のアドレス
- `MAIL_FROM_NAME`: メール送信元の名前（オプション、デフォルト: "Radish System"）

### SMTP設定（オプション）

SMTPを使用する場合は、以下の設定を追加してください：

- `MAIL_USE_SMTP`: SMTPを使用するかどうか（`true`または`false`、デフォルト: `false`）
  - `false`の場合、PHPの`mail()`関数が使用されます
  - `true`の場合、SMTP経由でメールが送信されます

- `MAIL_SMTP_HOST`: SMTPサーバーのホスト名（例: `smtp.gmail.com`）
- `MAIL_SMTP_PORT`: SMTPサーバーのポート番号（通常は`587`（TLS）または`465`（SSL））
- `MAIL_SMTP_SECURE`: 暗号化方式（`tls`または`ssl`）
  - ポート587の場合は`tls`
  - ポート465の場合は`ssl`
- `MAIL_SMTP_USER`: SMTP認証用のユーザー名
- `MAIL_SMTP_PASS`: SMTP認証用のパスワード
- `MAIL_SMTP_AUTH`: SMTP認証を使用するかどうか（`true`または`false`、デフォルト: `true`）

#### SMTP設定例

**Gmailの場合:**
```env
MAIL_USE_SMTP=true
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_SECURE=tls
MAIL_SMTP_USER=your-email@gmail.com
MAIL_SMTP_PASS=your-app-password
MAIL_SMTP_AUTH=true
```

**SendGridの場合:**
```env
MAIL_USE_SMTP=true
MAIL_SMTP_HOST=smtp.sendgrid.net
MAIL_SMTP_PORT=587
MAIL_SMTP_SECURE=tls
MAIL_SMTP_USER=apikey
MAIL_SMTP_PASS=your-sendgrid-api-key
MAIL_SMTP_AUTH=true
```

**エックスサーバーの場合:**
```env
MAIL_USE_SMTP=true
MAIL_SMTP_HOST=sv1234.xserver.jp
MAIL_SMTP_PORT=587
MAIL_SMTP_SECURE=tls
MAIL_SMTP_USER=your-email@yourdomain.com
MAIL_SMTP_PASS=your-email-password
MAIL_SMTP_AUTH=true
```

## デフォルト値

`.env`ファイルが存在しない場合、または環境変数が設定されていない場合、以下のデフォルト値が使用されます：

- `DB_HOST`: `localhost`
- `DB_NAME`: `mameq_radish`
- `DB_USER`: `mameq_radish`
- `DB_PASS`: （空文字列）
- `DB_CHARSET`: `utf8mb4`
- `ENVIRONMENT`: `development`
- `JWT_ALGORITHM`: `HS256`
- `JWT_EXPIRATION`: `604800`（7日間）
- `FRONTEND_BASE_URL`: `https://mameq.xsrv.jp`
- `MAIL_FROM`: `noreply@towndx.com`
- `MAIL_FROM_NAME`: `Radish System`
- `MAIL_USE_SMTP`: `false`（PHPの`mail()`関数を使用）

**注意**: デフォルト値は開発環境でのみ使用してください。本番環境では必ず`.env`ファイルを作成して適切な値を設定してください。

## トラブルシューティング

### `.env`ファイルが読み込まれない場合

1. `.env`ファイルが`api-server/radish/`ディレクトリに存在するか確認
2. ファイルのパーミッションを確認（読み取り可能である必要があります）
3. ファイルの構文を確認（`KEY=VALUE`形式、コメントは`#`で始まる）

### 環境変数が正しく読み込まれているか確認

`config.php`の先頭に以下を追加して確認できます：

```php
// デバッグ用（本番環境では削除してください）
if (DEBUG_MODE) {
    error_log('DB_HOST: ' . DB_HOST);
    error_log('DB_NAME: ' . DB_NAME);
    // パスワードはログに出力しないでください
}
```

### SMTPメール送信が失敗する場合

1. **SMTP設定の確認**
   - `.env`ファイルの`MAIL_USE_SMTP`が`true`に設定されているか確認
   - SMTPサーバーのホスト名、ポート番号、認証情報が正しいか確認

2. **ポート番号と暗号化方式の確認**
   - ポート587の場合は`MAIL_SMTP_SECURE=tls`
   - ポート465の場合は`MAIL_SMTP_SECURE=ssl`

3. **ファイアウォールの確認**
   - SMTPポート（587または465）が開いているか確認

4. **認証情報の確認**
   - Gmailを使用する場合、アプリパスワードが必要です（通常のパスワードでは動作しません）
   - SendGridを使用する場合、APIキーが必要です

5. **エラーログの確認**
   - PHPのエラーログを確認して、SMTP接続エラーの詳細を確認
   - エラーログには「SMTP connection failed」や「SMTP send failed」などのメッセージが記録されます

## セキュリティベストプラクティス

1. **`.env`ファイルをGitにコミットしない**
   - `.gitignore`に含まれていることを確認
   - 誤ってコミットした場合は、Git履歴から削除する必要があります

2. **本番環境では強力なパスワードと秘密鍵を使用**
   - データベースパスワードは複雑な文字列を使用
   - JWT秘密鍵は`openssl rand -base64 32`で生成

3. **ファイルパーミッションの設定**
   - `.env`ファイルのパーミッションを`600`（所有者のみ読み書き可能）に設定

4. **定期的なセキュリティ監査**
   - パスワードと秘密鍵を定期的に変更
   - 不要な環境変数を削除

