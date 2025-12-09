<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />
      <!-- ヘッダー -->
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">着座管理</h1>
          <p class="text-sm sm:text-base text-gray-600">テーブルの着座状況と注文状況を管理します</p>
        </div>
        <button
          @click="refreshData"
          :disabled="visitorStore.isLoading"
          class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          更新
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="visitorStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- 着座中テーブル一覧 -->
      <div v-else-if="activeVisitors.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div
          v-for="visitor in activeVisitors"
          :key="visitor.id"
          class="bg-white p-4 sm:p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2"
          :class="getVisitorBorderClass(visitor)"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1 min-w-0">
              <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                テーブル {{ visitor.tableNumber }}
              </h3>
              <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ visitor.numberOfGuests }}名</p>
            </div>
            <div class="flex flex-col items-end gap-2 ml-2">
              <span
                :class="[
                  'px-2 sm:px-3 py-1 rounded-full text-xs font-semibold',
                  getPaymentStatusClass(visitor.paymentStatus)
                ]"
              >
                {{ getPaymentStatusLabel(visitor.paymentStatus) }}
              </span>
              <span
                v-if="!visitor.isSetCompleted"
                class="px-2 sm:px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"
              >
                セット未完了
              </span>
            </div>
          </div>

          <!-- 経過時間情報 -->
          <div class="space-y-2 sm:space-y-3 mb-4 p-3 sm:p-4 bg-gray-50 rounded-xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-0">
              <span class="text-xs sm:text-sm text-gray-700 flex items-center gap-2">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                着座開始からの経過時間
              </span>
              <span class="text-base sm:text-lg font-bold" :class="getElapsedTimeClass(visitor.arrivalTime)">
                {{ getElapsedTime(visitor.arrivalTime) }}
              </span>
            </div>
            <div v-if="getOldestPendingOrder(visitor.tableNumber)" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-0">
              <span class="text-xs sm:text-sm text-gray-700 flex items-center gap-2">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                未提供注文からの経過時間
              </span>
              <span class="text-base sm:text-lg font-bold text-red-600">
                {{ getOrderElapsedTime(visitor.tableNumber) }}
              </span>
            </div>
          </div>

          <!-- アクションボタン -->
          <div class="flex flex-col sm:flex-row gap-2">
            <button
              @click="showMoveModal(visitor)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              移動
            </button>
            <button
              @click="showResetModal(visitor)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              リセット
            </button>
            <button
              @click="showForceReleaseModal(visitor)"
              class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
            >
              強制解除
            </button>
          </div>
        </div>
      </div>

      <!-- 着座中テーブルがない場合 -->
      <div v-else class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-6">着座中のテーブルがありません</p>
      </div>
    </div>

    <!-- テーブル移動モーダル -->
    <div
      v-if="showMoveTableModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeMoveModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl font-bold mb-4">テーブルを移動</h2>
          <p class="text-xs sm:text-sm text-gray-600 mb-4">
            テーブル {{ movingVisitor?.tableNumber }} を移動先のテーブルを選択してください
          </p>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                移動先テーブル <span class="text-red-500">*</span>
              </label>
              <select
                v-model="selectedTableId"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">選択してください</option>
                <option
                  v-for="table in availableTables"
                  :key="table.id"
                  :value="table.id"
                >
                  テーブル {{ table.tableNumber }} {{ table.name ? `(${table.name})` : '' }}
                </option>
              </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
              <button
                type="button"
                @click="closeMoveModal"
                class="w-full sm:flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base"
              >
                キャンセル
              </button>
              <button
                @click="moveTable"
                :disabled="!selectedTableId || isProcessing"
                class="w-full sm:flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed text-sm sm:text-base"
              >
                {{ isProcessing ? '移動中...' : '移動' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- テーブルリセットモーダル -->
    <div
      v-if="showResetTableModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeResetModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl font-bold mb-4 text-red-600">テーブルをリセット</h2>
          <p class="text-xs sm:text-sm text-gray-600 mb-4">
            テーブル {{ resettingVisitor?.tableNumber }} を強制的にリセットしますか？
          </p>
          <p class="text-xs sm:text-sm text-red-600 mb-4 font-semibold">
            この操作は取り消せません。テーブルの着座情報が削除されます。
          </p>

          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="button"
              @click="closeResetModal"
              class="w-full sm:flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base"
            >
              キャンセル
            </button>
            <button
              @click="resetTable"
              :disabled="isProcessing"
              class="w-full sm:flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed text-sm sm:text-base"
            >
              {{ isProcessing ? 'リセット中...' : 'リセット' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 強制解除モーダル -->
    <div
      v-if="showForceReleaseModalFlag"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeForceReleaseModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <h2 class="text-lg sm:text-xl font-bold mb-4 text-orange-600">visitorを強制解除</h2>
          <p class="text-xs sm:text-sm text-gray-600 mb-4">
            テーブル {{ forceReleasingVisitor?.tableNumber }} のvisitor（ID: {{ forceReleasingVisitor?.id }}）を強制的に削除しますか？
          </p>
          <p class="text-xs sm:text-sm text-orange-600 mb-4 font-semibold">
            ⚠️ この操作は取り消せません。visitor情報が完全に削除され、テーブルが空き状態に戻ります。
          </p>
          <p class="text-xs sm:text-sm text-gray-500 mb-4">
            顧客側のcartStore.visitorIdは次回アクセス時に無効になります。
          </p>

          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="button"
              @click="closeForceReleaseModal"
              class="w-full sm:flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base"
            >
              キャンセル
            </button>
            <button
              @click="forceReleaseVisitor"
              :disabled="isProcessing"
              class="w-full sm:flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed text-sm sm:text-base"
            >
              {{ isProcessing ? '解除中...' : '強制解除' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useVisitorStore } from '~/stores/visitor'
import { useTableStore } from '~/stores/table'
import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Visitor, ShopTable } from '~/types'

const { navigationItems } = useShopNavigation()
const { pageTitle } = useShopPageTitle('着座管理')

const visitorStore = useVisitorStore()
const tableStore = useTableStore()
const orderStore = useOrderStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { checkAuth } = useAuthCheck()

const showMoveTableModal = ref(false)
const showResetTableModal = ref(false)
const showForceReleaseModalFlag = ref(false)
const movingVisitor = ref<Visitor | null>(null)
const resettingVisitor = ref<Visitor | null>(null)
const forceReleasingVisitor = ref<Visitor | null>(null)
const selectedTableId = ref('')
const isProcessing = ref(false)

// アクティブなvisitor（着座中で未会計または会計済みだがセット未完了）
const activeVisitors = computed(() => {
  return visitorStore.visitors.filter(v => 
    v.paymentStatus === 'pending' || 
    (v.paymentStatus === 'completed' && !v.isSetCompleted)
  )
})

// 利用可能なテーブル（空いているテーブル）
const availableTables = computed(() => {
  return tableStore.tables.filter(table => 
    table.isActive && 
    !table.visitorId &&
    table.id !== movingVisitor.value?.tableId
  )
})

// 各テーブルの未提供注文の最古の注文時刻を取得する関数
const getOldestPendingOrder = (tableNumber: string) => {
  const orders = orderStore.orders.filter(order => 
    order.tableNumber === tableNumber &&
    (order.status === 'pending' || order.status === 'accepted' || order.status === 'cooking')
  )
  if (orders.length === 0) return null
  return orders.reduce((oldest, order) => {
    return order.createdAt < oldest.createdAt ? order : oldest
  })
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
      visitorStore.fetchVisitors(shopStore.currentShop.id),
      tableStore.fetchTables(shopStore.currentShop.id),
      orderStore.fetchOrders(undefined, shopStore.currentShop.code)
    ])
  }

  // 定期的にデータを更新（30秒ごと）
  const interval = setInterval(async () => {
    if (shopStore.currentShop) {
      await Promise.all([
        visitorStore.fetchVisitors(shopStore.currentShop.id),
        orderStore.fetchOrders(undefined, shopStore.currentShop.code)
      ])
    }
  }, 30000)

  onUnmounted(() => {
    clearInterval(interval)
  })
})

