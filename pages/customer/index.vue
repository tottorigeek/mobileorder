<template>
  <NuxtLayout name="default" title="メニュー一覧">
    <!-- 来店人数入力モーダル -->
    <div
      v-if="showVisitorModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeVisitorModal"
    >
      <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">来店人数を入力してください</h2>
        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              来店人数 <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-4">
              <button
                @click="decreaseGuests"
                :disabled="numberOfGuests <= 1"
                class="w-12 h-12 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center font-bold text-xl text-gray-700 transition-colors"
              >
                -
              </button>
              <div class="flex-1 text-center">
                <div class="text-4xl font-bold text-blue-600">{{ numberOfGuests }}</div>
                <div class="text-sm text-gray-500 mt-1">名様</div>
              </div>
              <button
                @click="increaseGuests"
                :disabled="numberOfGuests >= 20"
                class="w-12 h-12 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center font-bold text-xl text-gray-700 transition-colors"
              >
                +
              </button>
            </div>
          </div>
          <button
            @click="submitVisitorInfo"
            :disabled="isSubmittingVisitor"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl text-lg font-bold hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl"
          >
            {{ isSubmittingVisitor ? '登録中...' : '登録する' }}
          </button>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <!-- 席情報表示 -->
      <div v-if="cartStore.tableNumber" class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-5 shadow-lg text-white">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-blue-100 font-medium mb-2 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              ご利用中の席
            </div>
            <div class="flex items-center gap-3 flex-wrap">
              <div class="text-2xl font-bold">
                テーブル {{ cartStore.tableNumber }}
              </div>
              <div v-if="currentTableInfo?.name" class="text-sm text-blue-100 bg-white/20 px-3 py-1 rounded-full">
                {{ currentTableInfo.name }}
              </div>
              <div v-if="currentTableInfo?.capacity" class="text-sm text-blue-100 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                定員: {{ currentTableInfo.capacity }}名
              </div>
            </div>
          </div>
          <NuxtLink
            to="/shop-select"
            class="text-sm text-white hover:text-blue-100 font-medium transition-colors bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg"
          >
            変更
          </NuxtLink>
        </div>
      </div>

      <!-- 番号入力 -->
      <NumberInput />

      <!-- カテゴリフィルター -->
      <div v-if="isLoadingCategories" class="flex gap-2 overflow-x-auto pb-2">
        <div class="px-5 py-2.5 rounded-xl bg-gray-100 animate-pulse">読み込み中...</div>
      </div>
      <div v-else class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button
          :class="[
            'px-5 py-2.5 rounded-xl font-semibold whitespace-nowrap transition-all duration-300 touch-target',
            menuStore.selectedCategory === null
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30'
              : 'bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200'
          ]"
          @click="menuStore.setCategory(null)"
        >
          すべて
        </button>
        <button
          v-for="category in shopCategories"
          :key="category.code"
          :class="[
            'px-5 py-2.5 rounded-xl font-semibold whitespace-nowrap transition-all duration-300 touch-target',
            menuStore.selectedCategory === category.code
              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30'
              : 'bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200'
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

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>

    <!-- Bottom Navigation -->
    <CustomerBottomNav />
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useCartStore } from '~/stores/cart'
import { useShopStore } from '~/stores/shop'
import { useTableStore } from '~/stores/table'
import { useCategoryStore } from '~/stores/category'
import { useVisitorStore } from '~/stores/visitor'
import type { ShopTable, ShopCategory } from '~/types'
import MenuCard from '~/components/MenuCard.vue'
import NumberInput from '~/components/NumberInput.vue'
import CustomerBottomNav from '~/components/CustomerBottomNav.vue'

const menuStore = useMenuStore()
const cartStore = useCartStore()
const shopStore = useShopStore()
const tableStore = useTableStore()
const categoryStore = useCategoryStore()
const visitorStore = useVisitorStore()

