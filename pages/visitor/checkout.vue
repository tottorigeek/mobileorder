<template>
  <NuxtLayout name="default" title="お会計">
    <div v-if="isLoading" class="text-center py-8 sm:py-12 px-4">
      <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
      <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-500">読み込み中...</p>
    </div>
    
    <div v-else-if="!visitorId" class="text-center py-8 sm:py-12 px-4">
      <p class="text-sm sm:text-base text-gray-500 mb-4">来店情報が見つかりません</p>
      <NuxtLink
        to="/visitor"
        class="inline-block px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base touch-target"
      >
        メニューに戻る
      </NuxtLink>
    </div>
    
    <div v-else class="space-y-4 sm:space-y-6 px-4 sm:px-0">
      <!-- 未完成注文の警告 -->
      <div v-if="hasIncompleteOrders" class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 sm:p-6">
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="flex-1">
            <h3 class="text-lg sm:text-xl font-bold text-yellow-900 mb-2">未完成の注文があります</h3>
            <p class="text-sm sm:text-base text-yellow-800 mb-3">
              以下の注文がまだ完成していません。完成をお待ちください。
            </p>
            <div class="space-y-2">
              <div
                v-for="order in incompleteOrders"
                :key="order.id"
                class="bg-white/60 rounded-lg p-3"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold text-yellow-900">{{ order.orderNumber }}</p>
                    <p class="text-xs sm:text-sm text-yellow-700">{{ getStatusLabel(order.status) }}</p>
                  </div>
                  <NuxtLink
                    :to="`/visitor/status/${order.id}`"
                    class="text-xs sm:text-sm text-yellow-700 hover:text-yellow-900 underline"
                  >
                    詳細を見る
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 注文一覧 -->
      <div class="space-y-3 sm:space-y-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">注文内容</h2>
        <div
          v-for="order in allOrders"
          :key="order.id"
          class="bg-white p-4 sm:p-6 rounded-lg shadow"
        >
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-0 mb-3">
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <p class="font-semibold text-base sm:text-lg">{{ order.orderNumber }}</p>
                <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs sm:text-sm font-medium">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>
              <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(order.createdAt) }}</p>
            </div>
            <div class="text-left sm:text-right">
              <p class="text-xl sm:text-2xl font-bold text-blue-600">¥{{ order.totalAmount.toLocaleString() }}</p>
            </div>
          </div>

          <!-- 注文アイテム -->
          <div class="border-t pt-3 mt-3">
            <div class="space-y-2">
              <div
                v-for="item in order.items"
                :key="item.menuId"
                class="flex justify-between text-sm"
              >
                <span>{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
                <span>¥{{ (item.price * item.quantity).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 合計金額 -->
      <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-xl text-white">
        <div class="flex justify-between items-center mb-2">
          <span class="text-lg sm:text-xl font-semibold">合計金額</span>
          <span class="text-2xl sm:text-3xl font-bold">¥{{ totalAmount.toLocaleString() }}</span>
        </div>
        <p v-if="hasIncompleteOrders" class="text-xs sm:text-sm text-blue-100 mt-2">
          ※未完成の注文は含まれていません
        </p>
      </div>

      <!-- 支払いボタン -->
      <div v-if="!hasIncompleteOrders" class="space-y-4">
        <div v-if="!showPaymentMethod" class="space-y-3">
          <button
            @click="showPaymentMethod = true"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 sm:py-5 rounded-xl text-lg sm:text-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target"
          >
            支払いをする
          </button>
        </div>
        
        <div v-else class="space-y-3">
          <p class="text-sm sm:text-base text-gray-700 mb-3 sm:mb-4 text-center">お支払い方法を選択してください</p>
          <button
            @click="processPayment('credit')"
            :disabled="isProcessingPayment"
            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 touch-target"
          >
            <svg v-if="!isProcessingPayment" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:h-5 border-b-2 border-white"></span>
            {{ isProcessingPayment ? '処理中...' : 'クレジットカード' }}
          </button>
          <button
            @click="processPayment('paypay')"
            :disabled="isProcessingPayment"
            class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-yellow-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 touch-target"
          >
            <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:h-5 border-b-2 border-white"></span>
            {{ isProcessingPayment ? '処理中...' : 'PayPay' }}
          </button>
          <button
            @click="processPayment('cash')"
            :disabled="isProcessingPayment"
            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 touch-target"
          >
            <svg v-if="!isProcessingPayment" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:h-5 border-b-2 border-white"></span>
            {{ isProcessingPayment ? '処理中...' : '現金' }}
          </button>
          <button
            @click="showPaymentMethod = false"
            :disabled="isProcessingPayment"
            class="w-full bg-gray-200 text-gray-700 py-2.5 sm:py-3 rounded-xl text-sm sm:text-base font-semibold hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-target"
          >
            キャンセル
          </button>
        </div>
      </div>

      <!-- 支払い完了メッセージ -->
      <div v-if="isPaymentCompleted" class="bg-green-50 border-2 border-green-300 rounded-xl p-4 sm:p-6 text-center">
        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-green-500 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">お支払いが完了しました</h3>
        <p v-if="paymentMethod === 'cash'" class="text-sm sm:text-base text-gray-700 mb-3 sm:mb-4">
          レジまでお越しください。スタッフがお待ちしております。
        </p>
        <p v-else class="text-sm sm:text-base text-gray-700 mb-3 sm:mb-4">
          ご利用ありがとうございました。
        </p>
        <NuxtLink
          to="/shop-select"
          class="inline-block px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-semibold touch-target"
        >
          トップに戻る
        </NuxtLink>
      </div>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>

    <!-- Bottom Navigation -->
    <CustomerBottomNav />
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useVisitorStore } from '~/stores/visitor'
import { useCartStore } from '~/stores/cart'
import { useShopStore } from '~/stores/shop'
import type { OrderStatus, PaymentMethod } from '~/types'
import CustomerBottomNav from '~/components/CustomerBottomNav.vue'

const orderStore = useOrderStore()
const visitorStore = useVisitorStore()
const cartStore = useCartStore()
const shopStore = useShopStore()

const isLoading = ref(true)
const showPaymentMethod = ref(false)
const isProcessingPayment = ref(false)
const isPaymentCompleted = ref(false)
const paymentMethod = ref<PaymentMethod | null>(null)
const visitorId = ref<string | null>(null)

// すべての注文（キャンセル以外）
const allOrders = computed(() => {
  return orderStore.orders.filter(order => order.status !== 'cancelled')
})

// 未完成の注文（completed以外）
const incompleteOrders = computed(() => {
  return allOrders.value.filter(order => order.status !== 'completed')
})

// 未完成の注文があるかどうか
const hasIncompleteOrders = computed(() => {
  return incompleteOrders.value.length > 0
})

// 合計金額（完成した注文のみ）
const totalAmount = computed(() => {
  return allOrders.value
    .filter(order => order.status === 'completed')
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル',
    checkout_pending: '会計前'
  }
  return labels[status] || status
}

const getStatusClass = (status: OrderStatus) => {
  const classes: Record<OrderStatus, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    accepted: 'bg-blue-100 text-blue-800',
    cooking: 'bg-orange-100 text-orange-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    checkout_pending: 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date: Date | string) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return d.toLocaleString('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(async () => {
  isLoading.value = true
  try {
    // ストレージからテーブル番号を読み込む
    cartStore.loadTableNumberFromStorage()
    
    // visitorIdを取得
    const activeVisitorId = typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null
    const currentVisitorId = cartStore.visitorId || activeVisitorId
    
    if (!currentVisitorId) {
      console.warn('visitorIdが設定されていません')
      isLoading.value = false
      return
    }
    
    visitorId.value = currentVisitorId
    
    // visitor情報を取得
    try {
      const visitor = await visitorStore.fetchVisitor(currentVisitorId)
      
      // visitor情報から店舗を取得
      if (visitor.shopId) {
        if (!shopStore.currentShop) {
          const shop = await shopStore.fetchShopById(visitor.shopId)
          if (shop) {
            shopStore.setCurrentShop(shop)
          }
        }
        
        // visitor情報からテーブル番号を設定（ナビゲーションバー表示用）
        if (visitor.tableNumber && !cartStore.tableNumber) {
          cartStore.setTableNumber(visitor.tableNumber)
        }
        
        // visitorIdを設定
        if (!cartStore.visitorId) {
          cartStore.setVisitorId(visitor.id)
        }
        
        // visitorのテーブル番号で注文を取得
        const shop = shopStore.currentShop || await shopStore.fetchShopById(visitor.shopId)
        if (shop && visitor.tableNumber) {
          await orderStore.fetchOrders(undefined, shop.code, undefined, visitor.tableNumber, currentVisitorId)
          
          // 注文状況の監視を開始（storeで管理）
          orderStore.startPolling({
            shopCode: shop.code,
            tableNumber: visitor.tableNumber,
            visitorId: currentVisitorId,
            interval: 5000
          })
        }
      }
    } catch (error) {
      console.error('visitor情報の取得に失敗しました:', error)
    }
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
})

// コンポーネントがアンマウントされたときに監視を停止
onUnmounted(() => {
  orderStore.stopPolling()
})

// orderStoreのordersが変更されたときに再評価
watch(() => orderStore.orders, () => {
  // 自動的に再計算される
}, { deep: true })

const processPayment = async (method: PaymentMethod) => {
  if (!visitorId.value) {
    alert('来店情報が見つかりません')
    return
  }
  
  // 'electronic'は'paypay'に変換（APIが対応している支払い方法のみ）
  const paymentMethodForApi: 'cash' | 'credit' | 'paypay' = method === 'electronic' ? 'paypay' : method as 'cash' | 'credit' | 'paypay'
  
  isProcessingPayment.value = true
  try {
    // ダミー処理（2秒待機）
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // まず会計処理を実行（total_amountとcheckout_timeを設定）
    try {
      await visitorStore.processCheckout(visitorId.value)
    } catch (checkoutError) {
      console.warn('会計処理の実行に失敗しました（支払い処理は続行します）:', checkoutError)
    }
    
    // 支払い処理を実行
    await visitorStore.processPayment(visitorId.value, paymentMethodForApi)
    
    isPaymentCompleted.value = true
    paymentMethod.value = method
    showPaymentMethod.value = false
    
    // 支払い完了時にセッション情報をクリア
    cartStore.clearSession()
    shopStore.setCurrentShop(null)
    
    // ローカルストレージからも削除
    if (typeof window !== 'undefined') {
      localStorage.removeItem('tableNumber')
      localStorage.removeItem('activeOrderId')
      localStorage.removeItem('activeVisitorId')
      localStorage.removeItem('currentShop')
    }
    
    // 監視を停止
    orderStore.stopPolling()
  } catch (error: any) {
    alert('支払い処理に失敗しました: ' + (error.message || 'エラーが発生しました'))
  } finally {
    isProcessingPayment.value = false
  }
}
</script>

