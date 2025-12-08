<template>
  <NuxtLayout name="default" title="注文状況">
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-500">読み込み中...</p>
    </div>
    
    <div v-else-if="order" class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">注文状況</h2>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">注文番号:</span>
            <span class="font-semibold">{{ order.orderNumber }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">テーブル番号:</span>
            <span class="font-semibold">{{ order.tableNumber }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">ステータス:</span>
            <span :class="statusClass" class="font-semibold">{{ statusLabel }}</span>
          </div>
        </div>
      </div>

      <!-- ステータス進行バー -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="space-y-4">
          <div class="flex items-center">
            <div :class="getStepClass('pending')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('pending')">✓</span>
                <span v-else>1</span>
              </div>
              <p class="text-sm text-center">受付待ち</p>
            </div>
            <div :class="getLineClass('pending', 'accepted')" class="flex-1 h-1"></div>
            <div :class="getStepClass('accepted')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('accepted')">✓</span>
                <span v-else>2</span>
              </div>
              <p class="text-sm text-center">受付済み</p>
            </div>
            <div :class="getLineClass('accepted', 'cooking')" class="flex-1 h-1"></div>
            <div :class="getStepClass('cooking')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('cooking')">✓</span>
                <span v-else>3</span>
              </div>
              <p class="text-sm text-center">調理中</p>
            </div>
            <div :class="getLineClass('cooking', 'completed')" class="flex-1 h-1"></div>
            <div :class="getStepClass('completed')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('completed')">✓</span>
                <span v-else>4</span>
              </div>
              <p class="text-sm text-center">完成</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 注文内容 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">注文内容</h3>
        <div class="space-y-2">
          <div
            v-for="item in order.items"
            :key="item.menuId"
            class="flex justify-between py-2 border-b last:border-0"
          >
            <span>{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
            <span>¥{{ (item.price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t flex justify-between text-lg font-bold">
          <span>合計</span>
          <span>¥{{ order.totalAmount.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 清算ボタン（注文が完成している場合） -->
      <div v-if="order.status === 'completed' && !isPaymentCompleted" class="space-y-4">
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6">
          <h3 class="text-xl font-bold text-gray-900 mb-4">お会計</h3>
          <p class="text-gray-700 mb-4">合計金額: <span class="text-2xl font-bold text-blue-600">¥{{ order.totalAmount.toLocaleString() }}</span></p>
          
          <div v-if="!showPaymentMethod" class="space-y-3">
            <button
              @click="showPaymentMethod = true"
              class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl text-lg font-bold hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl"
            >
              清算する
            </button>
          </div>
          
          <div v-else class="space-y-3">
            <p class="text-sm text-gray-600 mb-4">お支払い方法を選択してください</p>
            <button
              @click="processPayment('credit')"
              :disabled="isProcessingPayment"
              class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl text-lg font-bold hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
            >
              <svg v-if="!isProcessingPayment" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
              {{ isProcessingPayment ? '処理中...' : 'クレジットカード' }}
            </button>
            <button
              @click="processPayment('paypay')"
              :disabled="isProcessingPayment"
              class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-4 rounded-xl text-lg font-bold hover:from-yellow-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
            >
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
              {{ isProcessingPayment ? '処理中...' : 'PayPay' }}
            </button>
            <button
              @click="processPayment('cash')"
              :disabled="isProcessingPayment"
              class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl text-lg font-bold hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
            >
              <svg v-if="!isProcessingPayment" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span v-if="isProcessingPayment" class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
              {{ isProcessingPayment ? '処理中...' : '現金' }}
            </button>
            <button
              @click="showPaymentMethod = false"
              :disabled="isProcessingPayment"
              class="w-full bg-gray-200 text-gray-700 py-3 rounded-xl text-base font-semibold hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              キャンセル
            </button>
          </div>
        </div>
      </div>
      
      <!-- 支払い完了メッセージ -->
      <div v-if="isPaymentCompleted" class="bg-green-50 border-2 border-green-300 rounded-xl p-6 text-center">
        <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">お支払いが完了しました</h3>
        <p v-if="paymentMethod === 'cash'" class="text-gray-700 mb-4">
          レジまでお越しください。スタッフがお待ちしております。
        </p>
        <p v-else class="text-gray-700 mb-4">
          ご利用ありがとうございました。
        </p>
      </div>

      <NuxtLink
        to="/customer"
        class="block w-full bg-blue-600 text-white py-4 rounded-lg text-center text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
      >
        メニューに戻る
      </NuxtLink>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-500">注文が見つかりません</p>
      <NuxtLink
        to="/customer"
        class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        メニューに戻る
      </NuxtLink>

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
import type { OrderStatus, Order, PaymentMethod } from '~/types'
import CustomerBottomNav from '~/components/CustomerBottomNav.vue'

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
      const config = useRuntimeConfig()
      const apiBase = config.public.apiBase
      const orderData = await $fetch<Order>(`${apiBase}/orders/${orderId}`)
      order.value = {
        ...orderData,
        createdAt: new Date(orderData.createdAt),
        updatedAt: new Date(orderData.updatedAt)
      }
    } catch (error) {
      console.error('注文の取得に失敗しました:', error)
    } finally {
      isLoading.value = false
    }
  }
})

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
  if (!cartStore.visitorId) {
    alert('来店情報が見つかりません')
    return
  }
  
  // 'electronic'は'paypay'に変換（APIが対応している支払い方法のみ）
  const paymentMethodForApi: 'cash' | 'credit' | 'paypay' = method === 'electronic' ? 'paypay' : method as 'cash' | 'credit' | 'paypay'
  
  isProcessingPayment.value = true
  try {
    // ダミー処理（2秒待機）
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // 支払い処理を実行
    await visitorStore.processPayment(cartStore.visitorId, paymentMethodForApi)
    
    isPaymentCompleted.value = true
    paymentMethod.value = method
    showPaymentMethod.value = false
    
    // 支払い完了時にセッション情報をクリア（精算完了したので/shop-selectに戻れるようにする）
    cartStore.clearSession()
    shopStore.setCurrentShop(null)
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

