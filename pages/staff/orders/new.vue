<template>
  <NuxtLayout name="default" title="新規注文">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />

      <!-- テーブル選択 -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          テーブル選択
        </h2>
        <div v-if="tableStore.isLoading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-200 border-t-blue-600"></div>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
          <button
            v-for="table in availableTables"
            :key="table.id"
            @click="selectTable(table)"
            :class="[
              'p-4 rounded-xl border-2 transition-all duration-300 touch-target transform hover:scale-105',
              selectedTable?.id === table.id
                ? 'border-purple-600 bg-gradient-to-br from-purple-50 to-indigo-50 text-purple-700 shadow-md'
                : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50'
            ]"
          >
            <div class="font-bold text-xl mb-1">{{ table.tableNumber }}</div>
            <div v-if="table.name" class="text-sm text-gray-600">{{ table.name }}</div>
            <div v-if="table.capacity" class="text-xs text-gray-500 mt-2">
              {{ table.capacity }}名
            </div>
          </button>
        </div>
        <p v-if="!selectedTable" class="mt-4 text-sm text-gray-500">
          テーブルを選択してください
        </p>
        <p v-else class="mt-4 text-sm font-semibold text-purple-600">
          選択中: テーブル {{ selectedTable.tableNumber }}
        </p>
      </div>

      <!-- メニュー検索 -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          メニュー検索
        </h2>
        
        <!-- 検索タブ -->
        <div class="flex gap-2 mb-4 overflow-x-auto pb-2 scrollbar-hide">
          <button
            v-for="tab in searchTabs"
            :key="tab.id"
            @click="activeSearchTab = tab.id"
            :class="[
              'px-4 py-2 rounded-lg font-semibold whitespace-nowrap transition-all duration-300',
              activeSearchTab === tab.id
                ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- 番号検索 -->
        <div v-if="activeSearchTab === 'number'" class="mb-4">
          <input
            v-model="searchNumber"
            type="text"
            placeholder="メニュー番号を入力（例: 001）"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            @input="searchByNumber"
          />
        </div>

        <!-- キーワード検索 -->
        <div v-if="activeSearchTab === 'keyword'" class="mb-4">
          <input
            v-model="searchKeyword"
            type="text"
            placeholder="メニュー名や説明で検索"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            @input="searchByKeyword"
          />
        </div>

        <!-- カテゴリ検索 -->
        <div v-if="activeSearchTab === 'category'" class="mb-4">
          <select
            v-model="selectedCategory"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            @change="searchByCategory"
          >
            <option value="">すべてのカテゴリ</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.code"
            >
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- 検索結果 -->
        <div v-if="filteredMenus.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
          <div
            v-for="menu in filteredMenus"
            :key="menu.id"
            class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-all duration-300"
          >
            <div class="flex justify-between items-start mb-2">
              <div>
                <div class="font-bold text-lg text-gray-900">{{ menu.name }}</div>
                <div class="text-sm text-gray-500">No. {{ menu.number }}</div>
              </div>
              <div class="text-xl font-bold text-purple-600">¥{{ menu.price.toLocaleString() }}</div>
            </div>
            <p v-if="menu.description" class="text-sm text-gray-600 mb-3">{{ menu.description }}</p>
            <div class="flex items-center justify-between">
              <span
                :class="[
                  'px-2 py-1 rounded text-xs font-semibold',
                  menu.isAvailable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]"
              >
                {{ menu.isAvailable ? '利用可能' : '利用不可' }}
              </span>
              <button
                v-if="menu.isAvailable"
                @click="addToCart(menu)"
                class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-300 font-semibold"
              >
                カートに追加
              </button>
            </div>
          </div>
        </div>
        <div v-else-if="hasSearched" class="text-center py-8 text-gray-500">
          検索結果がありません
        </div>
      </div>

      <!-- カート -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          カート
        </h2>
        <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
          カートは空です
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="(item, index) in cartItems"
            :key="index"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div class="flex-1">
              <div class="font-semibold text-gray-900">{{ item.menu.name }}</div>
              <div class="text-sm text-gray-600">No. {{ item.menu.number }} × {{ item.quantity }}</div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-lg font-bold text-purple-600">¥{{ (item.menu.price * item.quantity).toLocaleString() }}</div>
              <div class="flex items-center gap-2">
                <button
                  @click="decreaseQuantity(index)"
                  class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>
                <span class="w-8 text-center font-semibold">{{ item.quantity }}</span>
                <button
                  @click="increaseQuantity(index)"
                  class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
                <button
                  @click="removeFromCart(index)"
                  class="ml-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm"
                >
                  削除
                </button>
              </div>
            </div>
          </div>
          <div class="pt-4 border-t border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <span class="text-xl font-bold text-gray-900">合計</span>
              <span class="text-2xl font-bold text-purple-600">¥{{ totalAmount.toLocaleString() }}</span>
            </div>
            <button
              @click="submitOrder"
              :disabled="!selectedTable || cartItems.length === 0 || isSubmitting"
              class="w-full py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold text-lg"
            >
              {{ isSubmitting ? '注文中...' : '注文を確定' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useCategoryStore } from '~/stores/category'
import { useTableStore } from '~/stores/table'
import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Menu, ShopTable, ShopCategory } from '~/types'

