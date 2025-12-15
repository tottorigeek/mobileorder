<template>
  <NuxtLayout name="visitor" title="注文状況">
    <div v-if="isLoading" class="text-center py-8 sm:py-12 px-4">
      <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
      <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-500">読み込み中...</p>
    </div>
    
    <div v-else-if="order" class="space-y-4 sm:space-y-6 px-4 sm:px-0">
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow">
        <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4">注文状況</h2>
        <div class="space-y-2 sm:space-y-3">
          <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
            <span class="text-sm sm:text-base text-gray-600">注文番号:</span>
            <span class="text-sm sm:text-base font-semibold break-all sm:break-normal">{{ order.orderNumber }}</span>
          </div>
          <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
            <span class="text-sm sm:text-base text-gray-600">テーブル番号:</span>
            <span class="text-sm sm:text-base font-semibold">{{ order.tableNumber }}</span>
          </div>
          <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
            <span class="text-sm sm:text-base text-gray-600">ステータス:</span>
            <span :class="statusClass" class="text-sm sm:text-base font-semibold">{{ statusLabel }}</span>
          </div>
        </div>
      </div>

      <!-- ステータス進行バー -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow">
        <div class="space-y-4">
          <div class="flex items-center overflow-x-auto pb-2">
            <div :class="getStepClass('pending')" class="flex-1 min-w-[60px] sm:min-w-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2 text-xs sm:text-base">
                <span v-if="isStepCompleted('pending')">✓</span>
                <span v-else>1</span>
              </div>
              <p class="text-xs sm:text-sm text-center">受付待ち</p>
            </div>
            <div :class="getLineClass('pending', 'accepted')" class="flex-1 h-0.5 sm:h-1 min-w-[20px]"></div>
            <div :class="getStepClass('accepted')" class="flex-1 min-w-[60px] sm:min-w-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2 text-xs sm:text-base">
                <span v-if="isStepCompleted('accepted')">✓</span>
                <span v-else>2</span>
              </div>
              <p class="text-xs sm:text-sm text-center">受付済み</p>
            </div>
            <div :class="getLineClass('accepted', 'cooking')" class="flex-1 h-0.5 sm:h-1 min-w-[20px]"></div>
            <div :class="getStepClass('cooking')" class="flex-1 min-w-[60px] sm:min-w-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2 text-xs sm:text-base">
                <span v-if="isStepCompleted('cooking')">✓</span>
                <span v-else>3</span>
              </div>
              <p class="text-xs sm:text-sm text-center">調理中</p>
            </div>
            <div :class="getLineClass('cooking', 'completed')" class="flex-1 h-0.5 sm:h-1 min-w-[20px]"></div>
            <div :class="getStepClass('completed')" class="flex-1 min-w-[60px] sm:min-w-0">
              <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2 text-xs sm:text-base">
                <span v-if="isStepCompleted('completed')">✓</span>
                <span v-else>4</span>
              </div>
              <p class="text-xs sm:text-sm text-center">完成</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 注文内容 -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow">
        <h3 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">注文内容</h3>
        <div class="space-y-2">
          <div
            v-for="item in order.items"
            :key="item.menuId"
            class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0 py-2 border-b last:border-0"
          >
            <span class="text-sm sm:text-base break-words">{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
            <span class="text-sm sm:text-base font-semibold sm:font-normal">¥{{ (item.price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t flex justify-between text-base sm:text-lg font-bold">
          <span>合計</span>
          <span>¥{{ order.totalAmount.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 清算ボタン（注文が完成している場合） -->
      <div v-if="order.status === 'completed' && !isPaymentCompleted" class="space-y-4">
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 sm:p-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">お会計</h3>
          <p class="text-sm sm:text-base text-gray-700 mb-3 sm:mb-4">合計金額: <span class="text-xl sm:text-2xl font-bold text-blue-600">¥{{ order.totalAmount.toLocaleString() }}</span></p>
          
          <div v-if="!showPaymentMethod" class="space-y-3">
            <button
              @click="showPaymentMethod = true"
              class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl touch-target"
            >
              清算する
            </button>
          </div>
          
          <div v-else class="space-y-3">
            <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">お支払い方法を選択してください</p>
            <button
              @click="processPayment('credit')"
              :disabled="isProcessingPayment"
              class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 touch-target"
            >
              <svg v-if="!isProcessingPayment" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white"></span>
              {{ isProcessingPayment ? '処理中...' : 'クレジットカード' }}
            </button>
            <button
              @click="processPayment('paypay')"
              :disabled="isProcessingPayment"
              class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:from-yellow-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 touch-target"
            >
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white"></span>
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
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-4 w-4 sm:h-5 sm:w-5 border-b-2 border-white"></span>
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
      </div>

      <NuxtLink
        to="/visitor"
        class="block w-full bg-blue-600 text-white py-3 sm:py-4 rounded-lg text-center text-base sm:text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
      >
        メニューに戻る
      </NuxtLink>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>
    
    <div v-else class="text-center py-8 sm:py-12 px-4">
      <p class="text-sm sm:text-base text-gray-500">注文が見つかりません</p>
      <NuxtLink
        to="/visitor"
        class="inline-block mt-3 sm:mt-4 px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base touch-target"
      >
        メニューに戻る
      </NuxtLink>
      
      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useVisitorStore } from '~/stores/visitor'
import { useCartStore } from '~/stores/cart'
import { useShopStore } from '~/stores/shop'
import type { OrderStatus, Order, PaymentMethod } from '~/types'

const route = useRoute()
const orderStore = useOrderStore()
const visitorStore = useVisitorStore()
const cartStore = useCartStore()
const shopStore = useShopStore()
const orderId = route.params.id as string

// 注文を取得
const order = ref<Order | null>(null)
const isLoading = ref(true)
const showPaymentMethod = ref(false)
const isProcessingPayment = ref(false)
const isPaymentCompleted = ref(false)
const paymentMethod = ref<PaymentMethod | null>(null)

onMounted(async () => {
  // まずローカルストアから取得を試みる
  const localOrder = orderStore.orders.find(o => o.id === orderId)
  if (localOrder) {
    order.value = localOrder
    isLoading.value = false
  } else {
    // ローカルにない場合はAPIから取得
    try {
      // orderStoreのfetchOrderメソッドを使用
      await orderStore.fetchOrder(orderId)
      const fetchedOrder = orderStore.orders.find(o => o.id === orderId)
      if (fetchedOrder) {
        order.value = fetchedOrder
      }
    } catch (error) {
      console.error('注文の取得に失敗しました:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  // 注文情報からテーブル番号を取得してcartStoreに設定（ヘッダー表示用）
  if (order.value && order.value.tableNumber && !cartStore.tableNumber) {
    cartStore.setTableNumber(order.value.tableNumber)
  }
  
  // ローカルストレージからvisitorIdを読み込む
  if (!cartStore.visitorId && typeof window !== 'undefined') {
    const storedVisitorId = localStorage.getItem('activeVisitorId')
    if (storedVisitorId) {
      cartStore.setVisitorId(storedVisitorId)
    }
  }
  
  // 注文状況の監視を開始（storeで管理）
  if (order.value && shopStore.currentShop) {
    const visitorId = cartStore.visitorId || (typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null)
    orderStore.startPolling({
      shopCode: shopStore.currentShop.code,
      tableNumber: order.value.tableNumber,
      visitorId: visitorId || undefined,
      interval: 5000
    })
  }
})

// コンポーネントがアンマウントされたときに監視を停止
onUnmounted(() => {
  orderStore.stopPolling()
})

// orderStoreのordersが変更されたときに、現在の注文を更新
watch(() => orderStore.orders, () => {
  const updatedOrder = orderStore.orders.find(o => o.id === orderId)
  if (updatedOrder) {
    order.value = updatedOrder
  }
}, { deep: true })

const statusLabel = computed(() => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル',
    checkout_pending: '会計前'
  }
  return order.value ? labels[order.value.status] : ''
})

const statusClass = computed(() => {
  const classes: Record<OrderStatus, string> = {
    pending: 'text-yellow-600',
    accepted: 'text-blue-600',
    cooking: 'text-orange-600',
    completed: 'text-green-600',
    cancelled: 'text-red-600',
    checkout_pending: 'text-orange-600'
  }
  return order.value ? classes[order.value.status] : ''
})

const statusOrder: OrderStatus[] = ['pending', 'accepted', 'cooking', 'completed']

const isStepCompleted = (status: OrderStatus) => {
  if (!order.value) return false
  const currentIndex = statusOrder.indexOf(order.value.status)
  const stepIndex = statusOrder.indexOf(status)
  return currentIndex > stepIndex || order.value.status === status
}

const getStepClass = (status: OrderStatus) => {
  if (!order.value) return ''
  const currentIndex = statusOrder.indexOf(order.value.status)
  const stepIndex = statusOrder.indexOf(status)
  if (currentIndex > stepIndex || order.value.status === status) {
    return 'bg-blue-600 text-white'
  }
  return 'bg-gray-200 text-gray-500'
}

const processPayment = async (method: PaymentMethod) => {
  // visitorIdを取得（複数のソースから試行）
  let visitorId = cartStore.visitorId
  
  // cartStoreにvisitorIdがない場合、ローカルストレージから取得を試みる
  if (!visitorId && typeof window !== 'undefined') {
    visitorId = localStorage.getItem('activeVisitorId') || null
    if (visitorId) {
      cartStore.setVisitorId(visitorId)
    }
  }
  
  // まだ見つからない場合、テーブル番号からvisitorを検索
  if (!visitorId && order.value && order.value.tableNumber && shopStore.currentShop) {
    try {
      // テーブル番号からvisitorを検索（認証が必要な可能性があるため、エラーハンドリング）
      const visitors = await visitorStore.fetchVisitors(shopStore.currentShop.id, {
        tableNumber: order.value.tableNumber,
        paymentStatus: 'pending'
      })
      
      if (visitors && visitors.length > 0) {
        // 最新のvisitorを使用
        const latestVisitor = visitors[0]
        visitorId = latestVisitor.id
        cartStore.setVisitorId(visitorId)
      }
    } catch (error) {
      console.error('テーブル番号からのvisitor検索に失敗:', error)
    }
  }
  
  // visitorIdが見つからない場合、詳細なエラーメッセージを表示
  if (!visitorId) {
    const debugInfo = {
      cartStoreVisitorId: cartStore.visitorId || 'null',
      localStorageVisitorId: typeof window !== 'undefined' ? (localStorage.getItem('activeVisitorId') || 'null') : 'N/A',
      orderTableNumber: order.value?.tableNumber || 'null',
      shopId: shopStore.currentShop?.id || 'null',
      orderId: orderId
    }
    
    alert(`来店情報が見つかりません\n\nデバッグ情報:\n- cartStore.visitorId: ${debugInfo.cartStoreVisitorId}\n- localStorage.activeVisitorId: ${debugInfo.localStorageVisitorId}\n- 注文テーブル番号: ${debugInfo.orderTableNumber}\n- 店舗ID: ${debugInfo.shopId}\n- 注文ID: ${debugInfo.orderId}`)
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
      await visitorStore.processCheckout(visitorId)
    } catch (checkoutError) {
      console.warn('会計処理の実行に失敗しました（支払い処理は続行します）:', checkoutError)
    }
    
    // 支払い処理を実行
    await visitorStore.processPayment(visitorId, paymentMethodForApi)
    
    isPaymentCompleted.value = true
    paymentMethod.value = method
    showPaymentMethod.value = false
    
    // 支払い完了時にセッション情報をクリア（精算完了したので/shop-selectに戻れるようにする）
    cartStore.clearSession()
    shopStore.setCurrentShop(null)
    
    // ローカルストレージからも削除
    if (typeof window !== 'undefined') {
      localStorage.removeItem('tableNumber')
      localStorage.removeItem('activeOrderId')
      localStorage.removeItem('activeVisitorId')
      localStorage.removeItem('currentShop')
    }
  } catch (error: any) {
    alert('支払い処理に失敗しました: ' + (error.message || 'エラーが発生しました'))
  } finally {
    isProcessingPayment.value = false
  }
}

const getStatusClass = (status: OrderStatus) => {
  if (!order.value) return ''
  const isCompleted = isStepCompleted(status)
  const isCurrent = order.value.status === status
  
  if (isCompleted) return 'text-green-600'
  if (isCurrent) return 'text-blue-600'
  return 'text-gray-400'
}

const getLineClass = (from: OrderStatus, to: OrderStatus) => {
  if (!order.value) return 'bg-gray-300'
  const currentIndex = statusOrder.indexOf(order.value.status)
  const toIndex = statusOrder.indexOf(to)
  
  if (currentIndex >= toIndex) return 'bg-green-600'
  return 'bg-gray-300'
}
</script>

