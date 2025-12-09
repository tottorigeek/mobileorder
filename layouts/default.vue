<template>
  <div class="min-h-screen bg-gray-50">
    <header v-if="showHeader" class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 lg:px-8">
        <!-- スマホの/customerページ用のシンプルなヘッダー -->
        <div v-if="isCustomerPage" class="flex items-center justify-between h-14 sm:h-16 min-w-0">
          <!-- 左側：サービス名（スマホではアイコンのみ） -->
          <NuxtLink to="/" class="flex items-center gap-2 group flex-shrink-0">
            <div class="w-7 h-7 sm:w-8 sm:h-8 md:w-10 md:h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
              <span class="text-white font-bold text-sm sm:text-base md:text-lg">R</span>
            </div>
            <div class="flex flex-col hidden md:flex">
              <span class="text-base md:text-lg font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Radish
              </span>
              <span class="text-xs text-gray-500 -mt-1">ラディッシュ</span>
            </div>
          </NuxtLink>
          
          <!-- 右側：店舗名とテーブル番号（スマホでは縦並び、デスクトップでは横並び） -->
          <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 md:gap-3 px-2 sm:px-3 py-1 sm:py-1.5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg min-w-0 flex-shrink">
            <div v-if="shopStore.currentShop" class="flex items-center gap-1.5 sm:gap-2 min-w-0">
              <svg class="w-4 h-4 sm:w-4 sm:h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span class="text-sm sm:text-sm font-semibold text-blue-700 truncate max-w-[100px] sm:max-w-[150px] md:max-w-none">{{ shopStore.currentShop.name }}</span>
            </div>
            <div v-else class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <svg class="w-4 h-4 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span class="text-sm font-semibold text-orange-700 whitespace-nowrap">店舗未設定</span>
            </div>
            <div v-if="shopStore.currentShop || !displayTableNumber" class="hidden sm:block h-4 w-px bg-blue-300 flex-shrink-0"></div>
            <div v-if="displayTableNumber" class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
              <span class="text-sm font-semibold text-blue-700 whitespace-nowrap">テーブル {{ displayTableNumber }}</span>
            </div>
            <div v-else class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
              <span class="text-sm font-semibold text-orange-700 whitespace-nowrap">テーブル未設定</span>
            </div>
          </div>
        </div>
        
        <!-- 通常のヘッダー（/shop配下など） -->
        <div v-else class="flex items-center h-16 min-w-0 gap-1.5 sm:gap-2">
          <div class="flex items-center gap-1 sm:gap-1.5 md:gap-2 lg:gap-4 min-w-0 flex-shrink overflow-hidden">
            <!-- サービス名 -->
            <NuxtLink to="/" class="flex items-center gap-1 sm:gap-2 group flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                <span class="text-white font-bold text-base sm:text-lg">R</span>
              </div>
              <div class="flex flex-col hidden sm:flex">
                <span class="text-base sm:text-lg font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                  Radish
                </span>
                <span class="text-xs text-gray-500 -mt-1">ラディッシュ</span>
              </div>
            </NuxtLink>
            <!-- ページタイトル -->
            <div class="h-6 sm:h-8 w-px bg-gray-300 hidden sm:block flex-shrink-0"></div>
            <h1 class="text-base sm:text-xl font-semibold text-gray-700 truncate min-w-0">
              {{ title }}
            </h1>
          </div>
          <!-- スペーサー -->
          <div class="flex-1"></div>
          <div class="flex items-center gap-1 sm:gap-1.5 md:gap-2 lg:gap-3 flex-shrink-0 min-w-0">
            <!-- 店舗切替（/shop配下のページで複数店舗に所属している場合のみ） -->
            <div v-if="isShopPage && hasMultipleShops" class="flex items-center gap-2">
              <label for="shop-select" class="text-sm font-medium text-gray-700 whitespace-nowrap">店舗切替</label>
              <select
                id="shop-select"
                v-model="selectedShopId"
                @change="handleShopChange"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="shop in shopStore.shops" :key="shop.id" :value="shop.id">
                  {{ shop.name }}
                </option>
              </select>
            </div>
            <!-- ログインユーザー情報 -->
            <div v-if="authStore.isAuthenticated && authStore.user" class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user.name }}</p>
                <p class="text-xs text-gray-500">{{ getRoleLabel(authStore.user.role) }}</p>
              </div>
              <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="text-blue-600 text-xs sm:text-sm font-semibold">{{ getInitials(authStore.user.name) }}</span>
              </div>
            </div>
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- /shop配下のページでナビゲーションを表示 -->
      <div v-if="isShopPage" class="mb-6">
        <AdminNavigation
          :navigation-items="shopNavigationItems"
          active-color="blue"
        />
      </div>
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
import { useOrderStore } from '~/stores/order'
import AdminNavigation from '~/components/admin/AdminNavigation.vue'

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
const orderStore = useOrderStore()
const route = useRoute()

// /customerページかどうかを判定
const isCustomerPage = computed(() => {
  return route.path.startsWith('/customer')
})

// /shopページかどうかを判定
const isShopPage = computed(() => {
  return route.path.startsWith('/shop')
})

// /shop配下のナビゲーションアイテム
const { navigationItems: shopNavigationItems } = useShopNavigation()

// 表示するテーブル番号（cartStoreまたは注文情報から取得）
const displayTableNumber = computed(() => {
  // cartStoreにテーブル番号がある場合はそれを使用
  if (cartStore.tableNumber) {
    return cartStore.tableNumber
  }
  
  // /customer/order/[id]または/customer/status/[id]ページの場合、注文情報からテーブル番号を取得
  if (route.path.startsWith('/customer/order/') || route.path.startsWith('/customer/status/')) {
    const orderId = route.params.id as string
    const order = orderStore.orders.find(o => o.id === orderId)
    if (order && order.tableNumber) {
      return order.tableNumber
    }
  }
  
  return null
})

// 複数店舗に所属しているかどうかを判定
const hasMultipleShops = computed(() => {
  return shopStore.shops.length > 1
})

// 選択中の店舗ID
const selectedShopId = computed({
  get: () => shopStore.currentShop?.id || '',
  set: (value) => {
    // setterは使用しない（@changeで処理）
  }
})

// 店舗切替処理
const handleShopChange = async (event: Event) => {
  const target = event.target as HTMLSelectElement
  const shopId = target.value
  
  if (!shopId) return
  
  const selectedShop = shopStore.shops.find(s => s.id === shopId)
  if (selectedShop) {
    shopStore.setCurrentShop(selectedShop)
    // ページをリロードして店舗情報を反映
    await navigateTo(route.path)
  }
}

// ページ読み込み時にユーザー情報と店舗情報を読み込む
onMounted(async () => {
  authStore.loadUserFromStorage()
  shopStore.loadShopFromStorage()
  
  // /shop配下のページで、複数店舗に所属している可能性がある場合は店舗一覧を取得
  if (isShopPage.value && authStore.isAuthenticated) {
    await shopStore.fetchMyShops()
    
    // 現在の店舗が設定されていない場合、最初の店舗を設定
    if (!shopStore.currentShop && shopStore.shops.length > 0) {
      const primaryShop = shopStore.shops.find(s => s.isPrimary) || shopStore.shops[0]
      if (primaryShop) {
        shopStore.setCurrentShop(primaryShop)
      }
    }
  }
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

