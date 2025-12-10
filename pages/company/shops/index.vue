<template>
  <NuxtLayout name="company" title="店舗管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 mb-1">店舗管理</h2>
          <p class="text-gray-600">システム全体の店舗を管理します</p>
        </div>
        <button
          @click="showAddModal = true"
          class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          店舗を追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="shopStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-green-200 border-t-green-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- 店舗一覧 -->
      <div v-else-if="shopStore.shops.length === 0" class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-2">店舗が登録されていません</p>
        <p class="text-gray-400 text-sm">店舗を追加して管理を開始しましょう</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="shop in shopStore.shops"
          :key="shop.id"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-green-300"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-xl font-bold text-gray-900">{{ shop.name }}</h3>
                    <span :class="shop.isActive ? 'px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full text-xs font-semibold shadow-md' : 'px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold'">
                      {{ shop.isActive ? 'アクティブ' : '無効' }}
                    </span>
                  </div>
                  <div class="space-y-1 text-sm text-gray-600">
                    <p class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                      </svg>
                      コード: {{ shop.code }}
                    </p>
                    <p v-if="shop.description" class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                      </svg>
                      {{ shop.description }}
                    </p>
                    <p v-if="shop.address" class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ shop.address }}
                    </p>
                    <div v-if="shop.owners && shop.owners.length > 0" class="flex items-center gap-2 mt-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span class="font-medium">オーナー:</span>
                      <span v-for="(owner, index) in shop.owners" :key="owner.id" class="ml-1">
                        {{ owner.name }}
                        <span v-if="owner.email" class="text-gray-500">({{ owner.email }})</span>
                        <span v-if="index < shop.owners.length - 1" class="text-gray-400">, </span>
                      </span>
                    </div>
                    <p v-else-if="shop.owner" class="flex items-center gap-2 mt-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span class="font-medium">オーナー:</span> {{ shop.owner.name }}
                      <span v-if="shop.owner.email" class="text-gray-500">({{ shop.owner.email }})</span>
                    </p>
                    <p v-else class="text-gray-400 mt-2 flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      オーナー未設定
                    </p>
                  </div>
                  
                  <!-- 売上情報 -->
                  <div class="mt-4 pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      売上情報
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                      <div class="bg-teal-50 p-3 rounded-lg border border-teal-200">
                        <p class="text-xs text-gray-600 mb-1">直近1時間</p>
                        <p class="text-lg font-bold text-teal-700">¥{{ getShopSales(shop.id, '1hour').toLocaleString() }}</p>
                      </div>
                      <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                        <p class="text-xs text-gray-600 mb-1">本日</p>
                        <p class="text-lg font-bold text-green-700">¥{{ getShopSales(shop.id, 'today').toLocaleString() }}</p>
                      </div>
                      <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                        <p class="text-xs text-gray-600 mb-1">昨日</p>
                        <p class="text-lg font-bold text-blue-700">¥{{ getShopSales(shop.id, 'yesterday').toLocaleString() }}</p>
                      </div>
                      <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                        <p class="text-xs text-gray-600 mb-1">7日間</p>
                        <p class="text-lg font-bold text-purple-700">¥{{ getShopSales(shop.id, '7days').toLocaleString() }}</p>
                      </div>
                      <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                        <p class="text-xs text-gray-600 mb-1">30日間</p>
                        <p class="text-lg font-bold text-orange-700">¥{{ getShopSales(shop.id, '30days').toLocaleString() }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                @click="handleGoToShopDashboard(shop)"
                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold"
              >
                ダッシュボード
              </button>
              <NuxtLink
                :to="`/company/shops/${shop.id}/edit`"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold"
              >
                編集
              </NuxtLink>
              <button
                @click="handleDeleteShop(shop)"
                :class="[
                  'px-4 py-2 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold',
                  (shop.owners && shop.owners.length > 0) || shop.owner
                    ? 'bg-gray-200 text-gray-600 hover:bg-gray-300'
                    : 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700'
                ]"
              >
                削除
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 追加モーダル -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-semibold mb-4">店舗を追加</h3>

          <form @submit.prevent="handleAddShop" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                店舗コード <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newShop.code"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="shop001"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                店舗名 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="newShop.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="店舗名を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                説明
              </label>
              <textarea
                v-model="newShop.description"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                rows="3"
                placeholder="店舗の説明を入力"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                住所
              </label>
              <input
                v-model="newShop.address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="住所を入力"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                電話番号
              </label>
              <input
                v-model="newShop.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="03-1234-5678"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                最大テーブル数
              </label>
              <input
                v-model.number="newShop.maxTables"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="20"
              />
            </div>

            <div v-if="addError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
              {{ addError }}
            </div>

            <div class="flex gap-3 justify-end">
              <button
                type="button"
                @click="showAddModal = false"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
              >
                キャンセル
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
              >
                {{ isSubmitting ? '追加中...' : '追加' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import { useOrderStore } from '~/stores/order'
import type { Shop } from '~/types/multi-shop'

definePageMeta({
  layout: 'default'
})

const shopStore = useShopStore()
const authStore = useAuthStore()
const orderStore = useOrderStore()

const showAddModal = ref(false)
const isSubmitting = ref(false)
const addError = ref('')

const newShop = ref({
  code: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  maxTables: 20
})

const { navigationItems } = useCompanyNavigation()
const { handleLogout } = useAuthCheck()

// 店舗別売上計算
const getShopSales = (shopId: string, period: '1hour' | 'today' | 'yesterday' | '7days' | '30days'): number => {
  const shopOrders = orderStore.orders.filter(order => order.shopId === shopId && order.status === 'completed')
  
  if (shopOrders.length === 0) return 0
  
  const now = new Date()
  let startDate: Date
  let endDate: Date = new Date(now)
  
  switch (period) {
    case '1hour':
      startDate = new Date(now)
      startDate.setHours(startDate.getHours() - 1)
      endDate = new Date(now)
      break
    case 'today':
      startDate = new Date(now)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
    case 'yesterday':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 1)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(startDate)
      endDate.setHours(23, 59, 59, 999)
      break
    case '7days':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 7)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
    case '30days':
      startDate = new Date(now)
      startDate.setDate(startDate.getDate() - 30)
      startDate.setHours(0, 0, 0, 0)
      endDate = new Date(now)
      endDate.setHours(23, 59, 59, 999)
      break
  }
  
  return shopOrders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= startDate && orderDate <= endDate
    })
    .reduce((sum, order) => sum + order.totalAmount, 0)
}

