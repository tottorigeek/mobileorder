<template>
  <NuxtLayout name="default" title="店舗選択" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gray-50">
      <div class="w-full max-w-md">
        <!-- 店舗選択画面 -->
        <div v-if="!selectedShop">
          <h1 class="text-3xl font-bold text-center mb-8 text-gray-900">
            店舗を選択してください
          </h1>

          <div v-if="shopStore.isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-500">読み込み中...</p>
          </div>

          <div v-else-if="shopStore.shops.length === 0" class="text-center py-12">
            <p class="text-gray-500 mb-4">店舗が見つかりません</p>
          </div>

          <div v-else class="space-y-3">
            <button
              v-for="shop in shopStore.shops"
              :key="shop.id"
              @click="selectShop(shop)"
              class="w-full p-6 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left touch-target"
            >
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ shop.name }}</h3>
              <p v-if="shop.description" class="text-gray-600 text-sm mb-2">{{ shop.description }}</p>
              <p v-if="shop.address" class="text-gray-500 text-xs">{{ shop.address }}</p>
            </button>
          </div>

          <div class="mt-6 text-center">
            <NuxtLink
              to="/"
              class="text-blue-600 hover:text-blue-700"
            >
              トップに戻る
            </NuxtLink>
          </div>
        </div>

        <!-- テーブル番号入力画面 -->
        <div v-else class="space-y-6">
          <div>
            <button
              @click="selectedShop = null"
              class="text-blue-600 hover:text-blue-700 mb-4 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              店舗選択に戻る
            </button>
            <h1 class="text-3xl font-bold text-center mb-2 text-gray-900">
              {{ selectedShop.name }}
            </h1>
            <p class="text-center text-gray-600 mb-8">テーブル番号を入力してください</p>
          </div>

          <div class="bg-white p-6 rounded-lg shadow">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              テーブル番号 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="tableNumber"
              type="text"
              placeholder="テーブル番号を入力"
              class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              @keyup.enter="confirmTable"
            />
            <p v-if="errorMessage" class="mt-2 text-sm text-red-500">
              {{ errorMessage }}
            </p>
            <p v-if="isValidating" class="mt-2 text-sm text-gray-500">
              検証中...
            </p>
          </div>

          <button
            @click="confirmTable"
            :disabled="!tableNumber || isValidating"
            class="w-full bg-blue-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
          >
            {{ isValidating ? '検証中...' : '確定' }}
          </button>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useCartStore } from '~/stores/cart'
import { useTableStore } from '~/stores/table'
import type { Shop } from '~/types'

const shopStore = useShopStore()
const cartStore = useCartStore()
const tableStore = useTableStore()
const router = useRouter()
const route = useRoute()
const mode = route.query.mode as string || 'customer'

const selectedShop = ref<Shop | null>(null)
const tableNumber = ref('')
const errorMessage = ref('')
const isValidating = ref(false)

onMounted(async () => {
  await shopStore.fetchShops()
  
  // QRコードから来た場合の処理（GETパラメータから店舗コードとテーブル番号を取得）
  const shopCodeFromQuery = route.query.shop as string || null
  const tableNumberFromQuery = route.query.table as string || null
  
  if (shopCodeFromQuery) {
    try {
      // 店舗を取得
      const shop = await shopStore.fetchShopByCode(shopCodeFromQuery)
      if (shop) {
        selectedShop.value = shop
        
        // テーブル番号が指定されている場合は自動設定
        if (tableNumberFromQuery) {
          tableNumber.value = tableNumberFromQuery
          // テーブル情報を検証して設定
          try {
            const tableInfo = await tableStore.fetchTableByQRCode(shopCodeFromQuery, tableNumberFromQuery)
            cartStore.setTableNumber(tableInfo.tableNumber)
          } catch (error) {
            // 検証に失敗してもテーブル番号は設定する
            cartStore.setTableNumber(tableNumberFromQuery)
          }
        }
      }
    } catch (error) {
      console.error('QRコードからの店舗情報取得に失敗しました:', error)
    }
  } else {
    // 既に店舗とテーブル番号が設定されている場合はスキップ
    shopStore.loadShopFromStorage()
    if (shopStore.currentShop && cartStore.tableNumber) {
      // 既に設定済みの場合は顧客ページへ
      if (mode === 'staff') {
        router.push('/staff/login')
      } else {
        router.push('/customer')
      }
    }
  }
})

const selectShop = (shop: Shop) => {
  selectedShop.value = shop
  shopStore.setCurrentShop(shop)
  tableNumber.value = ''
  errorMessage.value = ''
}

const confirmTable = async () => {
  if (!tableNumber.value.trim()) {
    errorMessage.value = 'テーブル番号を入力してください'
    return
  }

  if (!selectedShop.value) {
    return
  }

  isValidating.value = true
  errorMessage.value = ''

  try {
    // QRコードAPIでテーブル情報を検証
    const tableInfo = await tableStore.fetchTableByQRCode(selectedShop.value.code, tableNumber.value.trim())
    
    // テーブル番号をカートストアに保存
    cartStore.setTableNumber(tableInfo.tableNumber)
    
    // 店舗情報をローカルストレージに保存
    if (typeof window !== 'undefined') {
      localStorage.setItem('selected_shop', JSON.stringify(selectedShop.value))
    }
    
    // 顧客ページへ遷移（GETパラメータなし）
    if (mode === 'staff') {
      router.push('/staff/login')
    } else {
      router.push('/customer')
    }
  } catch (error: any) {
    console.error('テーブル情報の検証に失敗しました:', error)
    // エラーが発生してもテーブル番号は設定する（QRコードが無効でも利用可能にする）
    cartStore.setTableNumber(tableNumber.value.trim())
    
    if (typeof window !== 'undefined') {
      localStorage.setItem('selected_shop', JSON.stringify(selectedShop.value))
    }
    
    // エラーがあっても進める（テーブル番号が存在しない場合でも利用可能にする）
    if (mode === 'staff') {
      router.push('/staff/login')
    } else {
      router.push('/customer')
    }
  } finally {
    isValidating.value = false
  }
}
</script>

