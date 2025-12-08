<template>
  <NuxtLayout name="default" title="メニュー一覧">
    <div class="space-y-6">
      <!-- テーブル番号入力 -->
      <div class="bg-white p-4 rounded-lg shadow">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          テーブル番号
        </label>
        <input
          v-model="tableNumber"
          type="text"
          placeholder="テーブル番号を入力"
          class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          @input="cartStore.setTableNumber(tableNumber)"
        />
      </div>

      <!-- 番号入力 -->
      <NumberInput />

      <!-- カテゴリフィルター -->
      <div class="flex gap-2 overflow-x-auto pb-2">
        <button
          v-for="category in categories"
          :key="category.value"
          :class="[
            'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors touch-target',
            menuStore.selectedCategory === category.value
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          @click="menuStore.setCategory(category.value)"
        >
          {{ category.label }}
        </button>
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
import MenuCard from '~/components/MenuCard.vue'
import NumberInput from '~/components/NumberInput.vue'

const menuStore = useMenuStore()
const cartStore = useCartStore()

const tableNumber = ref('')

const categories = [
  { value: 'food', label: '食べ物' },
  { value: 'drink', label: '飲み物' },
  { value: 'dessert', label: 'デザート' }
]

const route = useRoute()
const shopStore = useShopStore()

onMounted(async () => {
  // 店舗コードの取得（クエリパラメータまたはストレージから）
  const shopCode = route.query.shop as string || null
  const tableParam = route.query.table as string || null
  
  if (shopCode) {
    // クエリパラメータから店舗を取得
    await shopStore.fetchShopByCode(shopCode)
    
    // QRコードからテーブル番号が指定されている場合
    if (tableParam) {
      try {
        // QRコードからテーブル情報を取得して検証
        const tableStore = useTableStore()
        const tableInfo = await tableStore.fetchTableByQRCode(shopCode, tableParam)
        
        // テーブル番号を設定
        tableNumber.value = tableInfo.tableNumber
        cartStore.setTableNumber(tableInfo.tableNumber)
      } catch (error) {
        console.error('QRコードからのテーブル情報取得に失敗しました:', error)
        // エラーが発生してもテーブル番号は設定する（QRコードが無効でも利用可能にする）
        tableNumber.value = tableParam
        cartStore.setTableNumber(tableParam)
      }
    }
  } else {
    // ストレージから店舗を読み込み
    shopStore.loadShopFromStorage()
  }
  
  // 店舗が選択されていない場合は店舗選択ページにリダイレクト
  if (!shopStore.currentShop) {
    await navigateTo('/shop-select')
    return
  }
  
  // メニューを取得（店舗IDを含める）
  await menuStore.fetchMenus(shopStore.currentShop.code)
  
  // テーブル番号がまだ設定されていない場合はストレージから読み込む
  if (!tableNumber.value) {
    tableNumber.value = cartStore.tableNumber
  }
})
</script>

