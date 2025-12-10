<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 管理画面用ヘッダー -->
    <header v-if="showHeader && !isShopSelectPage" class="header-navigation bg-white shadow-sm border-b border-gray-200">
      <div class="header-container max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <div class="default-header flex items-center h-16 min-w-0 gap-2 sm:gap-2">
          <div class="header-left flex items-center gap-1 sm:gap-1.5 md:gap-2 lg:gap-4 min-w-0 flex-shrink overflow-hidden">
            <!-- サービス名 -->
            <NuxtLink to="/" class="header-logo-link flex items-center gap-1 sm:gap-2 group flex-shrink-0">
              <div class="header-logo-icon w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                <span class="header-logo-text text-white font-bold text-base sm:text-lg">R</span>
              </div>
              <div class="header-logo-text-container flex flex-col hidden sm:flex">
                <span class="header-logo-name text-base sm:text-lg font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                  Radish
                </span>
                <span class="header-logo-subtitle text-xs text-gray-500 -mt-1">ラディッシュ</span>
              </div>
            </NuxtLink>
            <!-- ページタイトル -->
            <div class="header-title-separator h-6 sm:h-8 w-px bg-gray-300 hidden sm:block flex-shrink-0"></div>
            <h1 class="header-title text-sm sm:text-base md:text-xl font-semibold text-gray-700 truncate min-w-0">
              {{ title }}
            </h1>
          </div>
          <!-- スペーサー -->
          <div class="header-spacer flex-1 min-w-0"></div>
          <div class="header-right flex items-center gap-1 sm:gap-1.5 md:gap-2 lg:gap-3 flex-shrink-0 min-w-0 overflow-hidden">
            <!-- 店舗切替（/shop配下のページで複数店舗に所属している場合のみ） -->
            <div v-if="isShopPage && hasMultipleShops" class="shop-selector-container flex items-center gap-2">
              <label for="shop-select" class="shop-selector-label text-sm font-medium text-gray-700 whitespace-nowrap">店舗切替</label>
              <select
                id="shop-select"
                v-model="selectedShopId"
                @change="handleShopChange"
                class="shop-selector-select px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="shop in shopStore.shops" :key="shop.id" :value="shop.id">
                  {{ shop.name }}
                </option>
              </select>
            </div>
            <!-- ログインユーザー情報 -->
            <div v-if="authStore.isAuthenticated && authStore.user" class="user-info-container flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <!-- /staff 配下のページでは AccountMenu を表示 -->
              <AccountMenu
                v-if="isStaffPage"
                password-change-path="/staff/password"
              />
              <!-- その他のページでは従来の表示 -->
              <div v-else class="flex items-center gap-1.5 sm:gap-2">
                <div class="user-info-text text-right hidden sm:block">
                  <p class="user-name text-sm font-medium text-gray-900">{{ authStore.user.name }}</p>
                  <p class="user-role text-xs text-gray-500">{{ getRoleLabel(authStore.user.role) }}</p>
                </div>
                <div class="user-avatar w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <span class="user-initials text-blue-600 text-xs sm:text-sm font-semibold">{{ getInitials(authStore.user.name) }}</span>
                </div>
              </div>
            </div>
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </header>

    <main :class="isShopSelectPage ? '' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6'">
      <!-- /shop配下のページでナビゲーションを表示 -->
      <div v-if="isShopPage && !isShopSelectPage" class="mb-6">
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
import AdminNavigation from '~/components/admin/AdminNavigation.vue'
import AccountMenu from '~/components/admin/AccountMenu.vue'
import type { Shop } from '~/types/multi-shop'

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
const route = useRoute()

// /shopページかどうかを判定
const isShopPage = computed(() => {
  return route.path.startsWith('/shop')
})

// /staffページかどうかを判定
const isStaffPage = computed(() => {
  return route.path.startsWith('/staff') && !route.path.startsWith('/staff/login') && !route.path.startsWith('/staff/reset-password') && !route.path.startsWith('/staff/forgot-password')
})

// /shop-selectページかどうかを判定
const isShopSelectPage = computed(() => {
  return route.path === '/shop-select'
})

// /shop配下のナビゲーションアイテム
const { navigationItems: shopNavigationItems } = useShopNavigation()

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
  
  const selectedShop = shopStore.shops.find((s: Shop) => s.id === shopId)
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
      const primaryShop = shopStore.shops.find((s: Shop) => s.isPrimary) || shopStore.shops[0]
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

