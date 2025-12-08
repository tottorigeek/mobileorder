<template>
  <NuxtLayout name="default" title="店舗選択" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gray-50">
      <div class="w-full max-w-md">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-center mb-2 text-gray-900">
            {{ authStore.user?.name }}さん
          </h1>
          <p class="text-center text-gray-600 text-sm">店舗を選択してください</p>
        </div>

        <div v-if="shopStore.isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p class="mt-4 text-gray-500">読み込み中...</p>
        </div>

        <div v-else-if="availableShops.length === 0" class="text-center py-12">
          <p class="text-gray-500 mb-4">利用可能な店舗が見つかりません</p>
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
          >
            ログアウト
          </button>
        </div>

        <div v-else class="space-y-3">
          <button
            v-for="shop in availableShops"
            :key="shop.id"
            @click="selectShop(shop)"
            :class="[
              'w-full p-6 rounded-lg shadow transition-shadow text-left touch-target',
              currentShop?.id === shop.id
                ? 'bg-blue-100 border-2 border-blue-500'
                : 'bg-white hover:shadow-lg'
            ]"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ shop.name }}</h3>
                <p v-if="shop.description" class="text-gray-600 text-sm mb-2">{{ shop.description }}</p>
                <p v-if="shop.address" class="text-gray-500 text-xs">{{ shop.address }}</p>
              </div>
              <div v-if="currentShop?.id === shop.id" class="ml-4">
                <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-sm">選択中</span>
              </div>
            </div>
          </button>
        </div>

        <div v-if="currentShop" class="mt-6">
          <button
            @click="goToDashboard"
            class="w-full bg-blue-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
          >
            ダッシュボードへ進む
          </button>
        </div>

        <div class="mt-4 text-center">
          <button
            @click="handleLogout"
            class="text-gray-600 hover:text-gray-700 text-sm"
          >
            ログアウト
          </button>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Shop } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()

const currentShop = computed(() => shopStore.currentShop)

// 利用可能な店舗（現在はログインユーザーの所属店舗のみ）
const availableShops = computed(() => {
  if (!authStore.user?.shop) {
    return []
  }
  // 現在は1店舗のみ所属だが、将来的に複数店舗対応可能
  return [authStore.user.shop]
})

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }

  // 店舗情報の読み込み
  shopStore.loadShopFromStorage()
  
  // ログインユーザーの所属店舗を自動設定
  if (authStore.user?.shop && !shopStore.currentShop) {
    shopStore.setCurrentShop(authStore.user.shop)
  }

  // 店舗一覧を取得（将来的に複数店舗対応する場合）
  await shopStore.fetchShops()
})

const selectShop = (shop: Shop) => {
  shopStore.setCurrentShop(shop)
}

const goToDashboard = async () => {
  if (!shopStore.currentShop) {
    alert('店舗を選択してください')
    return
  }
  await navigateTo('/admin/dashboard')
}

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}
</script>

