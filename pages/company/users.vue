<template>
  <NuxtLayout name="company" title="ユーザー管理">
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-1">ユーザー管理</h2>
        <p class="text-gray-600">システム全体のユーザーを管理します</p>
      </div>

      <!-- 説明 -->
      <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl shadow-lg border-l-4 border-green-500">
        <p class="text-gray-700 font-medium flex items-center gap-2">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          各店舗のユーザー情報を一覧で確認できます
        </p>
      </div>

      <!-- フィルター -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">フィルター</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- 検索 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              検索
            </label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="名前、ユーザー名、メールで検索"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            />
          </div>

          <!-- ロールフィルター -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              役割
            </label>
            <select
              v-model="filters.role"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="owner">オーナー</option>
              <option value="manager">管理者</option>
              <option value="staff">スタッフ</option>
            </select>
          </div>

          <!-- 店舗フィルター -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗
            </label>
            <select
              v-model="filters.shopId"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="none">未設定</option>
              <option
                v-for="shop in shopStore.shops"
                :key="shop.id"
                :value="shop.id"
              >
                {{ shop.name }} ({{ shop.code }})
              </option>
            </select>
          </div>

          <!-- 有効/無効フィルター -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              ステータス
            </label>
            <select
              v-model="filters.isActive"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option :value="null">すべて</option>
              <option :value="true">有効</option>
              <option :value="false">無効</option>
            </select>
          </div>
        </div>

        <!-- フィルターリセットボタン -->
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm"
          >
            フィルターをリセット
          </button>
        </div>

        <!-- フィルター結果の件数表示 -->
        <div class="mt-4 text-sm text-gray-600">
          表示中: {{ filteredUsers.length }} / {{ userStore.users.length }} 件
        </div>
      </div>

      <!-- エラーメッセージ -->
      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        {{ errorMessage }}
      </div>

      <!-- ローディング -->
      <div v-if="userStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- ユーザー一覧 -->
      <div v-else-if="userStore.users.length === 0 && !errorMessage" class="text-center py-12 text-gray-500">
        ユーザーが登録されていません
      </div>

      <div v-else-if="filteredUsers.length === 0" class="text-center py-12 text-gray-500">
        フィルター条件に一致するユーザーが見つかりませんでした
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="user in filteredUsers"
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
              <div v-if="user.shop" class="flex items-center gap-2 mb-1">
                <p class="text-sm text-gray-600">
                  店舗: {{ user.shop.name }} ({{ user.shop.code }})
                </p>
                <NuxtLink
                  :to="`/company/shops/${user.shop.id}/edit`"
                  class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium hover:bg-green-200 transition-colors"
                  @click.stop
                >
                  店舗設定
                </NuxtLink>
              </div>
              <p v-else class="text-sm text-gray-500 mb-1">店舗: 未設定</p>
              <p v-if="user.email" class="text-sm text-gray-600 mb-1">メール: {{ user.email }}</p>
              <p v-if="user.lastLoginAt" class="text-xs text-gray-500">
                最終ログイン: {{ formatDate(user.lastLoginAt) }}
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                @click="editUser(user)"
                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors touch-target text-sm"
              >
                編集
              </button>
              <button
                v-if="user.id !== authStore.user?.id && authStore.isOwner"
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

    <!-- 編集モーダル -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showEditModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">ユーザー情報を編集</h3>

          <form @submit.prevent="handleUpdateUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                表示名 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="editUserData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                メールアドレス
              </label>
              <input
                v-model="editUserData.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="メールアドレスを入力"
              />
            </div>

            <div v-if="editingUser && editingUser.id !== authStore.user?.id && authStore.isOwner">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                役割
              </label>
              <select
                v-model="editUserData.role"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value="staff">スタッフ</option>
                <option value="manager">管理者</option>
                <option value="owner">オーナー</option>
              </select>
            </div>

            <div v-if="editingUser && editingUser.id !== authStore.user?.id && authStore.isOwner">
              <label class="flex items-center gap-2">
                <input
                  v-model="editUserData.isActive"
                  type="checkbox"
                  class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
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
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
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
import { useUserStore, type UpdateUserInput } from '~/stores/user'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { User } from '~/types'