const { navigationItems } = useShopNavigation()
const { checkAuth } = useAuthCheck()

const menuStore = useMenuStore()
const categoryStore = useCategoryStore()
const tableStore = useTableStore()
const orderStore = useOrderStore()
const authStore = useAuthStore()
const shopStore = useShopStore()

const selectedTable = ref<ShopTable | null>(null)
const searchTabs = [
  { id: 'number', label: '番号検索' },
  { id: 'keyword', label: 'キーワード検索' },
  { id: 'category', label: 'カテゴリ検索' }
]
const activeSearchTab = ref('number')
const searchNumber = ref('')
const searchKeyword = ref('')
const selectedCategory = ref('')
const hasSearched = ref(false)
const cartItems = ref<Array<{ menu: Menu; quantity: number }>>([])
const isSubmitting = ref(false)

// 利用可能なテーブル（着座済みでないテーブル）
const availableTables = computed(() => {
  return tableStore.tables.filter(table => 
    table.isActive && 
    !table.visitorId && 
    (!table.status || table.status === 'available')
  )
})

// カテゴリ一覧
const categories = computed(() => categoryStore.activeCategories)

// フィルタリングされたメニュー
const filteredMenus = computed(() => {
  let menus = menuStore.menus.filter(menu => menu.isAvailable)

  if (activeSearchTab.value === 'number' && searchNumber.value) {
    const menu = menuStore.getMenuByNumber(searchNumber.value)
    return menu ? [menu] : []
  }

  if (activeSearchTab.value === 'keyword' && searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    return menus.filter(menu => 
      menu.name.toLowerCase().includes(keyword) ||
      (menu.description && menu.description.toLowerCase().includes(keyword))
    )
  }

  if (activeSearchTab.value === 'category' && selectedCategory.value) {
    return menus.filter(menu => menu.category === selectedCategory.value)
  }

  // 検索条件がない場合は空配列
  return []
})

// 合計金額
const totalAmount = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.menu.price * item.quantity), 0)
})

// テーブル選択
const selectTable = (table: ShopTable) => {
  selectedTable.value = table
}

// 番号検索
const searchByNumber = () => {
  hasSearched.value = true
}

// キーワード検索
const searchByKeyword = () => {
  hasSearched.value = true
}

// カテゴリ検索
const searchByCategory = () => {
  hasSearched.value = true
}

// カートに追加
const addToCart = (menu: Menu) => {
  const existingItem = cartItems.value.find(item => item.menu.id === menu.id)
  if (existingItem) {
    existingItem.quantity++
  } else {
    cartItems.value.push({ menu, quantity: 1 })
  }
}

// 数量を増やす
const increaseQuantity = (index: number) => {
  cartItems.value[index].quantity++
}

// 数量を減らす
const decreaseQuantity = (index: number) => {
  if (cartItems.value[index].quantity > 1) {
    cartItems.value[index].quantity--
  } else {
    removeFromCart(index)
  }
}

// カートから削除
const removeFromCart = (index: number) => {
  cartItems.value.splice(index, 1)
}

// 注文を確定
const submitOrder = async () => {
  if (!selectedTable.value || cartItems.value.length === 0) {
    return
  }

  if (!shopStore.currentShop) {
    alert('店舗が選択されていません')
    return
  }

  isSubmitting.value = true

  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null

    const orderItems = cartItems.value.map(item => ({
      menuId: item.menu.id,
      menuNumber: item.menu.number,
      menuName: item.menu.name,
      quantity: item.quantity,
      price: item.menu.price
    }))

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    await $fetch(`${apiBase}/orders?shop=${shopStore.currentShop.code}`, {
      method: 'POST',
      body: {
        tableNumber: selectedTable.value.tableNumber,
        items: orderItems,
        totalAmount: totalAmount.value
      },
      headers
    })

    // 成功メッセージを表示して注文一覧に戻る
    alert('注文が確定されました')
    await navigateTo('/staff/orders')
  } catch (error: any) {
    console.error('注文の作成に失敗しました:', error)
    alert('注文の作成に失敗しました: ' + (error.message || 'エラーが発生しました'))
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  authStore.loadUserFromStorage()
  shopStore.loadShopFromStorage()

  // 店舗が選択されていない場合は店舗選択画面にリダイレクト
  if (!shopStore.currentShop) {
    if (authStore.user?.shop) {
      shopStore.setCurrentShop(authStore.user.shop)
    } else {
      await navigateTo('/staff/shop-select')
      return
    }
  }

  // データの取得
  if (shopStore.currentShop) {
    await Promise.all([
      menuStore.fetchMenus(shopStore.currentShop.code),
      categoryStore.fetchCategoriesByShopCode(shopStore.currentShop.code),
      tableStore.fetchTables(shopStore.currentShop.id)
    ])
  }
})
</script>