const refreshData = async () => {
  if (shopStore.currentShop) {
    await Promise.all([
      visitorStore.fetchVisitors(shopStore.currentShop.id),
      tableStore.fetchTables(shopStore.currentShop.id),
      orderStore.fetchOrders(undefined, shopStore.currentShop.code)
    ])
  }
}

// 経過時間の計算
const getElapsedTime = (arrivalTime: string) => {
  const arrival = new Date(arrivalTime)
  const now = new Date()
  const diff = now.getTime() - arrival.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  
  if (hours > 0) {
    return `${hours}時間${mins}分`
  }
  return `${mins}分`
}

// 注文からの経過時間
const getOrderElapsedTime = (tableNumber: string) => {
  const oldestOrder = getOldestPendingOrder(tableNumber)
  if (!oldestOrder) {
    return 'なし'
  }
  return getElapsedTime(oldestOrder.createdAt.toString())
}

// 経過時間に応じたクラス
const getElapsedTimeClass = (arrivalTime: string) => {
  const arrival = new Date(arrivalTime)
  const now = new Date()
  const diff = now.getTime() - arrival.getTime()
  const minutes = Math.floor(diff / 60000)
  
  if (minutes > 120) return 'text-red-600'
  if (minutes > 60) return 'text-orange-600'
  return 'text-gray-900'
}

