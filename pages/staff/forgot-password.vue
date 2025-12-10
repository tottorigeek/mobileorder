<template>
  <NuxtLayout name="default" title="パスワードリセット" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gradient-to-br from-purple-50 via-indigo-50 to-pink-50">
      <div class="w-full max-w-md">
        <div class="text-center mb-8">
          <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
            パスワードリセット
          </h1>
          <p class="text-gray-600">登録済みのメールアドレスにリセットリンクを送信します</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-xl">
          <form @submit.prevent="handleSubmit" class="space-y-6" v-if="!success">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                ユーザー名またはメールアドレス
              </label>
              <input
                v-model="identifier"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all touch-target"
                placeholder="ユーザー名またはメールアドレスを入力"
              />
            </div>

            <div v-if="error" class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700 font-medium">{{ error }}</p>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl text-lg font-semibold hover:from-purple-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target"
            >
              {{ isLoading ? '送信中...' : 'リセットメールを送信' }}
            </button>
          </form>

          <div v-else class="text-center space-y-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800 mb-2">メールを送信しました</h2>
              <p class="text-gray-600 mb-4">
                パスワードリセット用のリンクをメールアドレスに送信しました。<br>
                メールが届かない場合は、登録されているメールアドレスを確認してください。
              </p>
              <p class="text-sm text-gray-500">
                メール内のリンクは24時間有効です。
              </p>
            </div>
            <NuxtLink
              to="/staff/login"
              class="inline-block w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 rounded-xl text-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target text-center"
            >
              ログインページに戻る
            </NuxtLink>
          </div>
        </div>

        <div class="mt-6 text-center">
          <NuxtLink
            to="/staff/login"
            class="text-purple-600 hover:text-purple-700 font-medium transition-colors inline-flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            ログインページに戻る
          </NuxtLink>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
const identifier = ref('')
const isLoading = ref(false)
const error = ref('')
const success = ref(false)

const handleSubmit = async () => {
  if (!identifier.value) {
    error.value = 'ユーザー名またはメールアドレスを入力してください'
    return
  }

  isLoading.value = true
  error.value = ''
  success.value = false

  try {
    const { buildUrl } = useApiBase()
    
    // ユーザー名かメールアドレスかを判定
    const isEmail = identifier.value.includes('@')
    const requestBody = isEmail 
      ? { email: identifier.value }
      : { username: identifier.value }
    
    const response = await $fetch<{ success: boolean; message: string }>(
      buildUrl('auth/forgot-password'),
      {
        method: 'POST',
        body: requestBody
      }
    )
    
    if (response.success) {
      success.value = true
    } else {
      error.value = response.message || 'メールの送信に失敗しました'
    }
  } catch (err: any) {
    error.value = err?.data?.error || err?.message || 'メールの送信に失敗しました'
  } finally {
    isLoading.value = false
  }
}
</script>

