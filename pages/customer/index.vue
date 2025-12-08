<template>
  <NuxtLayout name="default" title="メニュー一覧">
    <div class="space-y-6">
      <!-- 席情報表示 -->
      <div v-if="cartStore.tableNumber" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-blue-600 font-medium mb-1">ご利用中の席</div>
            <div class="flex items-center gap-3">
              <div class="text-xl font-bold text-gray-900">
                テーブル {{ cartStore.tableNumber }}
              </div>
              <div v-if="currentTableInfo?.name" class="text-sm text-gray-600">
                {{ currentTableInfo.name }}
              </div>
              <div v-if="currentTableInfo?.capacity" class="text-sm text-gray-500">
                （定員: {{ currentTableInfo.capacity }}名）
              </div>
            </div>
          </div>
          <NuxtLink
            to="/shop-select"
            class="text-sm text-blue-600 hover:text-blue-700 underline"
          >
            変更
          </NuxtLink>
        </div>
      </div>

      <!-- 番号入力 -->
      <NumberInput />

      <!-- カテゴリフィルター -->
      <div v-if="isLoadingCategories" class="flex gap-2 overflow-x-auto pb-2">
        <div class="px-4 py-2 rounded-lg bg-gray-100 animate-pulse">読み込み中...</div>
      </div>
      <div v-else class="flex gap-2 overflow-x-auto pb-2">
        <button
          :class="[
            'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors touch-target',
            menuStore.selectedCategory === null
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          @click="menuStore.setCategory(null)"
        >
          すべて
        </button>
        <button
          v-for="category in shopCategories"
          :key="category.code"
          :class="[
            'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors touch-target',
            menuStore.selectedCategory === category.code
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          @click="menuStore.setCategory(category.code)"
        >
          {{ category.name }}
        </button>
      </div>

      <!-- メニュー一覧 -->
      <div v-if="menuStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <MenuCard
          v-for="menu in menuStore.menusByCategory"
          :key="menu.id"
          :menu="menu"
        />
      </div>

      <!-- カートボタン -->
      <div v-if="!cartStore.isEmpty" class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4">
        <NuxtLink
          to="/customer/cart"
          class="block w-full bg-blue-600 text-white py-4 rounded-lg text-center text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
        >
          カートを見る ({{ cartStore.totalItems }}点 / ¥{{ cartStore.totalAmount.toLocaleString() }})
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useCartStore } from '~/stores/cart'
import { useShopStore } from '~/stores/shop'
import { useTableStore } from '~/stores/table'
import { useCategoryStore } from '~/stores/category'
import type { ShopTable, ShopCategory } from '~/types'
import MenuCard from '~/components/MenuCard.vue'
import NumberInput from '~/components/NumberInput.vue'

const menuStore = useMenuStore()
const cartStore = useCartStore()
const shopStore = useShopStore()
const tableStore = useTableStore()
const categoryStore = useCategoryStore()

const currentTableInfo = ref<ShopTable | null>(null)
const shopCategories = ref<ShopCategory[]>([])
const isLoadingCategories = ref(false)

onMounted(async () => {
  const route = useRoute()
  
  // QRコードから来た場合の処理（GETパラメータから店舗コードとテーブル番号を取得）
  const shopCodeFromQuery = route.query.shop as string || null
  const tableNumberFromQuery = route.query.table as string || null
  
  if (shopCodeFromQuery && tableNumberFromQuery) {
    // QRコードから来た場合
    try {
      // 店舗を取得
      const shop = await shopStore.fetchShopByCode(shopCodeFromQuery)
      if (shop) {
        shopStore.setCurrentShop(shop)
        
        // テーブル情報を検証して設定
        try {
          const tableInfo = await tableStore.fetchTableByQRCode(shopCodeFromQuery, tableNumberFromQuery)
          cartStore.setTableNumber(tableInfo.tableNumber)
          currentTableInfo.value = tableInfo
        } catch (error) {
          // 検証に失敗してもテーブル番号は設定する
          cartStore.setTableNumber(tableNumberFromQuery)
          currentTableInfo.value = null
        }
      } else {
        // 店舗が見つからない場合は店舗選択ページにリダイレクト
        await navigateTo('/shop-select')
        return
      }
    } catch (error) {
      console.error('QRコードからの店舗情報取得に失敗しました:', error)
      await navigateTo('/shop-select')
      return
    }
  } else {
    // QRコードから来ていない場合：ストレージから店舗を読み込み
    shopStore.loadShopFromStorage()
    
    // 店舗が選択されていない場合は店舗選択ページにリダイレクト
    if (!shopStore.currentShop) {
      await navigateTo('/shop-select')
      return
    }
    
    // テーブル番号が設定されていない場合は店舗選択ページにリダイレクト
    if (!cartStore.tableNumber) {
      await navigateTo('/shop-select')
      return
    }
    
    // テーブル情報を取得
    try {
      const tableInfo = await tableStore.fetchTableByQRCode(
        shopStore.currentShop.code,
        cartStore.tableNumber
      )
      currentTableInfo.value = tableInfo
    } catch (error) {
      console.error('テーブル情報の取得に失敗しました:', error)
      // エラーが発生してもテーブル番号だけは表示する
      currentTableInfo.value = null
    }
  }
  
  // 店舗が設定されていることを確認
  if (!shopStore.currentShop) {
    await navigateTo('/shop-select')
    return
  }
  
  // 店舗独自カテゴリを取得
  isLoadingCategories.value = true
  try {
    const categories = await categoryStore.fetchCategoriesByShopCode(shopStore.currentShop.code)
    shopCategories.value = categories
  } catch (error) {
    console.error('カテゴリ一覧の取得に失敗しました:', error)
    // エラーが発生してもメニューは表示する
    shopCategories.value = []
  } finally {
    isLoadingCategories.value = false
  }
  
  // メニューを取得（店舗IDを含める）
  await menuStore.fetchMenus(shopStore.currentShop.code)
})
</script>

