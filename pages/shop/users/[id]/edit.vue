<template>
  <NuxtLayout name="default" title="スタッフ情報編集">
    <div class="max-w-md mx-auto space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">スタッフ情報を編集</h2>

        <form @submit.prevent="handleUpdateUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              表示名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editData.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              v-model="editData.email"
              type="email"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="メールアドレスを入力"
            />
          </div>

          <div v-if="authStore.isOwner && user && user.id !== authStore.user?.id">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              役割
            </label>
            <select
              v-model="editData.role"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="staff">スタッフ</option>
              <option value="manager">管理者</option>
            </select>
          </div>

          <div v-if="authStore.isManager && user && user.id !== authStore.user?.id">
            <label class="flex items-center gap-2">
              <input
                v-model="editData.isActive"
                type="checkbox"
                class="w-4 h-4"
              />
              <span class="text-sm text-gray-700">有効</span>
            </label>
          </div>

          <div v-if="error" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ error }}
          </div>

          <div v-if="success" class="p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            スタッフ情報が更新されました
          </div>

          <div class="flex gap-3">
            <NuxtLink
              to="/shop/users"
              class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center touch-target"
            >
              キャンセル
            </NuxtLink>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
            >
              {{ isSubmitting ? '更新中...' : '更新' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useUserStore, type UpdateUserInput } from '~/stores/user'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { User } from '~/types'

const route = useRoute()
const userStore = useUserStore()
const authStore = useAuthStore()
const shopStore = useShopStore()

const userId = route.params.id as string
const user = ref<User | null>(null)
const isSubmitting = ref(false)
const error = ref('')
const success = ref(false)

const editData = ref<UpdateUserInput>({
  name: '',
  email: '',
  role: 'staff',
  isActive: true
})

const handleUpdateUser = async () => {
  isSubmitting.value = true
  error.value = ''
  success.value = false

  try {
    await userStore.updateUser(userId, editData.value)
    success.value = true
    
    // 2秒後にリダイレクト
    setTimeout(() => {
      navigateTo('/shop/users')
    }, 2000)
  } catch (err: any) {
    error.value = err?.data?.error || 'スタッフ情報の更新に失敗しました'
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

  // 自分の情報、または管理者権限が必要
  if (userId !== authStore.user?.id && !authStore.isManager) {
    await navigateTo('/shop/dashboard')
    return
  }

  try {
    user.value = await userStore.fetchUser(userId)
    editData.value = {
      name: user.value.name,
      email: user.value.email || '',
      role: user.value.role,
      isActive: user.value.isActive
    }
  } catch (err: any) {
    error.value = err?.data?.error || 'スタッフ情報の取得に失敗しました'
  }
})
</script>

