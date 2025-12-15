<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <div class="flex items-center h-16 min-w-0 gap-2 sm:gap-2">
          <div class="flex items-center gap-2 min-w-0 flex-shrink overflow-hidden">
            <NuxtLink to="/" class="flex items-center gap-2 group flex-shrink-0">
              <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-emerald-600 via-green-600 to-teal-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                <span class="text-white font-bold text-base sm:text-lg">R</span>
              </div>
              <div class="flex flex-col hidden sm:flex">
                <span class="text-base sm:text-lg font-bold bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 bg-clip-text text-transparent">
                  Radish Owner
                </span>
                <span class="text-xs text-gray-500 -mt-1">店舗オーナー</span>
              </div>
            </NuxtLink>
            <div class="h-6 sm:h-8 w-px bg-gray-300 hidden sm:block flex-shrink-0"></div>
            <h1 class="text-sm sm:text-base md:text-xl font-semibold text-gray-700 truncate min-w-0">
              {{ title }}
            </h1>
          </div>
          <div class="flex-1 min-w-0"></div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <NuxtLink
              to="/owner/select-shop"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 text-sm font-medium shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
              </svg>
              店舗切替
            </NuxtLink>
            <AccountMenu password-change-path="/company/password" />
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="mb-6">
        <AdminNavigation :navigation-items="navigationItems" active-color="green" />
      </div>
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import AdminNavigation from '~/components/admin/AdminNavigation.vue'
import AccountMenu from '~/components/admin/AccountMenu.vue'
import { useOwnerNavigation } from '~/composables/useOwnerNavigation'
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'

interface Props {
  title?: string
}

withDefaults(defineProps<Props>(), {
  title: 'オーナー管理'
})

const { navigationItems } = useOwnerNavigation()
const shopStore = useShopStore()
const authStore = useAuthStore()

onMounted(async () => {
  authStore.loadUserFromStorage()

  // 店舗が未選択の場合は選択へ誘導
  shopStore.loadShopFromStorage()
  if (!shopStore.currentShop) {
    await shopStore.fetchShops()
    if (shopStore.shops.length > 1) {
      navigateTo('/owner/select-shop')
    } else if (shopStore.shops.length === 1) {
      shopStore.setCurrentShop(shopStore.shops[0])
    }
  }
})
</script>


