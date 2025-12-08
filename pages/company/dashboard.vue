<template>
  <NuxtLayout name="default" title="弊社向け管理ダッシュボード">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

      <!-- ヘッダー -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-2">弊社向け管理ダッシュボード</h2>
        <p class="text-gray-600">システム全体の管理を行います</p>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">登録店舗数</h3>
          <p class="text-3xl font-bold text-green-600">{{ totalShops }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">アクティブ店舗数</h3>
          <p class="text-3xl font-bold text-blue-600">{{ activeShops }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 mb-2">総ユーザー数</h3>
          <p class="text-3xl font-bold text-purple-600">{{ totalUsers }}</p>
        </div>
      </div>

      <!-- 最近の店舗 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">最近登録された店舗</h3>
        <div v-if="recentShops.length === 0" class="text-center py-8 text-gray-500">
          店舗が登録されていません
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="shop in recentShops"
            :key="shop.id"
            class="border-b pb-3 last:border-0"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <p class="font-semibold">{{ shop.name }}</p>
                <p class="text-sm text-gray-600">コード: {{ shop.code }}</p>
                <p v-if="shop.address" class="text-sm text-gray-500">{{ shop.address }}</p>
                <p v-if="shop.owner" class="text-sm text-gray-600 mt-1">
                  <span class="font-medium">オーナー:</span> {{ shop.owner.name }}
                  <span v-if="shop.owner.email" class="text-gray-500">({{ shop.owner.email }})</span>
                </p>
                <p v-else class="text-sm text-gray-400 mt-1">オーナー未設定</p>
              </div>
              <div class="text-right ml-4">
                <span :class="shop.isActive ? 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm' : 'px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm'">
                  {{ shop.isActive ? 'アクティブ' : '無効' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- クイックアクション -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">クイックアクション</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <NuxtLink
            to="/company/shops"
            class="p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors"
          >
            <h4 class="font-semibold mb-2">店舗を追加</h4>
            <p class="text-sm text-gray-600">新しい店舗を登録します</p>
          </NuxtLink>
          <NuxtLink
            to="/company/users"
            class="p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors"
          >
            <h4 class="font-semibold mb-2">ユーザーを管理</h4>
            <p class="text-sm text-gray-600">システム全体のユーザーを管理します</p>
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

