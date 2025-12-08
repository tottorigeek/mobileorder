<template>
  <NuxtLayout name="default" title="弊社向け管理ログイン" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-900">
          弊社向け管理ログイン
        </h1>

        <div class="bg-white p-6 rounded-lg shadow">
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                ユーザー名
              </label>
              <input
                v-model="username"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 touch-target"
                placeholder="ユーザー名を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                パスワード
              </label>
              <input
                v-model="password"
                type="password"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 touch-target"
                placeholder="パスワードを入力"
              />
            </div>

            <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
              {{ errorMessage }}
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-green-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
            >
              {{ isLoading ? 'ログイン中...' : 'ログイン' }}
            </button>
          </form>
        </div>

        <div class="mt-4 text-center">
          <NuxtLink
            to="/"
            class="text-blue-600 hover:text-blue-700"
          >
            トップに戻る
          </NuxtLink>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  // ストレージからユーザー情報を読み込み
  authStore.loadUserFromStorage()
  
  // 既に認証済みの場合は管理ページにリダイレクト
  if (authStore.isAuthenticated) {
    navigateTo('/company/dashboard')
  }
})

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'ユーザー名とパスワードを入力してください'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const success = await authStore.login(username.value, password.value)
    
    if (success && authStore.user) {
      // 弊社向け管理ページにリダイレクト
      await navigateTo('/company/dashboard')
    } else {
      errorMessage.value = 'ユーザー名またはパスワードが正しくありません'
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.error || 'ログインに失敗しました'
  } finally {
    isLoading.value = false
  }
}
</script>