// Visitorのボーダークラス
const getVisitorBorderClass = (visitor: Visitor) => {
  if (visitor.paymentStatus === 'completed' && !visitor.isSetCompleted) {
    return 'border-yellow-400'
  }
  const arrival = new Date(visitor.arrivalTime)
  const now = new Date()
  const diff = now.getTime() - arrival.getTime()
  const minutes = Math.floor(diff / 60000)
  
  if (minutes > 120) return 'border-red-400'
  if (minutes > 60) return 'border-orange-400'
  return 'border-blue-400'
}

// 支払いステータスのラベル
const getPaymentStatusLabel = (status: string) => {
  return status === 'completed' ? '会計済み' : '会計待ち'
}

// 支払いステータスのクラス
const getPaymentStatusClass = (status: string) => {
  return status === 'completed' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-yellow-100 text-yellow-800'
}

// テーブル移動モーダル
const showMoveModal = (visitor: Visitor) => {
  movingVisitor.value = visitor
  selectedTableId.value = ''
  showMoveTableModal.value = true
}

const closeMoveModal = () => {
  showMoveTableModal.value = false
  movingVisitor.value = null
  selectedTableId.value = ''
}

const moveTable = async () => {
  if (!movingVisitor.value || !selectedTableId.value) return

  isProcessing.value = true
  try {
    // 新しいテーブル情報を取得
    const newTable = tableStore.tables.find(t => t.id === selectedTableId.value)
    if (!newTable) {
      throw new Error('移動先のテーブルが見つかりません')
    }

    // visitorストアに移動機能を追加する必要がある
    // ここでは直接APIを呼び出す
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    
    if (!token) {
      throw new Error('認証トークンが見つかりません')
    }

    // 古いテーブルのvisitor_idをクリア
    if (movingVisitor.value.tableId) {
      await $fetch(`${apiBase}/tables/${movingVisitor.value.tableId}`, {
        method: 'PUT',
        body: { visitorId: null, status: 'available' },
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      })
    }

    // visitorのテーブルIDを更新
    await $fetch(`${apiBase}/visitors/${movingVisitor.value.id}`, {
      method: 'PUT',
      body: {
        tableId: selectedTableId.value,
        tableNumber: newTable.tableNumber
      },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    // 新しいテーブルにvisitor_idを設定
    await $fetch(`${apiBase}/tables/${selectedTableId.value}`, {
      method: 'PUT',
      body: { visitorId: movingVisitor.value.id, status: 'occupied' },
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    // データを再取得
    await refreshData()
    closeMoveModal()
  } catch (error: any) {
    alert(error.message || 'テーブルの移動に失敗しました')
  } finally {
    isProcessing.value = false
  }
}

// テーブルリセットモーダル
const showResetModal = (visitor: Visitor) => {
  resettingVisitor.value = visitor
  showResetTableModal.value = true
}

const closeResetModal = () => {
  showResetTableModal.value = false
  resettingVisitor.value = null
}

const resetTable = async () => {
  if (!resettingVisitor.value) return

  isProcessing.value = true
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    
    if (!token) {
      throw new Error('認証トークンが見つかりません')
    }

    // visitorを削除（またはセット完了にする）
    // セット完了APIを使用してリセット
    await $fetch(`${apiBase}/visitors/${resettingVisitor.value.id}/set-complete`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    // データを再取得
    await refreshData()
    closeResetModal()
  } catch (error: any) {
    alert(error.message || 'テーブルのリセットに失敗しました')
  } finally {
    isProcessing.value = false
  }
}

// 強制解除モーダル
const showForceReleaseModal = (visitor: Visitor) => {
  forceReleasingVisitor.value = visitor
  showForceReleaseModalFlag.value = true
}

const closeForceReleaseModal = () => {
  showForceReleaseModalFlag.value = false
  forceReleasingVisitor.value = null
}

const forceReleaseVisitor = async () => {
  if (!forceReleasingVisitor.value) return

  isProcessing.value = true
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    
    if (!token) {
      throw new Error('認証トークンが見つかりません')
    }

    // visitorを削除
    await $fetch(`${apiBase}/visitors/${forceReleasingVisitor.value.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    // データを再取得
    await refreshData()
    closeForceReleaseModal()
    alert('visitorを強制解除しました')
  } catch (error: any) {
    alert(error.message || 'visitorの強制解除に失敗しました')
  } finally {
    isProcessing.value = false
  }
}
</script>

