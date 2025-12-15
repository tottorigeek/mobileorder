<template>
  <div class="min-h-screen bg-gray-50">
    <!-- company管理画面用ヘッダー -->
    <header v-if="showHeader" class="header-navigation bg-white shadow-sm border-b border-gray-200">
      <div class="header-container max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <div class="default-header flex items-center h-16 min-w-0 gap-2 sm:gap-2">
          <div class="header-left flex items-center gap-1 sm:gap-1.5 md:gap-2 lg:gap-4 min-w-0 flex-shrink overflow-hidden">
            <!-- サービス名 -->
            <NuxtLink to="/" class="header-logo-link flex items-center gap-1 sm:gap-2 group flex-shrink-0">
              <div class="header-logo-icon w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all">
                <span class="header-logo-text text-white font-bold text-base sm:text-lg">R</span>
              </div>
              <div class="header-logo-text-container flex flex-col hidden sm:flex">
                <span class="header-logo-name text-base sm:text-lg font-bold bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 bg-clip-text text-transparent">
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
            <!-- ログインユーザー情報 -->
            <div v-if="authStore.isAuthenticated && authStore.user" class="user-info-container flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
              <AccountMenu
                password-change-path="/unei/password"
              />
            </div>
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- ナビゲーション（ログインページを除く） -->
      <div v-if="!isLoginPage" class="mb-6">
        <AdminNavigation
          :navigation-items="navigationItems"
          active-color="green"
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
import AdminNavigation from '~/components/admin/AdminNavigation.vue'
import AccountMenu from '~/components/admin/AccountMenu.vue'
import { useCompanyNavigation } from '~/composables/useCompanyNavigation'

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
const route = useRoute()
const { navigationItems } = useCompanyNavigation()

// ログインページかどうかを判定
const isLoginPage = computed(() => {
  return route.path.includes('/login')
})

// ページ読み込み時にユーザー情報を読み込む
onMounted(() => {
  authStore.loadUserFromStorage()
})
</script>

