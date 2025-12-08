<template>
  <NuxtLayout name="default" title="複数店舗管理ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

      <!-- ヘッダー -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-2xl font-bold mb-2">{{ authStore.user?.name }}さんの管理ダッシュボード</h2>
            <p class="text-gray-600">複数店舗を一元管理</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">管理店舗数</p>
            <p class="text-2xl font-bold text-green-600">{{ myShops.length }}</p>
          </div>
        </div>

        <!-- 店舗切り替え -->
        <div v-if="myShops.length > 1" class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            表示する店舗を選択
          </label>
          <select
            v-model="selectedShopId"
            @change="onShopChange"
            class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
            <option value="">すべての店舗</option>
            <option
              v-for="shop in myShops"
              :key="shop.id"
              :value="shop.id"
            >
              {{ shop.name }} {{ shop.isPrimary ? '(主店舗)' : '' }}
            </option>
          </select>
        </div>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">今日の売上</h3>
          <p class="text-3xl font-bold text-green-600">¥{{ todaySales.toLocaleString() }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ todayOrders }}件の注文</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">受付待ち</h3>
          <p class="text-3xl font-bold text-yellow-600">{{ pendingOrders }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">調理中</h3>
          <p class="text-3xl font-bold text-orange-600">{{ cookingOrders }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">完成</h3>
          <p class="text-3xl font-bold text-blue-600">{{ completedOrders }}</p>
        </div>
      </div>

      <!-- 店舗別統計 -->
      <div v-if="myShops.length > 1" class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">店舗別統計</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="shop in myShops"
            :key="shop.id"
            class="border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition-colors cursor-pointer"
            @click="selectShop(shop)"
          >
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-semibold text-lg">{{ shop.name }}</h4>
              <span v-if="shop.isPrimary" class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                主店舗
              </span>
            </div>
            <p class="text-sm text-gray-600 mb-3">コード: {{ shop.code }}</p>
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">今日の売上:</span>
                <span class="font-semibold">¥{{ getShopSales(shop.id).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">注文数:</span>
                <span class="font-semibold">{{ getShopOrders(shop.id) }}件</span>
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

const navigationItems = computed(() => [
  { to: '/multi-shop/dashboard', label: 'ダッシュボード', isActive: true },
  { to: '/multi-shop/orders', label: '注文一覧', isActive: false },
  { to: '/multi-shop/menus', label: 'メニュー管理', isActive: false },
  { to: '/multi-shop/staff', label: 'スタッフ管理', isActive: false }
])

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

