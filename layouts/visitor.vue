<template>
  <div class="min-h-screen bg-gray-50">
    <!-- visitor用ヘッダー -->
    <header v-if="showHeader" class="header-navigation bg-white shadow-sm border-b border-gray-200">
      <div class="header-container max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <div class="customer-header relative h-14 sm:h-16">
          <!-- 左側：サービス名（スマホではアイコンのみ） -->
          <div class="header-logo-wrapper absolute left-0 top-1/2 -translate-y-1/2 pl-0 pr-2 sm:pr-3">
            <NuxtLink to="/" class="header-logo-link inline-block group">
              <div class="header-logo-icon w-7 h-7 sm:w-8 sm:h-8 md:w-10 md:h-10 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-lg sm:rounded-xl inline-flex items-center justify-center shadow-md group-hover:shadow-lg transition-all">
                <span class="header-logo-text text-white font-bold text-xs sm:text-sm md:text-base lg:text-lg">R</span>
              </div>
              <div class="header-logo-text-container hidden md:inline-block md:ml-2">
                <span class="header-logo-name text-sm md:text-base lg:text-lg font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                  Radish
                </span>
                <span class="header-logo-subtitle block text-[10px] md:text-xs text-gray-500 -mt-0.5">ラディッシュ</span>
              </div>
            </NuxtLink>
          </div>
          
          <!-- 右側：店舗名とテーブル番号（スマホでは縦並び、デスクトップでは横並び） -->
          <div class="customer-info-container absolute right-0 top-1/2 -translate-y-1/2 px-2 sm:px-2.5 md:px-3 lg:px-4 py-1 sm:py-1.5 md:py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg max-w-[calc(100%-3.5rem)] sm:max-w-[calc(100%-4rem)] md:max-w-none">
            <!-- スマホ：縦並び表示 -->
            <div class="sm:hidden">
              <div v-if="shopStore.currentShop" class="shop-name-container inline-flex items-center gap-1">
                <svg class="shop-icon w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="shop-name text-[11px] font-semibold text-blue-700 leading-tight">{{ shopStore.currentShop.name }}</span>
              </div>
              <div v-else class="shop-name-empty inline-flex items-center gap-1">
                <svg class="shop-icon-empty w-3.5 h-3.5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="shop-name-empty-text text-[11px] font-semibold text-orange-700 leading-tight">店舗未設定</span>
              </div>
              <div class="block mt-0.5">
                <div v-if="displayTableNumber" class="table-number-container inline-flex items-center gap-1">
                  <svg class="table-icon w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  <span class="table-number text-[11px] font-semibold text-blue-700 leading-tight">テーブル {{ displayTableNumber }}</span>
                </div>
                <div v-else class="table-number-empty inline-flex items-center gap-1">
                  <svg class="table-icon-empty w-3.5 h-3.5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  <span class="table-number-empty-text text-[11px] font-semibold text-orange-700 leading-tight">テーブル未設定</span>
                </div>
              </div>
            </div>
            
            <!-- タブレット・デスクトップ：横並び表示 -->
            <div class="hidden sm:inline-block">
              <span v-if="shopStore.currentShop" class="shop-name-container inline-flex items-center gap-1.5 md:gap-2 lg:gap-2.5">
                <svg class="shop-icon w-4 h-4 md:w-4 md:h-4 lg:w-5 lg:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="shop-name text-xs sm:text-sm md:text-base font-semibold text-blue-700">{{ shopStore.currentShop.name }}</span>
              </span>
              <span v-else class="shop-name-empty inline-flex items-center gap-1.5 md:gap-2 lg:gap-2.5">
                <svg class="shop-icon-empty w-4 h-4 md:w-4 md:h-4 lg:w-5 lg:h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="shop-name-empty-text text-xs sm:text-sm md:text-base font-semibold text-orange-700 whitespace-nowrap">店舗未設定</span>
              </span>
              <span v-if="shopStore.currentShop || !displayTableNumber" class="info-separator inline-block h-4 md:h-5 w-px bg-blue-300 mx-2 md:mx-2.5 flex-shrink-0"></span>
              <span v-if="displayTableNumber" class="table-number-container inline-flex items-center gap-1.5 md:gap-2 lg:gap-2.5">
                <svg class="table-icon w-4 h-4 md:w-4 md:h-4 lg:w-5 lg:h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="table-number text-xs sm:text-sm md:text-base font-semibold text-blue-700 whitespace-nowrap">テーブル {{ displayTableNumber }}</span>
              </span>
              <span v-else class="table-number-empty inline-flex items-center gap-1.5 md:gap-2 lg:gap-2.5">
                <svg class="table-icon-empty w-4 h-4 md:w-4 md:h-4 lg:w-5 lg:h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="table-number-empty-text text-xs sm:text-sm md:text-base font-semibold text-orange-700 whitespace-nowrap">テーブル未設定</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>

    <!-- Bottom Navigation -->
    <CustomerBottomNav />
  </div>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useCartStore } from '~/stores/cart'
import { useOrderStore } from '~/stores/order'
import CustomerBottomNav from '~/components/CustomerBottomNav.vue'

interface Props {
  title?: string
  showHeader?: boolean
}

withDefaults(defineProps<Props>(), {
  title: 'Radish',
  showHeader: true
})

const shopStore = useShopStore()
const cartStore = useCartStore()
const orderStore = useOrderStore()
const route = useRoute()

// 表示するテーブル番号（cartStoreまたは注文情報から取得）
const displayTableNumber = computed(() => {
  // cartStoreにテーブル番号がある場合はそれを使用
  if (cartStore.tableNumber) {
    return cartStore.tableNumber
  }
  
  // /visitor/order/[id]または/visitor/status/[id]ページの場合、注文情報からテーブル番号を取得
  if (route.path.startsWith('/visitor/order/') || route.path.startsWith('/visitor/status/')) {
    const orderId = route.params.id as string
    const order = orderStore.orders.find(o => o.id === orderId)
    if (order && order.tableNumber) {
      return order.tableNumber
    }
  }
  
  return null
})

// ページ読み込み時に店舗情報を読み込む
onMounted(() => {
  shopStore.loadShopFromStorage()
})
</script>

