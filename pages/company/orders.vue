<template>
  <NuxtLayout name="company" title="注文管理">
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-1">注文管理</h1>
          <p class="text-gray-600">全店舗の注文を一元管理</p>
        </div>
      </div>

      <!-- フィルター -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗で絞り込み
            </label>
            <select
              v-model="selectedShopId"
              @change="filterOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべての店舗</option>
              <option
                v-for="shop in allShops"
                :key="shop.id"
                :value="shop.id"
              >
                {{ shop.name }}
              </option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              ステータスで絞り込み
            </label>
            <select
              v-model="selectedStatus"
              @change="filterOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="pending">受付待ち</option>
              <option value="accepted">受付済み</option>
              <option value="cooking">調理中</option>
              <option value="completed">完成</option>
              <option value="cancelled">キャンセル</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="orderStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- 注文一覧 -->
      <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-lg shadow">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-gray-500 font-medium">注文がありません</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-green-300"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-bold text-gray-900">注文番号: {{ order.orderNumber }}</h3>
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                  {{ getShopName(order.shopId) }}
                </span>
                <span :class="getStatusClass(order.status)">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>
              <div class="flex items-center gap-4 text-sm text-gray-600">
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  テーブル {{ order.tableNumber }}
                </span>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(order.createdAt) }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-green-600">¥{{ order.totalAmount.toLocaleString() }}</p>
            </div>
          </div>

          <!-- 注文内容 -->
          <div class="mb-4 p-4 bg-gray-50 rounded-xl">
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              注文内容
            </h4>
            <ul class="space-y-2">
              <li
                v-for="item in order.items"
                :key="item.menuId"
                class="text-sm flex justify-between items-center p-2 bg-white rounded-lg"
              >
                <span>{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
                <span class="font-medium">¥{{ (item.price * item.quantity).toLocaleString() }}</span>
              </li>
            </ul>
          </div>

          <!-- アクションボタン -->
          <div class="flex gap-2 pt-4 border-t border-gray-200">
            <button
              v-if="order.status === 'pending'"
              @click="updateOrderStatus(order.id, 'accepted')"
              class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
            >
              受付
            </button>
            <button
              v-if="order.status === 'accepted'"
              @click="updateOrderStatus(order.id, 'cooking')"
              class="px-5 py-2.5 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
            >
              調理開始
            </button>
            <button
              v-if="order.status === 'cooking'"
              @click="updateOrderStatus(order.id, 'completed')"
              class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
            >
              完成
            </button>
            <button
              v-if="['pending', 'accepted', 'cooking'].includes(order.status)"
              @click="updateOrderStatus(order.id, 'cancelled')"
              class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold ml-auto"
            >
              キャンセル
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { OrderStatus, Shop, Order } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()
const orderStore = useOrderStore()

const { handleLogout } = useAuthCheck()

const allShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const selectedStatus = ref<string>('')
const filteredOrders = ref<Order[]>([])

const getShopName = (shopId: string) => {
  const shop = allShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル'
  }
  return labels[status] || status
}

const getStatusClass = (status: OrderStatus) => {
  const classes: Record<OrderStatus, string> = {
    pending: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium',
    accepted: 'px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium',
    cooking: 'px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium',
    completed: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium',
    cancelled: 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium'
  }
  return classes[status] || 'px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium'
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

const filterOrders = () => {
  let orders = orderStore.orders

  if (selectedShopId.value) {
    orders = orders.filter(o => o.shopId === selectedShopId.value)
  }

  if (selectedStatus.value) {
    orders = orders.filter(o => o.status === selectedStatus.value)
  }

  filteredOrders.value = orders.sort((a, b) => {
    return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
  })
}

const updateOrderStatus = async (orderId: string, status: OrderStatus) => {
  try {
    await orderStore.updateOrderStatus(orderId, status)
    // 更新後に注文一覧を再取得して最新の状態を反映
    const shopIds = allShops.value.map(s => s.id)
    await orderStore.fetchOrders(undefined, undefined, shopIds)
    filterOrders()
  } catch (error: any) {
    console.error('注文ステータスの更新エラー:', error)
    const errorMessage = error?.data?.error || error?.message || 'ステータスの更新に失敗しました'
    alert(errorMessage)
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 全店舗を取得
  try {
    await shopStore.fetchShops()
    allShops.value = shopStore.shops
    
    // 全店舗の注文を取得
    const shopIds = allShops.value.map(s => s.id)
    if (shopIds.length > 0) {
      await orderStore.fetchOrders(undefined, undefined, shopIds)
    }
    filterOrders()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

