<template>
  <NuxtLayout name="default" title="注文管理">
    <div class="space-y-4">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />
      <!-- ステータスフィルター -->
      <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button
          v-for="status in statuses"
          :key="status.value"
          :class="[
            'px-5 py-2.5 rounded-xl font-semibold whitespace-nowrap transition-all duration-300 touch-target',
            selectedStatus === status.value
              ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg shadow-purple-500/30'
              : 'bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200'
          ]"
          @click="selectedStatus = status.value"
        >
          {{ status.label }}
        </button>
      </div>

      <!-- 注文一覧 -->
      <div v-if="orderStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-purple-200 border-t-purple-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-purple-300"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-bold text-gray-900">注文番号: {{ order.orderNumber }}</h3>
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
                  {{ new Date(order.createdAt).toLocaleString('ja-JP') }}
                </span>
              </div>
            </div>
          </div>

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
              </li>
            </ul>
          </div>

          <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <span class="text-2xl font-bold text-purple-600">合計: ¥{{ order.totalAmount.toLocaleString() }}</span>
            <div class="flex gap-2">
              <button
                v-if="order.status === 'pending'"
                @click="updateStatus(order.id, 'accepted')"
                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold"
              >
                受付
              </button>
              <button
                v-if="order.status === 'accepted'"
                @click="updateStatus(order.id, 'cooking')"
                class="px-5 py-2.5 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold"
              >
                調理開始
              </button>
              <button
                v-if="order.status === 'cooking'"
                @click="updateStatus(order.id, 'completed')"
                class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold"
              >
                完成
              </button>
            </div>
          </div>
        </div>

        <div v-if="filteredOrders.length === 0" class="text-center py-16 bg-white rounded-2xl shadow-lg">
          <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <p class="text-gray-500 font-medium text-lg">注文がありません</p>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { OrderStatus } from '~/types'

const { navigationItems } = useShopNavigation()

const orderStore = useOrderStore()
const selectedStatus = ref<OrderStatus | 'all'>('all')

const statuses = [
  { value: 'all', label: 'すべて' },
  { value: 'pending', label: '受付待ち' },
  { value: 'accepted', label: '受付済み' },
  { value: 'cooking', label: '調理中' },
  { value: 'completed', label: '完成' }
]

const filteredOrders = computed(() => {
  if (selectedStatus.value === 'all') {
    return orderStore.orders
  }
  return orderStore.ordersByStatus(selectedStatus.value as OrderStatus)
})

// ステータス変更時に再取得
watch(selectedStatus, async (newStatus) => {
  await orderStore.fetchOrders(newStatus === 'all' ? undefined : newStatus as OrderStatus)
})

const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル'
  }
  return labels[status]
}

const getStatusClass = (status: OrderStatus) => {
  const classes: Record<OrderStatus, string> = {
    pending: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm',
    accepted: 'px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm',
    cooking: 'px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm',
    completed: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm',
    cancelled: 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm'
  }
  return classes[status]
}

const updateStatus = async (orderId: string, status: OrderStatus) => {
  await orderStore.updateOrderStatus(orderId, status)
  // 更新後に再取得
  await orderStore.fetchOrders(selectedStatus.value === 'all' ? undefined : selectedStatus.value as OrderStatus)
}

const authStore = useAuthStore()
const shopStore = useShopStore()

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }
  
  // 店舗情報の読み込み
  shopStore.loadShopFromStorage()
  
  // 店舗が選択されていない場合は店舗選択画面にリダイレクト
  if (!shopStore.currentShop) {
    if (authStore.user?.shop) {
      shopStore.setCurrentShop(authStore.user.shop)
    } else {
      await navigateTo('/staff/shop-select')
      return
    }
  }
  
  await orderStore.fetchOrders()
})
</script>

