<template>
  <NuxtLayout name="default" title="弊社向け管理ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

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

const authStore = useAuthStore()
const shopStore = useShopStore()
const { navigationItems } = useCompanyNavigation()
const { handleLogout } = useAuthCheck()

const totalShops = ref(0)
const activeShops = ref(0)
const totalUsers = ref(0)
const recentShops = ref([])

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
  } catch (error) {
    console.error('店舗情報の取得に失敗しました:', error)
  }

  // TODO: ユーザー数の取得（API実装が必要）
  totalUsers.value = 0
})
</script>

