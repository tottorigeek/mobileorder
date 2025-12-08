#!/bin/bash
# API動作確認テストスクリプト

API_BASE="http://mameq.xsrv.jp/radish/api"
USERNAME="seki"
PASSWORD="password123"

echo "=========================================="
echo "API動作確認テスト"
echo "=========================================="
echo ""

# テスト1: ログイン
echo "テスト1: ログイン"
echo "----------------------------------------"
LOGIN_RESPONSE=$(curl -s -X POST "${API_BASE}/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"${USERNAME}\",\"password\":\"${PASSWORD}\"}")

echo "$LOGIN_RESPONSE" | jq '.'
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ ログインに失敗しました"
  exit 1
fi

echo "✅ ログイン成功"
echo "トークン: ${TOKEN:0:50}..."
echo ""

# テスト2: 認証状態確認
echo "テスト2: 認証状態確認"
echo "----------------------------------------"
AUTH_RESPONSE=$(curl -s -X GET "${API_BASE}/auth/me" \
  -H "Authorization: Bearer ${TOKEN}")

echo "$AUTH_RESPONSE" | jq '.'
if echo "$AUTH_RESPONSE" | jq -e '.id' > /dev/null 2>&1; then
  echo "✅ 認証状態確認成功"
else
  echo "❌ 認証状態確認に失敗しました"
fi
echo ""

# テスト3: メニュー一覧取得
echo "テスト3: メニュー一覧取得"
echo "----------------------------------------"
MENU_RESPONSE=$(curl -s -X GET "${API_BASE}/menus?shop=shop001")
echo "$MENU_RESPONSE" | jq '.'
if echo "$MENU_RESPONSE" | jq -e '.[0].id' > /dev/null 2>&1; then
  echo "✅ メニュー一覧取得成功"
else
  echo "❌ メニュー一覧取得に失敗しました"
fi
echo ""

# テスト4: 注文作成（認証済みユーザー）
echo "テスト4: 注文作成（認証済みユーザー）"
echo "----------------------------------------"
ORDER_RESPONSE=$(curl -s -X POST "${API_BASE}/orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -d '{
    "tableNumber": "5",
    "items": [
      {
        "menuId": "1",
        "menuNumber": "001",
        "menuName": "ハンバーガー",
        "quantity": 2,
        "price": 800
      }
    ],
    "totalAmount": 1600
  }')

echo "$ORDER_RESPONSE" | jq '.'
ORDER_ID=$(echo "$ORDER_RESPONSE" | jq -r '.id // empty')

if [ -z "$ORDER_ID" ]; then
  echo "❌ 注文作成に失敗しました"
else
  echo "✅ 注文作成成功 (注文ID: ${ORDER_ID})"
fi
echo ""

# テスト5: 注文作成（一般顧客）
echo "テスト5: 注文作成（一般顧客）"
echo "----------------------------------------"
GUEST_ORDER_RESPONSE=$(curl -s -X POST "${API_BASE}/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "shopCode": "shop001",
    "tableNumber": "6",
    "items": [
      {
        "menuId": "1",
        "menuNumber": "001",
        "menuName": "ハンバーガー",
        "quantity": 1,
        "price": 800
      }
    ],
    "totalAmount": 800
  }')

echo "$GUEST_ORDER_RESPONSE" | jq '.'
GUEST_ORDER_ID=$(echo "$GUEST_ORDER_RESPONSE" | jq -r '.id // empty')

if [ -z "$GUEST_ORDER_ID" ]; then
  echo "❌ 一般顧客の注文作成に失敗しました"
else
  echo "✅ 一般顧客の注文作成成功 (注文ID: ${GUEST_ORDER_ID})"
fi
echo ""

# テスト6: 注文一覧取得（認証済みユーザー）
echo "テスト6: 注文一覧取得（認証済みユーザー）"
echo "----------------------------------------"
ORDERS_RESPONSE=$(curl -s -X GET "${API_BASE}/orders" \
  -H "Authorization: Bearer ${TOKEN}")

echo "$ORDERS_RESPONSE" | jq '.'
if echo "$ORDERS_RESPONSE" | jq -e '.[0].id' > /dev/null 2>&1; then
  echo "✅ 注文一覧取得成功"
else
  echo "⚠️  注文一覧が空です（正常な場合もあります）"
fi
echo ""

# テスト7: 注文一覧取得（一般顧客）
echo "テスト7: 注文一覧取得（一般顧客）"
echo "----------------------------------------"
GUEST_ORDERS_RESPONSE=$(curl -s -X GET "${API_BASE}/orders?shop=shop001&tableNumber=6")

echo "$GUEST_ORDERS_RESPONSE" | jq '.'
if echo "$GUEST_ORDERS_RESPONSE" | jq -e '.[0].id' > /dev/null 2>&1; then
  echo "✅ 一般顧客の注文一覧取得成功"
else
  echo "⚠️  注文一覧が空です（正常な場合もあります）"
fi
echo ""

# テスト8: 注文ステータス更新
if [ ! -z "$ORDER_ID" ]; then
  echo "テスト8: 注文ステータス更新"
  echo "----------------------------------------"
  UPDATE_RESPONSE=$(curl -s -X PUT "${API_BASE}/orders/${ORDER_ID}" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${TOKEN}" \
    -d '{"status": "accepted"}')

  echo "$UPDATE_RESPONSE" | jq '.'
  if echo "$UPDATE_RESPONSE" | jq -e '.id' > /dev/null 2>&1; then
    echo "✅ 注文ステータス更新成功"
  else
    echo "❌ 注文ステータス更新に失敗しました"
  fi
  echo ""
fi

# テスト9: エラーハンドリング（無効なトークン）
echo "テスト9: エラーハンドリング（無効なトークン）"
echo "----------------------------------------"
ERROR_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X GET "${API_BASE}/auth/me" \
  -H "Authorization: Bearer invalid_token")

HTTP_STATUS=$(echo "$ERROR_RESPONSE" | grep "HTTP_STATUS" | cut -d: -f2)
if [ "$HTTP_STATUS" = "401" ]; then
  echo "✅ エラーハンドリング正常（401 Unauthorized）"
else
  echo "❌ エラーハンドリングに問題があります（HTTPステータス: ${HTTP_STATUS}）"
fi
echo ""

echo "=========================================="
echo "テスト完了"
echo "=========================================="

