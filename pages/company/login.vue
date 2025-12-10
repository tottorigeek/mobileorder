<template>
  <NuxtLayout name="default" title="弊社向け管理ログイン" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
      <div class="w-full max-w-md">
        <div class="text-center mb-8">
          <div class="w-20 h-20 bg-gradient-to-br from-green-600 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
            弊社向け管理ログイン
          </h1>
          <p class="text-gray-600">システム管理にアクセス</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-xl">
          <form @submit.prevent="handleLogin" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                ユーザー名
              </label>
              <input
                v-model="username"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all touch-target"
                placeholder="ユーザー名を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                パスワード
              </label>
              <input
                v-model="password"
                type="password"
                required
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all touch-target"
                placeholder="パスワードを入力"
              />
            </div>

            <div v-if="errorMessage" class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl text-lg font-semibold hover:from-green-700 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target"
            >
              {{ isLoading ? 'ログイン中...' : 'ログイン' }}
            </button>
          </form>

          <div class="mt-6 text-center">
            <button
              @click="showForgotPasswordModal = true"
              class="text-green-600 hover:text-green-700 font-medium transition-colors text-sm underline"
            >
              パスワードをお忘れの方はこちら
            </button>
          </div>
        </div>

        <!-- パスワードリセットモーダル -->
        <div
          v-if="showForgotPasswordModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
          @click.self="showForgotPasswordModal = false"
        >
          <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 sm:p-8 relative">
            <button
              @click="showForgotPasswordModal = false"
              class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
              </div>
              <h2 class="text-2xl font-bold mb-2 bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                パスワードリセット
              </h2>
              <p class="text-gray-600 text-sm">登録済みのメールアドレスにリセットリンクを送信します</p>
            </div>

            <form @submit.prevent="handleForgotPassword" class="space-y-4" v-if="!forgotPasswordSuccess">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  ユーザー名またはメールアドレス
                </label>
                <input
                  v-model="forgotPasswordIdentifier"
                  type="text"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"
                  placeholder="ユーザー名またはメールアドレスを入力"
                />
              </div>

              <div v-if="forgotPasswordError" class="p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <p class="text-sm text-red-700 font-medium">{{ forgotPasswordError }}</p>
              </div>

              <button
                type="submit"
                :disabled="isForgotPasswordLoading"
                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300"
              >
                {{ isForgotPasswordLoading ? '送信中...' : 'リセットメールを送信' }}
              </button>
            </form>

            <div v-else class="text-center space-y-4">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">メールを送信しました</h3>
                <p class="text-gray-600 text-sm mb-4">
                  パスワードリセット用のリンクをメールアドレスに送信しました。<br>
                  メールが届かない場合は、登録されているメールアドレスを確認してください。
                </p>
                <p class="text-xs text-gray-500">
                  メール内のリンクは24時間有効です。
                </p>
              </div>
              <button
                @click="showForgotPasswordModal = false; forgotPasswordSuccess = false; forgotPasswordIdentifier = ''"
                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-700 transition-all duration-300"
              >
                閉じる
              </button>
            </div>
          </div>
        </div>

        <div class="mt-6 text-center">
          <NuxtLink
            to="/"
            class="text-green-600 hover:text-green-700 font-medium transition-colors inline-flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
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

// パスワードリセットモーダル関連
const showForgotPasswordModal = ref(false)
const forgotPasswordIdentifier = ref('')
const isForgotPasswordLoading = ref(false)
const forgotPasswordError = ref('')
const forgotPasswordSuccess = ref(false)

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

const handleForgotPassword = async () => {
  if (!forgotPasswordIdentifier.value) {
    forgotPasswordError.value = 'ユーザー名またはメールアドレスを入力してください'
    return
  }

  isForgotPasswordLoading.value = true
  forgotPasswordError.value = ''
  forgotPasswordSuccess.value = false

  try {
    const { buildUrl } = useApiBase()
    
    // ユーザー名かメールアドレスかを判定
    const isEmail = forgotPasswordIdentifier.value.includes('@')
    const requestBody = isEmail 
      ? { 
          email: forgotPasswordIdentifier.value,
          reset_path: '/company/reset-password'
        }
      : { 
          username: forgotPasswordIdentifier.value,
          reset_path: '/company/reset-password'
        }
    
    const response = await $fetch<{ success: boolean; message: string }>(
      buildUrl('auth/forgot-password'),
      {
        method: 'POST',
        body: requestBody
      }
    )
    
    if (response.success) {
      forgotPasswordSuccess.value = true
    } else {
      forgotPasswordError.value = response.message || 'メールの送信に失敗しました'
    }
  } catch (err: any) {
    forgotPasswordError.value = err?.data?.error || err?.message || 'メールの送信に失敗しました'
  } finally {
    isForgotPasswordLoading.value = false
  }
}
</script>