const currentTableInfo = ref<ShopTable | null>(null)
const shopCategories = ref<ShopCategory[]>([])
const isLoadingCategories = ref(false)
const showVisitorModal = ref(false)
const numberOfGuests = ref(2)
const isSubmittingVisitor = ref(false)

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
    // QRコードから来ていない場合：ストレージから店舗とテーブル番号を読み込み
    shopStore.loadShopFromStorage()
    cartStore.loadTableNumberFromStorage()
    
    // 精算完了前の注文がある場合は、店舗やテーブル番号がなくても/shop-selectにリダイレクトしない
    const activeOrderId = typeof window !== 'undefined' ? localStorage.getItem('activeOrderId') : null
    const activeVisitorId = typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null
    
    if (activeOrderId && activeVisitorId) {
      // アクティブな注文がある場合は、visitor情報から店舗とテーブル情報を復元を試みる
      try {
        const visitor = await visitorStore.fetchVisitor(activeVisitorId)
        
        // visitor情報から店舗を取得
        if (visitor.shopId && !shopStore.currentShop) {
          const shop = await shopStore.fetchShopById(visitor.shopId)
          if (shop) {
            shopStore.setCurrentShop(shop)
          }
        }
        
        // visitor情報からテーブル番号を設定
        if (visitor.tableNumber && !cartStore.tableNumber) {
          cartStore.setTableNumber(visitor.tableNumber)
        }
      } catch (error) {
        console.error('visitor情報の取得に失敗しました:', error)
      }
    }
    
    // 店舗が選択されていない場合は店舗選択ページにリダイレクト（アクティブな注文がない場合のみ）
    if (!shopStore.currentShop && !activeOrderId) {
      await navigateTo('/shop-select')
      return
    }
    
    // テーブル番号が設定されていない場合は店舗選択ページにリダイレクト（アクティブな注文がない場合のみ）
    if (!cartStore.tableNumber && !activeOrderId) {
      await navigateTo('/shop-select')
      return
    }
    
    // テーブル情報を取得（店舗とテーブル番号が設定されている場合のみ）
    if (shopStore.currentShop && cartStore.tableNumber) {
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
  }
  
  // 店舗が設定されていることを確認（アクティブな注文がない場合のみリダイレクト）
  const activeOrderIdCheck = typeof window !== 'undefined' ? localStorage.getItem('activeOrderId') : null
  if (!shopStore.currentShop && !activeOrderIdCheck) {
    await navigateTo('/shop-select')
    return
  }
  
  // 店舗独自カテゴリを取得（店舗が設定されている場合のみ）
  if (shopStore.currentShop) {
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
  }
  
  // visitorIdが設定されていない場合は来店人数入力モーダルを表示
  if (!cartStore.visitorId && cartStore.tableNumber && shopStore.currentShop) {
    showVisitorModal.value = true
  }
})

const decreaseGuests = () => {
  if (numberOfGuests.value > 1) {
    numberOfGuests.value--
  }
}

const increaseGuests = () => {
  if (numberOfGuests.value < 20) {
    numberOfGuests.value++
  }
}

const submitVisitorInfo = async () => {
  if (!shopStore.currentShop || !cartStore.tableNumber) {
    alert('店舗またはテーブル情報が設定されていません')
    return
  }
  
  isSubmittingVisitor.value = true
  try {
    const visitor = await visitorStore.createVisitor({
      shopId: shopStore.currentShop.id,
      tableNumber: cartStore.tableNumber,
      numberOfGuests: numberOfGuests.value,
      tableId: currentTableInfo.value?.id
    })
    
    cartStore.setVisitorId(visitor.id)
    showVisitorModal.value = false
  } catch (error: any) {
    alert('来店情報の登録に失敗しました: ' + (error.message || 'エラーが発生しました'))
  } finally {
    isSubmittingVisitor.value = false
  }
}

const closeVisitorModal = () => {
  // モーダルを閉じることはできない（必須入力）
}
</script>

