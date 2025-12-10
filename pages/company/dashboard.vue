<template>
  <NuxtLayout name="company" title="弊社向け管理ダッシュボード">
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="bg-gradient-to-br from-green-600 to-emerald-700 p-8 rounded-2xl shadow-xl text-white">
        <h2 class="text-3xl font-bold mb-2">弊社向け管理ダッシュボード</h2>
        <p class="text-green-100">システム全体の管理を行います</p>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-green-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">登録店舗数</h3>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
          <p class="text-4xl font-bold text-green-600">{{ totalShops }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-blue-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">アクティブ店舗数</h3>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-4xl font-bold text-blue-600">{{ activeShops }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-t-4 border-purple-500">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">総ユーザー数</h3>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <p class="text-4xl font-bold text-purple-600">{{ totalUsers }}</p>
        </div>
      </div>

      <!-- 売上統計カード -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-gradient-to-br from-teal-600 to-cyan-700 p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">直近1時間</h3>
            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-3xl font-bold">¥{{ lastHourSales.toLocaleString() }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-600 to-emerald-700 p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">本日の売上</h3>
            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-3xl font-bold">¥{{ todaySales.toLocaleString() }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">昨日の売上</h3>
            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-3xl font-bold">¥{{ yesterdaySales.toLocaleString() }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-600 to-pink-700 p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">7日間の売上</h3>
            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <p class="text-3xl font-bold">¥{{ sevenDaysSales.toLocaleString() }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-600 to-red-700 p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium opacity-90">30日間の売上</h3>
            <svg class="w-6 h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <p class="text-3xl font-bold">¥{{ thirtyDaysSales.toLocaleString() }}</p>
        </div>
      </div>

      <!-- 最近の店舗 -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">最近登録された店舗</h3>
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div v-if="recentShops.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <p class="text-gray-500">店舗が登録されていません</p>
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="shop in recentShops"
            :key="shop.id"
            class="p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all duration-200"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <p class="font-semibold text-gray-900 text-lg">{{ shop.name }}</p>
                  <span :class="shop.isActive ? 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium' : 'px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium'">
                    {{ shop.isActive ? 'アクティブ' : '無効' }}
                  </span>
                </div>
                <div class="space-y-1 text-sm text-gray-600">
                  <p class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    コード: {{ shop.code }}
                  </p>
                  <p v-if="shop.address" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ shop.address }}
                  </p>
                  <p v-if="shop.owner" class="flex items-center gap-2 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium">オーナー:</span> {{ shop.owner.name }}
                    <span v-if="shop.owner.email" class="text-gray-500">({{ shop.owner.email }})</span>
                  </p>
                  <p v-else class="text-gray-400 mt-2">オーナー未設定</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- クイックアクション -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-xl font-bold mb-6 text-gray-900">クイックアクション</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <NuxtLink
            to="/company/shops"
            class="group p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-gradient-to-br hover:from-green-50 hover:to-emerald-50 transition-all duration-300"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">店舗を追加</h4>
                <p class="text-sm text-gray-600">新しい店舗を登録します</p>
              </div>
            </div>
          </NuxtLink>
          <NuxtLink
            to="/company/users"
            class="group p-6 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-gradient-to-br hover:from-green-50 hover:to-emerald-50 transition-all duration-300"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">ユーザーを管理</h4>
                <p class="text-sm text-gray-600">システム全体のユーザーを管理します</p>
              </div>
            </div>
          </NuxtLink>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import { useOrderStore } from '~/stores/order'

const authStore = useAuthStore()
const shopStore = useShopStore()
const orderStore = useOrderStore()

const totalShops = ref(0)
const activeShops = ref(0)
const totalUsers = ref(0)
const recentShops = ref([])

// 売上計算
const lastHourSales = computed(() => {
  const oneHourAgo = new Date()
  oneHourAgo.setHours(oneHourAgo.getHours() - 1)
  const now = new Date()
  
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= oneHourAgo && orderDate <= now && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const todaySales = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= today && orderDate < tomorrow && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const yesterdaySales = computed(() => {
  const yesterday = new Date()
  yesterday.setDate(yesterday.getDate() - 1)
  yesterday.setHours(0, 0, 0, 0)
  const today = new Date(yesterday)
  today.setDate(today.getDate() + 1)
  
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= yesterday && orderDate < today && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const sevenDaysSales = computed(() => {
  const sevenDaysAgo = new Date()
  sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7)
  sevenDaysAgo.setHours(0, 0, 0, 0)
  const tomorrow = new Date()
  tomorrow.setHours(0, 0, 0, 0)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= sevenDaysAgo && orderDate < tomorrow && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

const thirtyDaysSales = computed(() => {
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)
  thirtyDaysAgo.setHours(0, 0, 0, 0)
  const tomorrow = new Date()
  tomorrow.setHours(0, 0, 0, 0)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  return orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= thirtyDaysAgo && orderDate < tomorrow && order.status === 'completed'
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
})

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 店舗一覧を取得
  try {
    await shopStore.fetchShops()
    totalShops.value = shopStore.shops.length
    activeShops.value = shopStore.shops.filter(shop => shop.isActive).length
    recentShops.value = shopStore.shops.slice(0, 5)
    
    // 全店舗の注文を取得（売上計算用）
    const shopIds = shopStore.shops.map(s => s.id)
    if (shopIds.length > 0) {
      await orderStore.fetchOrders(undefined, undefined, shopIds)
    }
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }

  // TODO: ユーザー数の取得（API実装が必要）
  totalUsers.value = 0
})
</script>

