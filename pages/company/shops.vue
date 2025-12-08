<template>
  <NuxtLayout name="default" title="店舗管理">
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
        <NuxtLink
          to="/admin/users/password"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          パスワード変更
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
        <h2 class="text-2xl font-bold">店舗管理</h2>
        <button
          @click="showAddModal = true"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors touch-target"
        >
          店舗を追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="shopStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- 店舗一覧 -->
      <div v-else-if="shopStore.shops.length === 0" class="text-center py-12 text-gray-500">
        店舗が登録されていません
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="shop in shopStore.shops"
          :key="shop.id"
          class="bg-white p-4 rounded-lg shadow"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold">{{ shop.name }}</h3>
                <span :class="shop.isActive ? 'px-2 py-1 bg-green-100 text-green-800 rounded text-sm' : 'px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm'">
                  {{ shop.isActive ? 'アクティブ' : '無効' }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mb-1">コード: {{ shop.code }}</p>
              <p v-if="shop.description" class="text-sm text-gray-600 mb-1">{{ shop.description }}</p>
              <p v-if="shop.address" class="text-sm text-gray-500">{{ shop.address }}</p>
            </div>
            <div class="flex gap-2">
              <NuxtLink
                :to="`/company/shops/${shop.id}/edit`"
                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors touch-target text-sm"
              >
                編集
              </NuxtLink>
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
          <h3 class="text-lg font-semibold mb-4">店舗を追加</h3>

          <form @submit.prevent="handleAddShop" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                店舗コード <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newShop.code"
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
                v-model="newShop.name"
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
                v-model="newShop.description"
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
                v-model="newShop.address"
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
                v-model="newShop.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="03-1234-5678"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                最大テーブル数
              </label>
              <input
                v-model.number="newShop.maxTables"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="20"
              />
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
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
              >
                {{ isSubmitting ? '追加中...' : '追加' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import type { Shop } from '~/types'

const shopStore = useShopStore()
const authStore = useAuthStore()

const showAddModal = ref(false)
const isSubmitting = ref(false)
const addError = ref('')

const newShop = ref({
  code: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  maxTables: 20
})

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const handleAddShop = async () => {
  isSubmitting.value = true
  addError.value = ''
  
  try {
    // TODO: 店舗追加APIの実装が必要
    // await shopStore.createShop(newShop.value)
    alert('店舗追加機能は実装予定です')
    showAddModal.value = false
    newShop.value = {
      code: '',
      name: '',
      description: '',
      address: '',
      phone: '',
      maxTables: 20
    }
  } catch (error: any) {
    addError.value = error?.data?.error || '店舗の追加に失敗しました'
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

  await shopStore.fetchShops()
})
</script>

