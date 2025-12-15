<template>
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-1">メニュー管理</h1>
          <p class="text-gray-600">全店舗のメニューを一元管理</p>
        </div>
      </div>

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
                v-for="shop in allShops"
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
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- メニュー一覧 -->
      <div v-else-if="filteredMenus.length === 0" class="text-center py-12 text-gray-500 bg-white rounded-lg shadow">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <p class="text-gray-500 font-medium">メニューがありません</p>
      </div>

      <div v-else class="space-y-4">
        <!-- 店舗ごとのアコーディオン -->
        <div
          v-for="shop in displayedShops"
          :key="shop.id"
          class="bg-white rounded-lg shadow"
        >
          <!-- 見出し（クリックで開閉） -->
          <button
            type="button"
            class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 rounded-t-lg"
            @click="toggleShop(shop.id)"
          >
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 text-sm font-semibold">
                {{ groupedMenus[shop.id]?.length || 0 }}
              </span>
              <div class="text-left">
                <p class="font-semibold text-gray-900">{{ shop.name }}</p>
                <p class="text-xs text-gray-500">
                  この店舗のメニュー一覧
                </p>
              </div>
            </div>
            <svg
              class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
              :class="isShopOpen(shop.id) ? 'rotate-180' : ''"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- 本文（メニュー一覧） -->
          <div
            v-if="isShopOpen(shop.id)"
            class="border-t px-4 py-3 space-y-3 rounded-b-lg"
          >
            <div
              v-for="menu in groupedMenus[shop.id]"
              :key="menu.id"
              class="p-3 rounded-lg border border-gray-100 hover:border-green-300 hover:shadow-sm transition"
            >
              <div class="flex justify-between items-start mb-1">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span
                      v-if="menu.isRecommended"
                      class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs"
                    >
                      おすすめ
                    </span>
                    <span
                      class="px-2 py-0.5 rounded text-xs"
                      :class="menu.isAvailable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    >
                      {{ menu.isAvailable ? '利用可能' : '利用不可' }}
                    </span>
                  </div>
                  <h3 class="font-semibold text-sm md:text-base">
                    {{ menu.number }}. {{ menu.name }}
                  </h3>
                </div>
                <p class="text-sm md:text-base font-bold text-green-600 ml-3">
                  ¥{{ menu.price.toLocaleString() }}
                </p>
              </div>
              <p
                v-if="menu.description"
                class="text-xs md:text-sm text-gray-600"
              >
                {{ menu.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { $fetch as ofetch } from 'ofetch'
import { useMenuStore } from '~/stores/menu'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Shop, Menu } from '~/types'

definePageMeta({
  layout: 'company'
})

const authStore = useAuthStore()
const shopStore = useShopStore()
const menuStore = useMenuStore()

const { handleLogout } = useAuthCheck()

const allShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const selectedCategory = ref<string>('')
const allMenus = ref<Menu[]>([])
const filteredMenus = ref<Menu[]>([])

// 店舗ごとの開閉状態
const openShopIds = ref<string[]>([])

// 絞り込み後のメニューを店舗ごとにグルーピング
const groupedMenus = computed<Record<string, Menu[]>>(() => {
  const map: Record<string, Menu[]> = {}

  for (const menu of filteredMenus.value) {
    const shopId = (menu as any).shopId as string | undefined
    if (!shopId) continue
    if (!map[shopId]) {
      map[shopId] = []
    }
    map[shopId].push(menu)
  }

  // 店舗内で番号順に並べ替え
  Object.values(map).forEach(list => {
    list.sort((a, b) => a.number.localeCompare(b.number))
  })

  return map
})

// 表示対象となる店舗（メニューを1件以上持つ店舗）
const displayedShops = computed<Shop[]>(() => {
  return allShops.value.filter(shop => groupedMenus.value[shop.id]?.length)
})

const isShopOpen = (shopId: string) => openShopIds.value.includes(shopId)

const toggleShop = (shopId: string) => {
  if (isShopOpen(shopId)) {
    openShopIds.value = openShopIds.value.filter(id => id !== shopId)
  } else {
    openShopIds.value.push(shopId)
  }
}
const isLoading = ref(false)

const getShopName = (shopId: string) => {
  const shop = allShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const filterMenus = () => {
  let menus = allMenus.value

  if (selectedShopId.value) {
    menus = menus.filter(m => m.shopId === selectedShopId.value)
  }

  if (selectedCategory.value) {
    menus = menus.filter(m => m.category === selectedCategory.value)
  }

  // 絞り込み結果を反映（ソートは店舗ごとに行うためここでは行わない）
  filteredMenus.value = menus

  // フィルター変更時はすべて折りたたむ
  openShopIds.value = []
}

const fetchAllMenus = async () => {
  isLoading.value = true
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    
    // 各店舗のメニューを取得（店舗IDをフロント側で補完）
    const menuPromises = allShops.value.map(async (shop) => {
      try {
        // 運営者向け画面では JWT の shop_id を無視して、
        // クエリパラメータの shop=コード だけでメニューを取得したいので
        // Authorization ヘッダーを自動付与する $fetch ではなく ofetch を直接利用する
        const menus = await ofetch<Menu[]>(`${apiBase}/menus?shop=${shop.code}`)
        // APIレスポンスに shopId が無い場合でも、この画面用に補完しておく
        return (menus || []).map(menu => ({
          ...menu,
          shopId: menu.shopId || shop.id
        }))
      } catch (e) {
        console.error(`店舗「${shop.name}」のメニュー取得に失敗しました:`, e)
        return []
      }
    })
    
    const menuArrays = await Promise.all(menuPromises)
    allMenus.value = menuArrays.flat()
    filterMenus()
  } catch (error) {
    console.error('メニューの取得に失敗しました:', error)
    allMenus.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/unei/login')
    return
  }

  // 全店舗を取得
  try {
    await shopStore.fetchShops()
    allShops.value = shopStore.shops
    
    // 全店舗のメニューを取得
    await fetchAllMenus()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

