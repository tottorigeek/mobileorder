<template>
  <NuxtLayout name="visitor" title="注文一覧">
    <div class="space-y-4 sm:space-y-6 px-4 sm:px-0">
      <div v-if="isLoading" class="text-center py-8 sm:py-12">
        <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
        <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-500">読み込み中...</p>
      </div>
      
      <div v-else-if="orders.length === 0" class="text-center py-8 sm:py-12">
        <p class="text-gray-500 text-sm sm:text-base">注文がありません</p>
      </div>
      
      <div v-else class="space-y-3 sm:space-y-4">
        <div
          v-for="order in orders"
          :key="order.id"
          class="bg-white p-4 sm:p-6 rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-0 mb-3">
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <p class="font-semibold text-base sm:text-lg">{{ order.orderNumber }}</p>
                <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs sm:text-sm font-medium">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mb-1">テーブル: {{ order.tableNumber }}</p>
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

          <!-- アクションボタン -->
          <div class="flex flex-col sm:flex-row gap-2 mt-4 pt-4 border-t">
            <NuxtLink
              :to="`/visitor/order/${order.id}`"
              class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium touch-target"
            >
              詳細を見る
            </NuxtLink>
            <NuxtLink
              v-if="order.status === 'completed'"
              :to="`/visitor/status/${order.id}`"
              class="flex-1 text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base font-medium touch-target"
            >
              お会計
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useCartStore } from '~/stores/cart'
import { useShopStore } from '~/stores/shop'
import { useVisitorStore } from '~/stores/visitor'
import type { OrderStatus } from '~/types'

const orderStore = useOrderStore()
const cartStore = useCartStore()
const shopStore = useShopStore()
const visitorStore = useVisitorStore()

const isLoading = ref(false)
const orders = computed(() => orderStore.orders)

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
    
    // visitorIdから注文を取得
    const activeVisitorId = typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null
    const currentVisitorId = cartStore.visitorId || activeVisitorId
    
    if (!currentVisitorId) {
      console.warn('visitorIdが設定されていません')
      isLoading.value = false
      return
    }
    
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
        
        // visitorのテーブル番号で注文を取得（visitorがそのテーブルで行った注文のみ）
        const shop = shopStore.currentShop || await shopStore.fetchShopById(visitor.shopId)
        if (shop && visitor.tableNumber) {
          // visitorのテーブル番号で注文を取得
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
    console.error('注文一覧の取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
})

// コンポーネントがアンマウントされたときに監視を停止
onUnmounted(() => {
  orderStore.stopPolling()
})
</script>

