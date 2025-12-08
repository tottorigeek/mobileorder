<template>
  <div class="min-h-screen bg-gray-50">
    <header v-if="showHeader" class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-4">
            <!-- サービス名 -->
            <NuxtLink to="/" class="flex items-center gap-2 group">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                <span class="text-white font-bold text-lg">R</span>
              </div>
              <div class="flex flex-col">
                <span class="text-lg font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                  Radish
                </span>
                <span class="text-xs text-gray-500 -mt-1">ラディッシュ</span>
              </div>
            </NuxtLink>
            <!-- ページタイトル -->
            <div class="h-8 w-px bg-gray-300"></div>
            <h1 class="text-xl font-semibold text-gray-700">
              {{ title }}
            </h1>
          </div>
          <div class="flex items-center gap-4">
            <!-- デバッグ用：店舗名とテーブル番号表示（/customerページのみ） -->
            <div v-if="isCustomerPage" class="flex items-center gap-3 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
              <div v-if="shopStore.currentShop" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium text-blue-700">{{ shopStore.currentShop.name }}</span>
              </div>
              <div v-else class="flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium text-orange-700">店舗未設定</span>
              </div>
              <div v-if="shopStore.currentShop || !cartStore.tableNumber" class="h-4 w-px bg-blue-300"></div>
              <div v-if="cartStore.tableNumber" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="text-sm font-medium text-blue-700">テーブル {{ cartStore.tableNumber }}</span>
              </div>
              <div v-else class="flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="text-sm font-medium text-orange-700">テーブル未設定</span>
              </div>
            </div>
            <!-- ログインユーザー情報 -->
            <div v-if="authStore.isAuthenticated && authStore.user" class="flex items-center gap-2">
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user.name }}</p>
                <p class="text-xs text-gray-500">{{ getRoleLabel(authStore.user.role) }}</p>
              </div>
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="text-blue-600 text-sm font-semibold">{{ getInitials(authStore.user.name) }}</span>
              </div>
            </div>
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>

    <footer v-if="showFooter" class="bg-white border-t mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <slot name="footer" />
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import { useCartStore } from '~/stores/cart'

interface Props {
  title?: string
  showHeader?: boolean
  showFooter?: boolean
}

withDefaults(defineProps<Props>(), {
  title: 'Radish',
  showHeader: true,
  showFooter: false
})

const authStore = useAuthStore()
const shopStore = useShopStore()
const cartStore = useCartStore()
const route = useRoute()

// /customerページかどうかを判定
const isCustomerPage = computed(() => {
  return route.path.startsWith('/customer')
})

// ページ読み込み時にユーザー情報と店舗情報を読み込む
onMounted(() => {
  authStore.loadUserFromStorage()
  shopStore.loadShopFromStorage()
})

const getRoleLabel = (role: string) => {
  const labels: Record<string, string> = {
    owner: 'オーナー',
    manager: '管理者',
    staff: 'スタッフ'
  }
  return labels[role] || role
}

const getInitials = (name: string) => {
  if (!name) return '?'
  // 日本語の場合は最初の1文字、英語の場合は最初の2文字
  const firstChar = name.charAt(0)
  if (firstChar.match(/[a-zA-Z]/)) {
    return name.substring(0, 2).toUpperCase()
  }
  return firstChar
}
</script>

