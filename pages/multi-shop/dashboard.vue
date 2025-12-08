<template>
  <NuxtLayout name="default" title="複数店舗管理ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

      <!-- ヘッダー -->
      <div class="bg-gradient-to-br from-green-600 to-emerald-700 p-8 rounded-2xl shadow-xl text-white">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-3xl font-bold mb-2">{{ authStore.user?.name }}さんの管理ダッシュボード</h2>
            <p class="text-green-100">複数店舗を一元管理</p>
          </div>
          <div class="text-right bg-white/20 backdrop-blur-sm rounded-xl px-6 py-4">
            <p class="text-sm text-green-100 font-medium">管理店舗数</p>
            <p class="text-4xl font-bold">{{ myShops.length }}</p>
          </div>
        </div>

        <!-- 店舗切り替え -->
        <div v-if="myShops.length > 1" class="mt-6 bg-white/10 backdrop-blur-sm rounded-xl p-4">
          <label class="block text-sm font-semibold text-white mb-2">
            表示する店舗を選択
          </label>
          <select
            v-model="selectedShopId"
            @change="onShopChange"
            class="w-full md:w-auto px-4 py-2.5 bg-white/20 backdrop-blur-sm border-2 border-white/30 rounded-xl text-white focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all"
          >
            <option value="" class="text-gray-900">すべての店舗</option>
            <option
              v-for="shop in myShops"
              :key="shop.id"
              :value="shop.id"
              class="text-gray-900"
            >
              {{ shop.name }} {{ shop.isPrimary ? '(主店舗)' : '' }}
            </option>
          </select>
        </div>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-green-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">今日の売上</h3>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-green-600 mb-1">¥{{ todaySales.toLocaleString() }}</p>
          <p class="text-xs text-gray-500">{{ todayOrders }}件の注文</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-yellow-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">受付待ち</h3>
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-yellow-600">{{ pendingOrders }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-orange-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">調理中</h3>
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-orange-600">{{ cookingOrders }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-blue-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">完成</h3>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-blue-600">{{ completedOrders }}</p>
        </div>
      </div>

      <!-- 店舗別統計 -->
      <div v-if="myShops.length > 1" class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">店舗別統計</h3>
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="shop in myShops"
            :key="shop.id"
            class="border-2 border-gray-200 rounded-xl p-5 hover:border-green-500 hover:bg-green-50 transition-all duration-300 cursor-pointer transform hover:-translate-y-1"
            @click="selectShop(shop)"
          >
            <div class="flex justify-between items-start mb-3">
              <h4 class="font-bold text-lg text-gray-900">{{ shop.name }}</h4>
              <span v-if="shop.isPrimary" class="px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full text-xs font-semibold shadow-md">
                主店舗
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-4 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              コード: {{ shop.code }}
            </p>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                <span class="text-gray-600 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  今日の売上
                </span>
                <span class="font-bold text-green-600">¥{{ getShopSales(shop.id).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                <span class="text-gray-600 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  注文数
                </span>
                <span class="font-bold text-blue-600">{{ getShopOrders(shop.id) }}件</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 最近の注文 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">最近の注文</h3>
          <NuxtLink
            to="/multi-shop/orders"
            class="text-green-600 hover:text-green-700 text-sm font-medium"
          >
            すべて見る →
          </NuxtLink>
        </div>
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
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <p class="font-semibold">{{ order.orderNumber }}</p>
                  <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                    {{ getShopName(order.shopId) }}
                  </span>
                </div>
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
import type { OrderStatus, Shop, Order } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()
const orderStore = useOrderStore()
const { handleLogout, checkAuthMultiShop } = useAuthCheck()
const { getStatusLabel, getStatusClass, formatDate } = useAdminUtils()

const myShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const filteredOrders = ref<Order[]>([])

const { navigationItems } = useMultiShopNavigation()

const selectShop = (shop: Shop) => {
  shopStore.setCurrentShop(shop)
  selectedShopId.value = shop.id
  filterOrders()
}

const onShopChange = () => {
  if (selectedShopId.value) {
    const shop = myShops.value.find(s => s.id === selectedShopId.value)
    if (shop) {
      shopStore.setCurrentShop(shop)
    }
  } else {
    shopStore.setCurrentShop(null)
  }
  filterOrders()
}

const filterOrders = () => {
  if (selectedShopId.value) {
    filteredOrders.value = orderStore.orders.filter(o => o.shopId === selectedShopId.value)
  } else {
    filteredOrders.value = orderStore.orders
  }
}

const getShopName = (shopId: string) => {
  const shop = myShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const getShopSales = (shopId: string) => {
  const today = new Date().toISOString().split('T')[0]
  return filteredOrders.value
    .filter(order => {
      const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
      return order.shopId === shopId && orderDate === today && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
}

const getShopOrders = (shopId: string) => {
  const today = new Date().toISOString().split('T')[0]
  return filteredOrders.value.filter(order => {
    const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
    return order.shopId === shopId && orderDate === today
  }).length
}

const todaySales = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return filteredOrders.value
    .filter(order => {
      const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
      return orderDate === today && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const todayOrders = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return filteredOrders.value.filter(order => {
    const orderDate = new Date(order.createdAt).toISOString().split('T')[0]
    return orderDate === today
  }).length
})

const pendingOrders = computed(() => {
  return filteredOrders.value.filter(o => o.status === 'pending').length
})

const cookingOrders = computed(() => {
  return filteredOrders.value.filter(o => o.status === 'cooking').length
})

const completedOrders = computed(() => {
  return filteredOrders.value.filter(o => o.status === 'completed').length
})

const recentOrders = computed(() => {
  return filteredOrders.value.slice(0, 10)
})


onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuthMultiShop()
  if (!isAuthenticated) {
    return
  }

  // 所属店舗一覧を取得
  try {
    myShops.value = await shopStore.fetchMyShops()
    
    if (myShops.value.length === 0) {
      // 所属店舗がない場合は通常の管理画面へ
      await navigateTo('/shop/dashboard')
      return
    }

    // 主店舗を自動選択
    const primaryShop = myShops.value.find(s => s.isPrimary) || myShops.value[0]
    if (primaryShop) {
      shopStore.setCurrentShop(primaryShop)
      selectedShopId.value = primaryShop.id
    }

    // 注文一覧を取得（全店舗分）
    const shopIds = myShops.value.map(s => s.id)
    await orderStore.fetchOrders(undefined, undefined, shopIds)
    filterOrders()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