const handleAddShop = async () => {
  isSubmitting.value = true
  addError.value = ''
  
  try {
    // TODO: 店舗追加APIの実装が必要
    // await shopStore.createShop(newShop.value)
    alert('店舗追加機能は実装予定です')
    showAddModal.value = false
    newShop.value = {
      code: '',
      name: '',
      description: '',
      address: '',
      phone: '',
      maxTables: 20
    }
  } catch (error: any) {
    addError.value = error?.data?.error || '店舗の追加に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const handleGoToShopDashboard = async (shop: Shop) => {
  try {
    // 店舗をストアに設定
    shopStore.setCurrentShop(shop)
    // 店舗ダッシュボードに遷移
    await navigateTo('/shop/dashboard')
  } catch (error) {
    console.error('店舗ダッシュボードへの遷移に失敗しました:', error)
    alert('店舗ダッシュボードへの遷移に失敗しました')
  }
}

const handleDeleteShop = async (shop: Shop) => {
  // オーナーが存在する場合は削除を拒否
  const hasOwners = (shop.owners && shop.owners.length > 0) || shop.owner
  if (hasOwners) {
    alert(`店舗「${shop.name}」にはオーナーが設定されています。\n削除するには、まずオーナーを解除してください。`)
    return
  }
  
  // 確認ダイアログ
  if (!confirm(`店舗「${shop.name}」を削除しますか？\nこの操作は取り消せません。`)) {
    return
  }
  
  try {
    await shopStore.deleteShop(shop.id)
    alert('店舗を削除しました')
  } catch (error: any) {
    const errorMessage = error?.data?.error || error?.message || '店舗の削除に失敗しました'
    alert(errorMessage)
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 店舗一覧を取得
  await shopStore.fetchShops()
  
  // 全店舗の注文を取得（売上計算用）
  try {
    const shopIds = shopStore.shops.map(s => s.id)
    if (shopIds.length > 0) {
      await orderStore.fetchOrders(undefined, undefined, shopIds)
    }
  } catch (error) {
    console.error('注文データの取得に失敗しました:', error)
  }
})
</script>

