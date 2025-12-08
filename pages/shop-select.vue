<template>
  <NuxtLayout name="default" title="店舗選択" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      <div class="w-full max-w-md">
        <!-- 店舗選択画面 -->
        <div v-if="!selectedShop">
          <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              店舗を選択してください
            </h1>
          </div>

          <div v-if="shopStore.isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
            <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
          </div>

          <div v-else-if="shopStore.shops.length === 0" class="text-center py-12 bg-white rounded-2xl shadow-lg p-8">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="text-gray-500 font-medium">店舗が見つかりません</p>
          </div>

          <div v-else class="space-y-4">
            <button
              v-for="shop in shopStore.shops"
              :key="shop.id"
              @click="selectShop(shop)"
              class="w-full p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 text-left touch-target border-2 border-transparent hover:border-blue-300 transform hover:-translate-y-1"
            >
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div class="flex-1">
                  <h3 class="text-xl font-bold text-gray-900 mb-1">{{ shop.name }}</h3>
                  <p v-if="shop.description" class="text-gray-600 text-sm mb-2">{{ shop.description }}</p>
                  <p v-if="shop.address" class="text-gray-500 text-xs flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ shop.address }}
                  </p>
                </div>
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </button>
          </div>

          <div class="mt-8 text-center">
            <NuxtLink
              to="/"
              class="text-blue-600 hover:text-blue-700 font-medium transition-colors inline-flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              トップに戻る
            </NuxtLink>
          </div>
        </div>

        <!-- テーブル番号選択画面 -->
        <div v-else class="space-y-6">
          <div>
            <button
              @click="selectedShop = null"
              class="text-blue-600 hover:text-blue-700 mb-6 flex items-center gap-2 font-medium transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              店舗選択に戻る
            </button>
            <div class="text-center mb-8">
              <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                {{ selectedShop.name }}
              </h1>
              <p class="text-gray-600">テーブル番号を選択してください</p>
            </div>
          </div>

          <div v-if="isLoadingTables" class="bg-white p-8 rounded-2xl shadow-lg text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
            <p class="mt-4 text-gray-500 font-medium">テーブル一覧を読み込み中...</p>
          </div>

          <div v-else-if="availableTables.length === 0" class="bg-white p-8 rounded-2xl shadow-lg text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <p class="text-gray-500 font-medium">利用可能なテーブルがありません</p>
          </div>

          <div v-else class="bg-white p-6 rounded-2xl shadow-lg">
            <label class="block text-sm font-semibold text-gray-700 mb-4">
              テーブル番号 <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="table in availableTables"
                :key="table.id"
                @click="selectTable(table)"
                :class="[
                  'p-5 rounded-xl border-2 transition-all duration-300 touch-target transform hover:scale-105',
                  tableNumber === table.tableNumber
                    ? 'border-blue-600 bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-700 shadow-md'
                    : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'
                ]"
              >
                <div class="font-bold text-2xl mb-1">{{ table.tableNumber }}</div>
                <div v-if="table.name" class="text-sm text-gray-600 font-medium">{{ table.name }}</div>
                <div v-if="table.capacity" class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {{ table.capacity }}名
                </div>
              </button>
            </div>
            <p v-if="errorMessage" class="mt-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg text-sm text-red-700">
              {{ errorMessage }}
            </p>
          </div>

          <button
            @click="confirmTable"
            :disabled="!tableNumber || isValidating"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl text-lg font-semibold hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target"
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
import { useVisitorStore } from '~/stores/visitor'
import type { Shop, ShopTable } from '~/types'

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
const availableTables = ref<ShopTable[]>([])
const isLoadingTables = ref(false)

onMounted(async () => {
  // 精算完了前の注文がある場合は/customerにリダイレクト
  if (typeof window !== 'undefined') {
    const activeOrderId = localStorage.getItem('activeOrderId')
    const activeVisitorId = localStorage.getItem('activeVisitorId')
    
    if (activeOrderId && activeVisitorId) {
      // visitorの支払いステータスを確認
      try {
        const visitorStore = useVisitorStore()
        const visitor = await visitorStore.fetchVisitor(activeVisitorId)
        
        // 支払いが完了していない場合は/customerにリダイレクト
        if (visitor.paymentStatus !== 'completed') {
          await router.push('/customer')
          return
        } else {
          // 支払いが完了している場合はセッション情報をクリア
          localStorage.removeItem('activeOrderId')
          localStorage.removeItem('activeVisitorId')
        }
      } catch (error) {
        console.error('visitor情報の取得に失敗しました:', error)
        // エラーが発生した場合は/customerにリダイレクト（安全のため）
        await router.push('/customer')
        return
      }
    }
  }
  
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
        
        // テーブル一覧を取得
        isLoadingTables.value = true
        try {
          const tables = await tableStore.fetchTablesByShopCode(shopCodeFromQuery)
          availableTables.value = tables
        } catch (error) {
          console.error('テーブル一覧の取得に失敗しました:', error)
        } finally {
          isLoadingTables.value = false
        }
        
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
    cartStore.loadTableNumberFromStorage()
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

const selectShop = async (shop: Shop) => {
  selectedShop.value = shop
  shopStore.setCurrentShop(shop)
  tableNumber.value = ''
  errorMessage.value = ''
  availableTables.value = []
  
  // テーブル一覧を取得
  isLoadingTables.value = true
  try {
    const tables = await tableStore.fetchTablesByShopCode(shop.code)
    availableTables.value = tables
  } catch (error) {
    console.error('テーブル一覧の取得に失敗しました:', error)
    errorMessage.value = 'テーブル一覧の取得に失敗しました'
  } finally {
    isLoadingTables.value = false
  }
}

const selectTable = (table: ShopTable) => {
  tableNumber.value = table.tableNumber
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

