<template>
    <div class="max-w-md mx-auto space-y-6 px-4">
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow">
        <h2 class="text-lg sm:text-xl font-bold mb-4">パスワード変更</h2>

        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              現在のパスワード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="passwordData.currentPassword"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="現在のパスワードを入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              新しいパスワード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="passwordData.newPassword"
              type="password"
              required
              minlength="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="新しいパスワードを入力（6文字以上）"
            />
            <p class="mt-1 text-xs text-gray-500">6文字以上で入力してください</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              新しいパスワード（確認） <span class="text-red-500">*</span>
            </label>
            <input
              v-model="passwordData.confirmPassword"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="新しいパスワードを再入力"
            />
          </div>

          <div v-if="error" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ error }}
          </div>

          <div v-if="success" class="p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            パスワードが変更されました
          </div>

          <div class="flex gap-3">
            <button
              type="button"
              @click="handleCancel"
              class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors touch-target"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting || !isFormValid"
              class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
            >
              {{ isSubmitting ? '変更中...' : 'パスワードを変更' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup lang="ts">
import { useUserStore, type ChangePasswordInput } from '~/stores/user'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: 'company'
})

const router = useRouter()
const userStore = useUserStore()
const authStore = useAuthStore()

const isSubmitting = ref(false)
const error = ref('')
const success = ref(false)

const passwordData = ref<ChangePasswordInput & { confirmPassword: string }>({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const isFormValid = computed(() => {
  return passwordData.value.currentPassword &&
         passwordData.value.newPassword.length >= 6 &&
         passwordData.value.newPassword === passwordData.value.confirmPassword
})

const handleCancel = () => {
  router.back()
}

const handleChangePassword = async () => {
  if (!isFormValid.value) {
    error.value = 'パスワードが一致しません'
    return
  }

  isSubmitting.value = true
  error.value = ''
  success.value = false

  try {
    if (!authStore.user) {
      throw new Error('ユーザー情報が取得できません')
    }

    await userStore.changePassword(authStore.user.id, {
      currentPassword: passwordData.value.currentPassword,
      newPassword: passwordData.value.newPassword
    })

    success.value = true
    passwordData.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }

    // 3秒後にリダイレクト
    setTimeout(() => {
      navigateTo('/unei/users')
    }, 3000)
  } catch (err: any) {
    // エラーメッセージの取得
    let errorMessage = 'パスワードの変更に失敗しました'
    
    if (err?.message) {
      errorMessage = err.message
    } else if (err?.data?.error) {
      errorMessage = err.data.error
    } else if (err?.error) {
      errorMessage = err.error
    }
    
    // 401エラーの場合は、ログイン画面にリダイレクト
    if (err?.status === 401) {
      error.value = 'セッションが無効です。再度ログインしてください。'
      setTimeout(() => {
        navigateTo('/unei/login')
      }, 2000)
      return
    }
    
    error.value = errorMessage
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/unei/login')
    return
  }
})
</script>

