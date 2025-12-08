<template>
  <div class="min-h-screen bg-gray-50">
    <header v-if="showHeader" class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <h1 class="text-xl font-bold text-gray-900">
            {{ title }}
          </h1>
          <div class="flex items-center gap-4">
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

interface Props {
  title?: string
  showHeader?: boolean
  showFooter?: boolean
}

withDefaults(defineProps<Props>(), {
  title: 'モバイルオーダーシステム',
  showHeader: true,
  showFooter: false
})

const authStore = useAuthStore()

// ページ読み込み時にユーザー情報を読み込む
onMounted(() => {
  authStore.loadUserFromStorage()
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

