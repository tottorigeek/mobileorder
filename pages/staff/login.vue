<template>
  <NuxtLayout name="default" title="スタッフログイン" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-900">
          スタッフログイン
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
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 touch-target"
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
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 touch-target"
                placeholder="パスワードを入力"
              />
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full bg-purple-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
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
const username = ref('')
const password = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  isLoading.value = true
  try {
    // TODO: 認証API呼び出しに置き換え
    // モック: 簡単な認証
    if (username.value && password.value) {
      // 認証成功
      await navigateTo('/staff/orders')
    } else {
      alert('ユーザー名とパスワードを入力してください')
    }
  } catch (error) {
    alert('ログインに失敗しました')
  } finally {
    isLoading.value = false
  }
}
</script>

