# 注文API修正手順

現在、注文APIで404エラーが発生しています。以下の手順で修正してください。

## 修正方法

### 方法1: orders.phpを置き換え（推奨）

1. `api-server/api/orders-fixed.php` の内容をコピー
2. エックスサーバーの `radish/api/orders.php` を開く
3. ファイルの内容をすべて `orders-fixed.php` の内容で置き換える
4. 保存

### 方法2: 手動で修正

`orders.php` の以下の部分を修正：

**修正前（11-22行目あたり）:**
```php
$method = $_SERVER['REQUEST_METHOD'];

// パスの取得と処理
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$orderId = null;

// /radish/api/orders/{id} の形式からIDを抽出
if (preg_match('#/orders/(\d+)$#', $path, $matches)) {
    $orderId = $matches[1];
} elseif (preg_match('#/orders/(\d+)/?#', $path, $matches)) {
    $orderId = $matches[1];
}
```

**修正後:**
```php
setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];

// パスの取得と処理
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$orderId = null;

// /radish/api/orders/{id} の形式からIDを抽出
// /orders/ の後に数字がある場合のみIDとして抽出
if (preg_match('#/orders/(\d+)/?$#', $path, $matches)) {
    $orderId = $matches[1];
}
```

また、`getOrders()`関数内の89行目あたりを修正：

**修正前:**
```php
$result = array_map(function($order) {
    $items = json_decode('[' . $order['items_json'] . ']', true) ?: [];
```

**修正後:**
```php
// データが空の場合は空配列を返す
if (empty($orders)) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    return;
}

// データ形式を変換
$result = array_map(function($order) {
    // items_jsonがnullの場合は空配列
    $itemsJson = $order['items_json'] ?? null;
    $items = $itemsJson ? json_decode('[' . $itemsJson . ']', true) : [];
    if (!is_array($items)) {
        $items = [];
    }
```

## 動作確認

修正後、以下のURLにアクセスして確認してください：

```
http://mameq.xsrv.jp/radish/api/orders
```

**期待される結果:**
```json
[]
```
（まだ注文がない場合は空の配列）

## 問題が解決しない場合

1. ファイルが正しく保存されているか確認
2. ブラウザのキャッシュをクリア
3. エックスサーバーのエラーログを確認

