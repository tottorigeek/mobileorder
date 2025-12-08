<template>
  <NuxtLayout name="default" title="ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />
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
const { handleLogout, checkAuth } = useAuthCheck()
const { getStatusLabel, getStatusClass, formatDate } = useAdminUtils()

const navigationItems = computed(() => [
  { to: '/shop/dashboard', label: 'ダッシュボード', isActive: true },
  { to: '/shop/users', label: 'スタッフ管理', isActive: false },
  { to: '/staff/tables', label: 'テーブル設定', isActive: false },
  { to: '/staff/orders', label: '注文管理', isActive: false }
])

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

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }
  
  await orderStore.fetchOrders()
})
</script>

