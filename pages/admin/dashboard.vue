<template>
  <NuxtLayout name="default" title="ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/admin/dashboard"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/admin/users"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          スタッフ管理
        </NuxtLink>
        <NuxtLink
          to="/staff/orders"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          注文管理
        </NuxtLink>
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
        >
          ログアウト
        </button>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">今日の売上</h2>
        <p class="text-4xl font-bold text-blue-600">¥{{ todaySales.toLocaleString() }}</p>
        <p class="text-sm text-gray-500 mt-2">{{ todayOrders }}件の注文</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">受付待ち</h3>
          <p class="text-2xl font-bold">{{ pendingOrders }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">調理中</h3>
          <p class="text-2xl font-bold">{{ cookingOrders }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">完成</h3>
          <p class="text-2xl font-bold">{{ completedOrders }}</p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">最近の注文</h3>
        <div v-if="recentOrders.length === 0" class="text-center py-8 text-gray-500">
          注文がありません
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="order in recentOrders"
            :key="order.id"
            class="border-b pb-3 last:border-0"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold">{{ order.orderNumber }}</p>
                <p class="text-sm text-gray-600">テーブル: {{ order.tableNumber }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(order.createdAt) }}</p>
              </div>
              <div class="text-right">
                <span :class="getStatusClass(order.status)">
                  {{ getStatusLabel(order.status) }}
                </span>
                <p class="text-lg font-bold mt-1">¥{{ order.totalAmount.toLocaleString() }}</p>
              </div>
            </div>
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
import type { OrderStatus } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const orderStore = useOrderStore()

const todaySales = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
      return orderDate === today && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const todayOrders = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return orderStore.orders.filter(order => {
    const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
    return orderDate === today
  }).length
})

const pendingOrders = computed(() => {
  return orderStore.ordersByStatus('pending').length
})

const cookingOrders = computed(() => {
  return orderStore.ordersByStatus('cooking').length
})

const completedOrders = computed(() => {
  return orderStore.ordersByStatus('completed').length
})

const recentOrders = computed(() => {
  return orderStore.orders.slice(0, 5)
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

const formatDate = (date: Date | string) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return d.toLocaleString('ja-JP', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

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

