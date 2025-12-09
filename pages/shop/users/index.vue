<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />

      <!-- ヘッダー -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">スタッフ管理</h2>
          <p class="text-sm sm:text-base text-gray-600">店舗スタッフの管理を行います</p>
        </div>
        <button
          v-if="authStore.isManager"
          @click="showAddModal = true"
          class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold flex items-center justify-center gap-2 text-sm sm:text-base"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          スタッフを追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="userStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- ユーザー一覧 -->
      <div v-else-if="userStore.users.length === 0" class="text-center py-12 text-gray-500">
        スタッフが登録されていません
      </div>

      <div v-else class="space-y-3">
        <StaffCard
          v-for="user in userStore.users"
          :key="user.id"
          :staff="user"
          :show-shop-name="false"
          :show-current-user-badge="false"
          :edit-path="`/shop/users/${user.id}/edit`"
          :password-path="`/shop/users/${user.id}/password`"
          :can-delete="authStore.isOwner && user.id !== authStore.user?.id"
          @delete="confirmDelete"
        />
      </div>
    </div>

    <!-- 追加モーダル -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-8">
          <div class="flex items-center gap-3 mb-4 sm:mb-6">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">スタッフを追加</h3>
          </div>

          <form @submit.prevent="handleAddUser" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              ユーザー名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.username"
              type="text"
              required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              placeholder="ユーザー名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              パスワード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.password"
              type="password"
              required
              minlength="6"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              placeholder="パスワードを入力（6文字以上）"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              表示名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.name"
              type="text"
              required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              placeholder="表示名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              メールアドレス
            </label>
            <input
              v-model="newUser.email"
              type="email"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              placeholder="メールアドレスを入力（任意）"
            />
          </div>

          <div v-if="authStore.isOwner">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              役割
            </label>
            <select
              v-model="newUser.role"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            >
              <option value="staff">スタッフ</option>
              <option value="manager">管理者</option>
            </select>
          </div>

          <div v-if="addError" class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm text-red-700 font-medium">{{ addError }}</p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4">
            <button
              type="button"
              @click="showAddModal = false"
              class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-300 font-semibold text-sm sm:text-base"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-sm sm:text-base"
            >
              {{ isSubmitting ? '追加中...' : '追加' }}
            </button>
          </div>
        </form>
        </div>
      </div>
    </div>

    <!-- 編集モーダル -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showEditModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h3 class="text-lg sm:text-xl font-semibold mb-4">スタッフ情報を編集</h3>

        <form @submit.prevent="handleUpdateUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              表示名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editUserData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              v-model="editUserData.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="メールアドレスを入力"
            />
          </div>

          <div v-if="authStore.isOwner && editingUser && editingUser.id !== authStore.user?.id">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              役割
            </label>
            <select
              v-model="editUserData.role"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="staff">スタッフ</option>
              <option value="manager">管理者</option>
            </select>
          </div>

          <div v-if="authStore.isManager && editingUser && editingUser.id !== authStore.user?.id">
            <label class="flex items-center gap-2">
              <input
                v-model="editUserData.isActive"
                type="checkbox"
                class="w-4 h-4"
              />
              <span class="text-sm text-gray-700">有効</span>
            </label>
          </div>

          <div v-if="editError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ editError }}
          </div>

          <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <button
              type="button"
              @click="showEditModal = false"
              class="w-full sm:w-auto px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors text-sm sm:text-base"
            >
              {{ isSubmitting ? '更新中...' : '更新' }}
            </button>
          </div>
        </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useUserStore, type CreateUserInput, type UpdateUserInput } from '~/stores/user'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { User } from '~/types'

const userStore = useUserStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { handleLogout, checkAuth } = useAuthCheck()

const { navigationItems } = useShopNavigation()
const { pageTitle } = useShopPageTitle('スタッフ管理')

const showAddModal = ref(false)
const showEditModal = ref(false)
const isSubmitting = ref(false)
const addError = ref('')
const editError = ref('')
const editingUser = ref<User | null>(null)

const newUser = ref<CreateUserInput>({
  username: '',
  password: '',
  name: '',
  email: '',
  role: 'staff'
})

const editUserData = ref<UpdateUserInput>({
  name: '',
  email: '',
  role: 'staff',
  isActive: true
})


const handleAddUser = async () => {
  isSubmitting.value = true
  addError.value = ''
  
  try {
    await userStore.createUser(newUser.value)
    showAddModal.value = false
    newUser.value = {
      username: '',
      password: '',
      name: '',
      email: '',
      role: 'staff'
    }
  } catch (error: any) {
    addError.value = error?.data?.error || 'スタッフの追加に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const editUser = (user: User) => {
  editingUser.value = user
  editUserData.value = {
    name: user.name,
    email: user.email || '',
    role: user.role,
    isActive: user.isActive
  }
  showEditModal.value = true
}

const handleUpdateUser = async () => {
  if (!editingUser.value) return
  
  isSubmitting.value = true
  editError.value = ''
  
  try {
    await userStore.updateUser(editingUser.value.id, editUserData.value)
    showEditModal.value = false
    editingUser.value = null
  } catch (error: any) {
    editError.value = error?.data?.error || 'スタッフ情報の更新に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const confirmDelete = async (user: User) => {
  if (confirm(`本当に「${user.name}」を削除しますか？`)) {
    try {
      await userStore.deleteUser(user.id)
    } catch (error: any) {
      alert(error?.data?.error || 'スタッフの削除に失敗しました')
    }
  }
}

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  // マネージャー権限チェック
  if (!authStore.isManager) {
    await navigateTo('/staff/login')
    return
  }
  
  await userStore.fetchUsers()
})
</script>

