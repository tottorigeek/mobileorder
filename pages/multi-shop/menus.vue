<template>
  <NuxtLayout name="default" title="メニュー管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

      <!-- フィルター -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗で絞り込み
            </label>
            <select
              v-model="selectedShopId"
              @change="filterMenus"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべての店舗</option>
              <option
                v-for="shop in myShops"
                :key="shop.id"
                :value="shop.id"
              >
                {{ shop.name }}
              </option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              カテゴリで絞り込み
            </label>
            <select
              v-model="selectedCategory"
              @change="filterMenus"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="food">フード</option>
              <option value="drink">ドリンク</option>
              <option value="dessert">デザート</option>
              <option value="other">その他</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="menuStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- メニュー一覧 -->
      <div v-else-if="filteredMenus.length === 0" class="text-center py-12 text-gray-500">
        メニューがありません
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="menu in filteredMenus"
          :key="menu.id"
          class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="flex justify-between items-start mb-2">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                  {{ getShopName(menu.shopId) }}
                </span>
                <span
                  v-if="menu.isRecommended"
                  class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs"
                >
                  おすすめ
                </span>
              </div>
              <h3 class="font-semibold text-lg">{{ menu.number }}. {{ menu.name }}</h3>
            </div>
          </div>
          <p v-if="menu.description" class="text-sm text-gray-600 mb-2">{{ menu.description }}</p>
          <div class="flex justify-between items-center">
            <p class="text-lg font-bold text-green-600">¥{{ menu.price.toLocaleString() }}</p>
            <span
              :class="menu.isAvailable ? 'px-2 py-1 bg-green-100 text-green-800 rounded text-xs' : 'px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs'"
            >
              {{ menu.isAvailable ? '利用可能' : '利用不可' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Shop, Menu } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()
const menuStore = useMenuStore()

const myShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const selectedCategory = ref<string>('')
const allMenus = ref<Menu[]>([])
const filteredMenus = ref<Menu[]>([])

const { navigationItems } = useMultiShopNavigation()
const { handleLogout } = useAuthCheck()

const filterMenus = () => {
  let menus = allMenus.value

  if (selectedShopId.value) {
    menus = menus.filter(m => m.shopId === selectedShopId.value)
  }

  if (selectedCategory.value) {
    menus = menus.filter(m => m.category === selectedCategory.value)
  }

  filteredMenus.value = menus.sort((a, b) => {
    if (a.shopId !== b.shopId) {
      return a.shopId.localeCompare(b.shopId)
    }
    return a.number.localeCompare(b.number)
  })
}

const getShopName = (shopId: string) => {
  const shop = myShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const fetchAllMenus = async () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  
  try {
    // 各店舗のメニューを取得
    const menuPromises = myShops.value.map(shop => 
      $fetch<Menu[]>(`${apiBase}/menus?shop=${shop.code}`).catch(() => [])
    )
    
    const menuArrays = await Promise.all(menuPromises)
    allMenus.value = menuArrays.flat()
    filterMenus()
  } catch (error) {
    console.error('メニューの取得に失敗しました:', error)
    allMenus.value = []
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }

  // 所属店舗一覧を取得
  try {
    myShops.value = await shopStore.fetchMyShops()
    
    if (myShops.value.length === 0) {
      await navigateTo('/shop/dashboard')
      return
    }

    // 全店舗のメニューを取得
    await fetchAllMenus()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