const userStore = useUserStore()
const authStore = useAuthStore()
const shopStore = useShopStore()

const showEditModal = ref(false)
const isSubmitting = ref(false)
const editError = ref('')
const editingUser = ref<User | null>(null)
const errorMessage = ref('')

// フィルター設定
const filters = ref({
  search: '',
  role: '',
  shopId: '',
  isActive: null as boolean | null
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

// フィルターされたユーザーリスト
const filteredUsers = computed(() => {
  let users = [...userStore.users]

  // 検索フィルター
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    users = users.filter(user => {
      return (
        user.name.toLowerCase().includes(searchLower) ||
        user.username.toLowerCase().includes(searchLower) ||
        (user.email && user.email.toLowerCase().includes(searchLower))
      )
    })
  }

  // ロールフィルター
  if (filters.value.role) {
    users = users.filter(user => user.role === filters.value.role)
  }

  // 店舗フィルター
  if (filters.value.shopId) {
    if (filters.value.shopId === 'none') {
      users = users.filter(user => !user.shop || !user.shopId)
    } else {
      users = users.filter(user => user.shopId === filters.value.shopId)
    }
  }

  // 有効/無効フィルター
  if (filters.value.isActive !== null) {
    users = users.filter(user => user.isActive === filters.value.isActive)
  }

  return users
})

// フィルターリセット
const resetFilters = () => {
  filters.value = {
    search: '',
    role: '',
    shopId: '',
    isActive: null
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
  
  // オーナーでない場合、役割と有効フラグの変更は不可
  if (!authStore.isOwner) {
    // managerは名前とメールアドレスのみ変更可能
    const updateData: any = {
      name: editUserData.value.name,
      email: editUserData.value.email
    }
    editUserData.value = updateData
  }
  
  isSubmitting.value = true
  editError.value = ''
  
  try {
    await userStore.updateCompanyUser(editingUser.value.id, editUserData.value)
    showEditModal.value = false
    editingUser.value = null
  } catch (error: any) {
    editError.value = error?.data?.error || 'ユーザー情報の更新に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const confirmDelete = async (user: User) => {
  if (confirm(`本当に「${user.name}」を削除しますか？`)) {
    try {
      await userStore.deleteCompanyUser(user.id)
    } catch (error: any) {
      alert(error?.data?.error || 'ユーザーの削除に失敗しました')
    }
  }
}

onMounted(async () => {
  console.log('[company/users] onMounted called')
  
  // 認証チェック
  authStore.loadUserFromStorage()
  console.log('[company/users] isAuthenticated:', authStore.isAuthenticated)
  console.log('[company/users] user:', authStore.user)
  console.log('[company/users] isOwner:', authStore.isOwner)
  console.log('[company/users] isManager:', authStore.isManager)
  
  if (!authStore.isAuthenticated) {
    console.log('[company/users] Not authenticated, redirecting to login')
    await navigateTo('/company/login')
    return
  }
  
  // API側で権限チェックを行うため、フロントエンドでは認証のみ確認
  try {
    console.log('[company/users] Fetching all users...')
    await Promise.all([
      userStore.fetchAllUsers(),
      shopStore.fetchShops()
    ])
    console.log('[company/users] Users fetched successfully')
    errorMessage.value = ''
  } catch (error: any) {
    console.error('[company/users] Error fetching users:', error)
    // 権限エラーの場合はエラーメッセージを表示（リダイレクトしない）
    if (error?.statusCode === 403 || error?.data?.status === 403 || error?.status === 403) {
      errorMessage.value = 'オーナーまたはマネージャー権限が必要です。このページにアクセスするには、オーナーまたはマネージャーロールが必要です。'
      console.warn('[company/users] Owner or Manager permission required')
    } else {
      errorMessage.value = error?.data?.error || 'ユーザー一覧の取得に失敗しました'
      console.error('ユーザー一覧の取得に失敗しました:', error)
    }
  }
})
</script>

