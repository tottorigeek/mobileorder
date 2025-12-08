# セットアップガイド

## 必要な環境

- Node.js 18以上
- npm または yarn

## インストール手順

1. 依存関係のインストール
```bash
npm install
```

2. 環境変数の設定（オプション）
```bash
cp .env.example .env
# .envファイルを編集して必要な設定を行う
```

3. 開発サーバーの起動
```bash
npm run dev
```

ブラウザで `http://localhost:3000` にアクセスしてください。

## プロジェクト構造

```
├── assets/              # 静的アセット
│   └── css/            # スタイルシート
├── components/         # Vueコンポーネント
│   ├── MenuCard.vue   # メニューカード
│   └── NumberInput.vue # 番号入力コンポーネント
├── composables/        # コンポーザブル関数
│   └── useOrder.ts    # 注文関連のロジック
├── layouts/           # レイアウトコンポーネント
│   └── default.vue    # デフォルトレイアウト
├── pages/             # ページコンポーネント
│   ├── customer/      # 顧客側ページ
│   │   ├── index.vue  # メニュー一覧
│   │   ├── cart.vue   # カート
│   │   ├── order/     # 注文確認
│   │   └── status/    # 注文状況
│   ├── staff/         # 店舗側ページ
│   │   ├── login.vue  # ログイン
│   │   └── orders.vue # 注文管理
│   └── index.vue      # トップページ
├── server/            # サーバーサイドAPI
│   └── api/          # APIエンドポイント
├── stores/           # Piniaストア
│   ├── menu.ts       # メニュー管理
│   ├── cart.ts       # カート管理
│   └── order.ts      # 注文管理
└── types/            # TypeScript型定義
    └── index.ts      # 共通型定義
```

## 主な機能

### 顧客側
- `/customer` - メニュー一覧と番号入力
- `/customer/cart` - カート確認
- `/customer/order/[id]` - 注文確認
- `/customer/status/[id]` - 注文状況確認

### 店舗側
- `/staff/login` - スタッフログイン
- `/staff/orders` - 注文管理

## 次のステップ

1. データベースの接続設定
2. 認証機能の実装
3. リアルタイム更新機能（WebSocket）
4. PWA対応
5. レポート機能の実装

