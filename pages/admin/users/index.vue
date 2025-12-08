<template>
  <NuxtLayout name="default" title="スタッフ管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/admin/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/admin/users"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          スタッフ管理
        </NuxtLink>
        <NuxtLink
          to="/staff/orders"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          注文管理
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
        <h2 class="text-2xl font-bold">スタッフ管理</h2>
        <button
          v-if="authStore.isManager"
          @click="showAddModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors touch-target"
        >
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
        <div
          v-for="user in userStore.users"
          :key="user.id"
          class="bg-white p-4 rounded-lg shadow"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold">{{ user.name }}</h3>
                <span :class="getRoleBadgeClass(user.role)">
                  {{ getRoleLabel(user.role) }}
                </span>
                <span v-if="!user.isActive" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm">
                  無効
                </span>
              </div>
              <p class="text-sm text-gray-600 mb-1">ユーザー名: {{ user.username }}</p>
              <p v-if="user.email" class="text-sm text-gray-600 mb-1">メール: {{ user.email }}</p>
              <p v-if="user.lastLoginAt" class="text-xs text-gray-500">
                最終ログイン: {{ formatDate(user.lastLoginAt) }}
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <NuxtLink
                :to="`/admin/users/${user.id}/edit`"
                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors touch-target text-sm"
              >
                編集
              </NuxtLink>
              <NuxtLink
                :to="`/admin/users/${user.id}/password`"
                class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors touch-target text-sm"
              >
                パスワード変更
              </NuxtLink>
              <button
                v-if="authStore.isOwner && user.id !== authStore.user?.id"
                @click="confirmDelete(user)"
                class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors touch-target text-sm"
              >
                削除
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 追加モーダル -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">スタッフを追加</h3>

          <form @submit.prevent="handleAddUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              ユーザー名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.username"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="ユーザー名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              パスワード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.password"
              type="password"
              required
              minlength="6"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="パスワードを入力（6文字以上）"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              表示名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="newUser.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="表示名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              v-model="newUser.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="メールアドレスを入力（任意）"
            />
          </div>

          <div v-if="authStore.isOwner">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              役割
            </label>
            <select
              v-model="newUser.role"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="staff">スタッフ</option>
              <option value="manager">管理者</option>
            </select>
          </div>

          <div v-if="addError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ addError }}
          </div>

          <div class="flex gap-3 justify-end">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
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
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">スタッフ情報を編集</h3>

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

          <div class="flex gap-3 justify-end">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
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
import type { User } from '~/types'

const userStore = useUserStore()
const authStore = useAuthStore()

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

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

const getRoleLabel = (role: string) => {
  const labels: Record<string, string> = {
    owner: 'オーナー',
    manager: '管理者',
    staff: 'スタッフ'
  }
  return labels[role] || role
}

const getRoleBadgeClass = (role: string) => {
  const classes: Record<string, string> = {
    owner: 'px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm',
    manager: 'px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm',
    staff: 'px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm'
  }
  return classes[role] || ''
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleString('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

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
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated || !authStore.isManager) {
    await navigateTo('/staff/login')
    return
  }
  
  // 店舗情報の確認
  shopStore.loadShopFromStorage()
  if (!shopStore.currentShop && authStore.user?.shop) {
    shopStore.setCurrentShop(authStore.user.shop)
  }
  
  await userStore.fetchUsers()
})
</script>

