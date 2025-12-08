<template>
  <NuxtLayout name="default" title="ユーザー管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/company/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/company/shops"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          店舗管理
        </NuxtLink>
        <NuxtLink
          to="/company/users"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          ユーザー管理
        </NuxtLink>
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
        >
          ログアウト
        </button>
      </div>

      <!-- ヘッダー -->
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">ユーザー管理</h2>
      </div>

      <!-- 説明 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <p class="text-gray-600">
          システム全体のユーザーを管理します。各店舗のユーザー情報を一覧で確認できます。
        </p>
      </div>

      <!-- 実装予定メッセージ -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-yellow-800 mb-2">実装予定</h3>
        <p class="text-yellow-700">
          システム全体のユーザー管理機能は現在実装中です。
          現在は各店舗の管理画面からユーザーを管理できます。
        </p>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }
})
</script>

