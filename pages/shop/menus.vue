<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">メニュー管理</h1>
          <p class="text-sm sm:text-base text-gray-600">店舗のメニューを管理します</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold flex items-center justify-center gap-2 text-sm sm:text-base"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          メニューを追加
        </button>
      </div>

      <!-- フィルター -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
          <div class="flex-1 w-full sm:min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              カテゴリで絞り込み
            </label>
            <select
              v-model="selectedCategory"
              @change="filterMenus"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">すべて</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.code"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="flex items-center gap-2">
              <input
                v-model="showAvailableOnly"
                type="checkbox"
                @change="filterMenus"
                class="w-4 h-4"
              />
              <span class="text-sm text-gray-700">利用可能のみ表示</span>
            </label>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="menuStore.isLoading || categoryStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- メニュー一覧 -->
      <div v-else-if="filteredMenus.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div
          v-for="menu in filteredMenus"
          :key="menu.id"
          class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-300"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                  {{ menu.number }}
                </span>
                <span
                  v-if="menu.isRecommended"
                  class="px-2 sm:px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold"
                >
                  おすすめ
                </span>
                <span
                  :class="[
                    'px-2 sm:px-3 py-1 rounded-full text-xs font-semibold',
                    menu.isAvailable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ menu.isAvailable ? '利用可能' : '利用不可' }}
                </span>
              </div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-1 break-words">{{ menu.name }}</h3>
              <p v-if="menu.description" class="text-xs sm:text-sm text-gray-600 break-words">{{ menu.description }}</p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
            <p class="text-xl sm:text-2xl font-bold text-blue-600">¥{{ menu.price.toLocaleString() }}</p>
            <span class="px-2 sm:px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs sm:text-sm self-start sm:self-auto">
              {{ getCategoryName(menu.category) }}
            </span>
          </div>

          <div class="flex gap-2">
            <button
              @click="editMenu(menu)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              編集
            </button>
            <button
              @click="deleteMenu(menu)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              削除
            </button>
          </div>
        </div>
      </div>

      <!-- メニューが存在しない場合 -->
      <div v-else class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-6">メニューが登録されていません</p>
        <button
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
        >
          メニューを追加
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
            {{ editingMenu ? 'メニューを編集' : 'メニューを追加' }}
          </h2>

          <form @submit.prevent="saveMenu" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                メニュー番号 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.number"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="例: 001"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                メニュー名 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="メニュー名を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                説明
              </label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="メニューの説明を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                価格 <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="formData.price"
                type="number"
                required
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="価格を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                カテゴリ <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.category"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">選択してください</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.code"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="flex items-center gap-2">
                <input
                  v-model="formData.isAvailable"
                  type="checkbox"
                  class="w-4 h-4"
                />
                <span class="text-sm text-gray-700">利用可能</span>
              </label>
            </div>

            <div>
              <label class="flex items-center gap-2">
                <input
                  v-model="formData.isRecommended"
                  type="checkbox"
                  class="w-4 h-4"
                />
                <span class="text-sm text-gray-700">おすすめ</span>
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
                {{ editingMenu ? '更新' : '作成' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useCategoryStore } from '~/stores/category'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Menu, ShopCategory } from '~/types'

const { pageTitle } = useShopPageTitle('メニュー管理')

const menuStore = useMenuStore()
const categoryStore = useCategoryStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { checkAuth } = useAuthCheck()

const showCreateModal = ref(false)
const editingMenu = ref<Menu | null>(null)
const selectedCategory = ref('')
const showAvailableOnly = ref(false)

const formData = ref({
  number: '',
  name: '',
  description: '',
  price: 0,
  category: '',
  isAvailable: true,
  isRecommended: false
})

const categories = computed(() => categoryStore.activeCategories)

const filteredMenus = computed(() => {
  let menus = menuStore.menus

  if (selectedCategory.value) {
    menus = menus.filter(m => m.category === selectedCategory.value)
  }

  if (showAvailableOnly.value) {
    menus = menus.filter(m => m.isAvailable)
  }

  return menus.sort((a, b) => a.number.localeCompare(b.number))
})

const getCategoryName = (categoryCode: string) => {
  const category = categories.value.find(c => c.code === categoryCode)
  return category?.name || categoryCode
}

const filterMenus = () => {
  // computedで自動的にフィルタリングされる
}

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  // データの取得
  if (shopStore.currentShop) {
    await Promise.all([
      menuStore.fetchMenus(shopStore.currentShop.code),
      categoryStore.fetchCategories(shopStore.currentShop.id)
    ])
  }
})

const editMenu = (menu: Menu) => {
  editingMenu.value = menu
  formData.value = {
    number: menu.number,
    name: menu.name,
    description: menu.description || '',
    price: menu.price,
    category: menu.category,
    isAvailable: menu.isAvailable,
    isRecommended: menu.isRecommended || false
  }
  showCreateModal.value = true
}

const saveMenu = async () => {
  if (!shopStore.currentShop) {
    alert('店舗が選択されていません')
    return
  }

  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    
    if (!token) {
      throw new Error('認証トークンが見つかりません')
    }

    // TODO: メニュー作成・更新APIが実装されたら使用
    // 現在はAPIが存在しないため、エラーメッセージを表示
    alert('メニューの作成・更新機能は現在開発中です。APIの実装が必要です。')
    
    // 将来的な実装例:
    // if (editingMenu.value) {
    //   await $fetch(`${apiBase}/menus/${editingMenu.value.id}`, {
    //     method: 'PUT',
    //     body: formData.value,
    //     headers: {
    //       'Content-Type': 'application/json',
    //       'Accept': 'application/json',
    //       'Authorization': `Bearer ${token}`
    //   })
    // } else {
    //   await $fetch(`${apiBase}/menus`, {
    //     method: 'POST',
    //     body: { ...formData.value, shopId: shopStore.currentShop.id },
    //     headers: {
    //       'Content-Type': 'application/json',
    //       'Accept': 'application/json',
    //       'Authorization': `Bearer ${token}`
    //   })
    // }
    
    // await menuStore.fetchMenus(shopStore.currentShop.code)
    // closeModal()
  } catch (error: any) {
    alert(error.message || 'メニューの保存に失敗しました')
  }
}

const deleteMenu = async (menu: Menu) => {
  if (!confirm(`メニュー「${menu.name}」を削除してもよろしいですか？`)) {
    return
  }

  try {
    // TODO: メニュー削除APIが実装されたら使用
    alert('メニューの削除機能は現在開発中です。APIの実装が必要です。')
    
    // 将来的な実装例:
    // const config = useRuntimeConfig()
    // const apiBase = config.public.apiBase
    // const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    // 
    // if (!token) {
    //   throw new Error('認証トークンが見つかりません')
    // }
    // 
    // await $fetch(`${apiBase}/menus/${menu.id}`, {
    //   method: 'DELETE',
    //   headers: {
    //     'Accept': 'application/json',
    //     'Authorization': `Bearer ${token}`
    //   }
    // })
    // 
    // if (shopStore.currentShop) {
    //   await menuStore.fetchMenus(shopStore.currentShop.code)
    // }
  } catch (error: any) {
    alert(error.message || 'メニューの削除に失敗しました')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingMenu.value = null
  formData.value = {
    number: '',
    name: '',
    description: '',
    price: 0,
    category: '',
    isAvailable: true,
    isRecommended: false
  }
}
</script>

