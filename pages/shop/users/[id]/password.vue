<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="max-w-md mx-auto space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">パスワード変更</h2>
        <p v-if="user" class="text-sm text-gray-600 mb-4">
          {{ user.name }}さんのパスワードを変更します
        </p>

        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <div v-if="isOwnPassword">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              現在のパスワード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="passwordData.currentPassword"
              type="password"
              :required="isOwnPassword"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="新しいパスワードを入力（6文字以上）"
            />
            <p class="mt-1 text-xs text-gray-500">6文字以上で入力してください</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              新しいパスワード（確認） <span class="text-red-500">*</span>
            </label>
            <input
              v-model="confirmPassword"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
              class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center touch-target"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting || !isFormValid"
              class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
            >
              {{ isSubmitting ? '変更中...' : 'パスワードを変更' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useUserStore, type ChangePasswordInput } from '~/stores/user'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { User } from '~/types'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { pageTitle } = useShopPageTitle('パスワード変更')

const userId = route.params.id as string
const user = ref<User | null>(null)
const isSubmitting = ref(false)
const error = ref('')
const success = ref(false)
const confirmPassword = ref('')

const passwordData = ref<ChangePasswordInput>({
  currentPassword: '',
  newPassword: ''
})

const isOwnPassword = computed(() => {
  return userId === authStore.user?.id
})

const isFormValid = computed(() => {
  if (isOwnPassword.value && !passwordData.value.currentPassword) {
    return false
  }
  return passwordData.value.newPassword.length >= 6 &&
         passwordData.value.newPassword === confirmPassword.value
})

const handleCancel = () => {
  router.back()
}

const handleChangePassword = async () => {
  if (!isFormValid.value) {
    error.value = 'パスワードが一致しないか、6文字未満です'
    return
  }

  isSubmitting.value = true
  error.value = ''
  success.value = false

  try {
    // 自分のパスワード変更の場合は現在のパスワードが必要
    const input: ChangePasswordInput = {
      currentPassword: isOwnPassword.value ? passwordData.value.currentPassword : '',
      newPassword: passwordData.value.newPassword
    }

    await userStore.changePassword(userId, input)

    success.value = true
    passwordData.value = {
      currentPassword: '',
      newPassword: ''
    }
    confirmPassword.value = ''

    // 3秒後にリダイレクト
    setTimeout(() => {
      navigateTo('/shop/users')
    }, 3000)
  } catch (err: any) {
    error.value = err?.data?.error || 'パスワードの変更に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }

  // 店舗情報の確認
  shopStore.loadShopFromStorage()
  if (!shopStore.currentShop && authStore.user?.shop) {
    shopStore.setCurrentShop(authStore.user.shop)
  }

  // 自分のパスワード変更、または管理者権限が必要
  if (userId !== authStore.user?.id && !authStore.isManager) {
    await navigateTo('/shop/dashboard')
    return
  }

  try {
    user.value = await userStore.fetchUser(userId)
  } catch (err: any) {
    error.value = err?.data?.error || 'ユーザー情報の取得に失敗しました'
  }
})
</script>

