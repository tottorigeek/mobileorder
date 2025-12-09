<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">カテゴリ管理</h1>
          <p class="text-sm sm:text-base text-gray-600">メニューカテゴリを管理します</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          カテゴリを追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="categoryStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- カテゴリ一覧 -->
      <div v-else-if="categoryStore.categories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div
          v-for="category in categoryStore.categories"
          :key="category.id"
          class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-300"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1 min-w-0">
              <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1 truncate">{{ category.name }}</h3>
              <p class="text-xs sm:text-sm text-gray-600 truncate">コード: {{ category.code }}</p>
            </div>
            <span
              :class="[
                'px-2 sm:px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0 ml-2',
                category.isActive ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md' : 'bg-gray-200 text-gray-700'
              ]"
            >
              {{ category.isActive ? '有効' : '無効' }}
            </span>
          </div>

          <div class="space-y-2 mb-4 p-3 sm:p-4 bg-gray-50 rounded-xl">
            <p class="text-xs sm:text-sm text-gray-700 flex items-center gap-2">
              <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
              <span class="font-medium">表示順:</span> {{ category.displayOrder }}
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="editCategory(category)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              編集
            </button>
            <button
              @click="deleteCategory(category)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              削除
            </button>
          </div>
        </div>
      </div>

      <!-- カテゴリが存在しない場合 -->
      <div v-else class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-6">カテゴリが登録されていません</p>
        <button
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
        >
          カテゴリを追加
        </button>
      </div>
    </div>

    <!-- 作成・編集モーダル -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h2 class="text-xl font-bold mb-4">
            {{ editingCategory ? 'カテゴリを編集' : 'カテゴリを追加' }}
          </h2>

          <form @submit.prevent="saveCategory" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                カテゴリコード <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.code"
                type="text"
                required
                :disabled="!!editingCategory"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100"
                placeholder="例: food, drink"
              />
              <p class="mt-1 text-xs text-gray-500">編集時は変更できません</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                カテゴリ名 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="例: フード, ドリンク"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                表示順 <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="formData.displayOrder"
                type="number"
                required
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="0"
              />
            </div>

            <div>
              <label class="flex items-center gap-2">
                <input
                  v-model="formData.isActive"
                  type="checkbox"
                  class="w-4 h-4"
                />
                <span class="text-sm text-gray-700">有効</span>
              </label>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
              >
                キャンセル
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                {{ editingCategory ? '更新' : '作成' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useCategoryStore } from '~/stores/category'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { ShopCategory } from '~/types'

const { pageTitle } = useShopPageTitle('カテゴリ管理')

const categoryStore = useCategoryStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { checkAuth } = useAuthCheck()

const showCreateModal = ref(false)
const editingCategory = ref<ShopCategory | null>(null)

const formData = ref({
  code: '',
  name: '',
  displayOrder: 0,
  isActive: true
})

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  // カテゴリ一覧の取得
  if (shopStore.currentShop) {
    await categoryStore.fetchCategories(shopStore.currentShop.id)
  }
})

const editCategory = (category: ShopCategory) => {
  editingCategory.value = category
  formData.value = {
    code: category.code,
    name: category.name,
    displayOrder: category.displayOrder,
    isActive: category.isActive
  }
  showCreateModal.value = true
}

const saveCategory = async () => {
  if (!shopStore.currentShop) {
    alert('店舗が選択されていません')
    return
  }

  try {
    if (editingCategory.value) {
      // 更新
      await categoryStore.updateCategory(editingCategory.value.id, formData.value)
    } else {
      // 作成
      await categoryStore.createCategory(shopStore.currentShop.id, formData.value)
    }
    closeModal()
  } catch (error: any) {
    alert(error.message || 'カテゴリの保存に失敗しました')
  }
}

const deleteCategory = async (category: ShopCategory) => {
  if (!confirm(`カテゴリ「${category.name}」を削除してもよろしいですか？`)) {
    return
  }

  try {
    await categoryStore.deleteCategory(category.id)
  } catch (error: any) {
    alert(error.message || 'カテゴリの削除に失敗しました')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingCategory.value = null
  formData.value = {
    code: '',
    name: '',
    displayOrder: 0,
    isActive: true
  }
}
</script>

