<template>
  <NuxtLayout name="default" title="店舗編集">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/company/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/company/shops"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          店舗管理
        </NuxtLink>
        <NuxtLink
          to="/company/users"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ユーザー管理
        </NuxtLink>
        <NuxtLink
          to="/company/error-logs"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          エラーログ
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
        <h2 class="text-2xl font-bold">店舗編集</h2>
        <NuxtLink
          to="/company/shops"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
        >
          一覧に戻る
        </NuxtLink>
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- エラー -->
      <div v-else-if="error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        {{ error }}
      </div>

      <!-- 編集フォーム -->
      <div v-else-if="shop" class="bg-white p-6 rounded-lg shadow">
        <form @submit.prevent="handleUpdateShop" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗コード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editData.code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="shop001"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="店舗名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              説明
            </label>
            <textarea
              v-model="editData.description"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              rows="3"
              placeholder="店舗の説明を入力"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              住所
            </label>
            <input
              v-model="editData.address"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="住所を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              電話番号
            </label>
            <input
              v-model="editData.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="03-1234-5678"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              v-model="editData.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="shop@example.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              最大テーブル数
            </label>
            <input
              v-model.number="editData.maxTables"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="20"
            />
          </div>

          <div>
            <label class="flex items-center gap-2">
              <input
                v-model="editData.isActive"
                type="checkbox"
                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
              />
              <span class="text-sm font-medium text-gray-700">アクティブ</span>
            </label>
          </div>

          <div v-if="updateError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ updateError }}
          </div>

          <div v-if="success" class="p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            店舗情報を更新しました
          </div>

          <div class="flex gap-3 justify-end">
            <NuxtLink
              to="/company/shops"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              キャンセル
            </NuxtLink>
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
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import type { Shop } from '~/types'

const route = useRoute()
const shopStore = useShopStore()
const authStore = useAuthStore()

const shopId = route.params.id as string
const shop = ref<Shop | null>(null)
const isLoading = ref(true)
const isSubmitting = ref(false)
const error = ref('')
const updateError = ref('')
const success = ref(false)

const editData = ref({
  code: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  email: '',
  maxTables: 20,
  isActive: true
})

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const handleUpdateShop = async () => {
  isSubmitting.value = true
  updateError.value = ''
  success.value = false
  
  try {
    await shopStore.updateShop(shopId, editData.value)
    success.value = true
    
    // 2秒後にリダイレクト
    setTimeout(() => {
      navigateTo('/company/shops')
    }, 2000)
  } catch (err: any) {
    updateError.value = err?.data?.error || err?.message || '店舗情報の更新に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 店舗情報を取得
  try {
    // IDで直接店舗情報を取得
    const fetchedShop = await shopStore.fetchShopById(shopId)
    
    if (!fetchedShop) {
      error.value = '店舗が見つかりませんでした'
      isLoading.value = false
      return
    }
    
    shop.value = fetchedShop
    editData.value = {
      code: fetchedShop.code,
      name: fetchedShop.name,
      description: fetchedShop.description || '',
      address: fetchedShop.address || '',
      phone: fetchedShop.phone || '',
      email: fetchedShop.email || '',
      maxTables: fetchedShop.maxTables || 20,
      isActive: fetchedShop.isActive
    }
  } catch (err: any) {
    error.value = err?.data?.error || err?.message || '店舗情報の取得に失敗しました'
  } finally {
    isLoading.value = false
  }
})
</script>

