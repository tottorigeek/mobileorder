<template>
    <div class="space-y-6">
      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-1">テーブル管理</h1>
          <p class="text-gray-600">全店舗のテーブルを一元管理</p>
        </div>
        <button
          v-if="selectedShopId"
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          テーブルを追加
        </button>
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
              @change="filterTables"
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
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-green-200 border-t-green-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- テーブル一覧（店舗別アコーディオン） -->
      <div v-else-if="shopsForDisplay.length > 0" class="space-y-4">
        <div
          v-for="shop in shopsForDisplay"
          :key="shop.id"
          class="bg-white rounded-2xl shadow-lg overflow-hidden"
        >
          <!-- アコーディオンヘッダー -->
          <button
            type="button"
            class="w-full flex items-center justify-between px-4 sm:px-6 py-4 sm:py-5 hover:bg-gray-50 transition-colors"
            @click="toggleShop(shop.id)"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold shadow-md">
                {{ shop.name.slice(0, 2) }}
              </div>
              <div class="text-left">
                <p class="font-semibold text-gray-900 text-base sm:text-lg">{{ shop.name }}</p>
                <p class="text-xs sm:text-sm text-gray-500">
                  全テーブル {{ getShopStats(shop.id).total }}卓 / 稼働中 {{ getShopStats(shop.id).active }}卓
                  <span v-if="getShopStats(shop.id).seated > 0" class="ml-2 text-emerald-600 font-semibold">
                    着座中 {{ getShopStats(shop.id).seated }}卓
                  </span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-1 text-xs sm:text-sm text-gray-500">
                <span class="inline-flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 着座中
                </span>
                <span class="inline-flex items-center gap-1 ml-2">
                  <span class="w-2 h-2 rounded-full bg-gray-300"></span> 空席
                </span>
              </div>
              <svg
                class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
                :class="{ 'rotate-180': isShopExpanded(shop.id) }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </button>

          <!-- アコーディオン中身 -->
          <transition name="fade" mode="out-in">
            <div v-show="isShopExpanded(shop.id)" class="border-t border-gray-100 px-4 sm:px-6 py-4 sm:py-5 bg-gray-50/60">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div
                  v-for="table in tablesByShop[shop.id]"
                  :key="table.id"
                  :class="[
                    'bg-white p-4 sm:p-5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border-2',
                    isTableOccupied(table)
                      ? 'border-emerald-400 ring-1 ring-emerald-100'
                      : 'border-transparent hover:border-green-200'
                  ]"
                >
                  <div class="flex justify-between items-start mb-3 sm:mb-4">
                    <div class="flex items-center gap-3">
                      <div
                        :class="[
                          'w-10 h-10 rounded-xl flex items-center justify-center shadow',
                          isTableOccupied(table)
                            ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white'
                            : 'bg-gradient-to-br from-gray-200 to-gray-300 text-gray-700'
                        ]"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                          />
                        </svg>
                      </div>
                      <div>
                        <h3 class="text-lg font-bold text-gray-900">
                          テーブル {{ table.tableNumber }}
                        </h3>
                        <p v-if="table.name" class="text-xs sm:text-sm text-gray-600 mt-1">{{ table.name }}</p>
                      </div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                      <span
                        :class="[
                          'px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold inline-flex items-center gap-1',
                          table.isActive
                            ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md'
                            : 'bg-gray-200 text-gray-700'
                        ]"
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full"
                          :class="table.isActive ? 'bg-white/90' : 'bg-gray-500'"
                        ></span>
                        {{ table.isActive ? '有効' : '無効' }}
                      </span>
                      <span
                        v-if="getStatusInfo(table).label"
                        :class="getStatusInfo(table).class"
                      >
                        <span class="w-1.5 h-1.5 rounded-full" :class="getStatusInfo(table).dotClass"></span>
                        {{ getStatusInfo(table).label }}
                      </span>
                    </div>
                  </div>

                  <div class="space-y-2 mb-3 sm:mb-4 p-3 sm:p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs sm:text-sm text-gray-700 flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                      </svg>
                      <span class="font-medium">定員:</span> {{ table.capacity }}名
                    </p>
                    <p class="text-xs sm:text-sm text-gray-700 flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                        />
                      </svg>
                      <span class="font-medium">店舗:</span> {{ getShopName(table.shopId) }}
                    </p>
                  </div>

                  <div class="flex flex-col sm:flex-row gap-2">
                    <button
                      @click="editTable(table)"
                      class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
                    >
                      編集
                    </button>
                    <button
                      v-if="isTableOccupied(table) && table.visitorId"
                      @click="openForceReleaseModal(table)"
                      class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
                    >
                      着座解除
                    </button>
                    <button
                      @click="deleteTable(table)"
                      class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-sm font-semibold"
                    >
                      削除
                    </button>
                  </div>

                  <!-- QRコード表示 -->
                  <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t">
                    <QRCodeGenerator
                      :shop-code="getShopCode(table.shopId)"
                      :table-number="table.tableNumber"
                    />
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- テーブルが存在しない場合 -->
      <div v-else class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-6">テーブルが登録されていません</p>
        <button
          v-if="selectedShopId"
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
        >
          テーブルを追加
        </button>
      </div>
    </div>

    <!-- 作成・編集モーダル -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4">
            {{ editingTable ? 'テーブルを編集' : 'テーブルを追加' }}
          </h2>

          <form @submit.prevent="saveTable" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                テーブル番号 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.tableNumber"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="例: 1, A-1"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                テーブル名（任意）
              </label>
              <input
                v-model="formData.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="例: 窓際席"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                定員 <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="formData.capacity"
                type="number"
                required
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <div>
              <label class="flex items-center gap-2">
                <input
                  v-model="formData.isActive"
                  type="checkbox"
                  class="w-4 h-4"
                />
                <span class="text-sm text-gray-700">有効</span>
              </label>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
              >
                キャンセル
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
              >
                {{ editingTable ? '更新' : '作成' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 着座強制解除モーダル -->
    <div
      v-if="showForceReleaseModal && targetTableForRelease"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeForceReleaseModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 space-y-4">
          <h2 class="text-xl font-bold text-gray-900">
            テーブル {{ targetTableForRelease.tableNumber }} の着座を解除しますか？
          </h2>
          <p class="text-sm text-gray-700">
            この操作は、来店中のお客様のテーブル状態を強制的に「空席」に戻します。
            関連する注文・会計データをどのように扱うかを選択してください。
          </p>

          <div class="space-y-3">
            <label class="flex items-start gap-2">
              <input
                v-model="treatOrdersAsPaid"
                type="radio"
                :value="true"
                class="mt-1"
              />
              <div>
                <p class="text-sm font-semibold text-gray-900">注文を清算済み扱いにする</p>
                <p class="text-xs text-gray-600">
                  現在の注文を売上として計上し、未完了の注文ステータスを完了に更新します。
                  実際には現金などで支払い済みだが端末操作が漏れている場合などに選択してください。
                </p>
              </div>
            </label>

            <label class="flex items-start gap-2">
              <input
                v-model="treatOrdersAsPaid"
                type="radio"
                :value="false"
                class="mt-1"
              />
              <div>
                <p class="text-sm font-semibold text-gray-900">注文を清算せずキャンセル扱いにする</p>
                <p class="text-xs text-gray-600">
                  進行中の注文をすべてキャンセル扱いとし、売上には計上しません。
                  テスト注文や誤操作で発生したデータを破棄したい場合に選択してください。
                </p>
              </div>
            </label>
          </div>

          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="closeForceReleaseModal"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
            >
              キャンセル
            </button>
            <button
              type="button"
              @click="executeForceRelease"
              :disabled="isForceReleasing"
              class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors text-sm font-semibold"
            >
              {{ isForceReleasing ? '処理中...' : '着座解除を実行' }}
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { useTableStore } from '~/stores/table'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import QRCodeGenerator from '~/components/QRCodeGenerator.vue'
import type { ShopTable, Shop } from '~/types'

definePageMeta({
  layout: 'company'
})

const { handleLogout } = useAuthCheck()

const tableStore = useTableStore()
const authStore = useAuthStore()
const shopStore = useShopStore()

const allShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const allTables = ref<ShopTable[]>([])
const filteredTables = ref<ShopTable[]>([])
const expandedShopIds = ref<string[]>([])
const isLoading = ref(false)
const showCreateModal = ref(false)
const editingTable = ref<ShopTable | null>(null)

// 着座強制解除用
const showForceReleaseModal = ref(false)
const targetTableForRelease = ref<ShopTable | null>(null)
const treatOrdersAsPaid = ref<boolean>(true)
const isForceReleasing = ref(false)

const formData = ref({
  tableNumber: '',
  name: '',
  capacity: 4,
  isActive: true
})

const getShopName = (shopId: string) => {
  const shop = allShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const getShopCode = (shopId: string) => {
  const shop = allShops.value.find(s => s.id === shopId)
  return shop?.code || ''
}

const tablesByShop = computed<Record<string, ShopTable[]>>(() => {
  const groups: Record<string, ShopTable[]> = {}
  for (const table of filteredTables.value) {
    if (!groups[table.shopId]) {
      groups[table.shopId] = []
    }
    groups[table.shopId].push(table)
  }
  return groups
})

const shopsForDisplay = computed(() => {
  return allShops.value.filter((shop) => tablesByShop.value[shop.id]?.length)
})

const filterTables = () => {
  if (selectedShopId.value) {
    filteredTables.value = allTables.value.filter(t => t.shopId === selectedShopId.value)
  } else {
    filteredTables.value = allTables.value
  }
}

const isTableOccupied = (table: ShopTable) => {
  // status: available / occupied / set_pending / checkout_pending など + visitorId で着座判定
  const status = table.status || 'available'
  return status === 'occupied' || status === 'set_pending' || status === 'checkout_pending' || !!table.visitorId
}

const getStatusInfo = (table: ShopTable) => {
  const status = table.status || 'available'
  if (!table.isActive) {
    return {
      label: '',
      class: 'hidden',
      dotClass: ''
    }
  }

  if (status === 'set_pending') {
    return {
      label: '入店待ち',
      class: 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-amber-100 text-amber-800',
      dotClass: 'bg-amber-500'
    }
  }

  if (status === 'checkout_pending') {
    return {
      label: '会計待ち',
      class: 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-sky-100 text-sky-800',
      dotClass: 'bg-sky-500'
    }
  }

  if (status === 'occupied' || table.visitorId) {
    return {
      label: '着座中',
      class: 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-100 text-emerald-800',
      dotClass: 'bg-emerald-500'
    }
  }

  return {
    label: '空席',
    class: 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-gray-100 text-gray-600',
    dotClass: 'bg-gray-400'
  }
}

const getShopStats = (shopId: string) => {
  const list = tablesByShop.value[shopId] || []
  const total = list.length
  const active = list.filter(t => t.isActive).length
  const seated = list.filter(t => isTableOccupied(t)).length
  return { total, active, seated }
}

const toggleShop = (shopId: string) => {
  if (expandedShopIds.value.includes(shopId)) {
    expandedShopIds.value = expandedShopIds.value.filter(id => id !== shopId)
  } else {
    expandedShopIds.value.push(shopId)
  }
}

const isShopExpanded = (shopId: string) => expandedShopIds.value.includes(shopId)

const fetchAllTables = async () => {
  isLoading.value = true
  try {
    // 全店舗のテーブルを取得
    await shopStore.fetchShops()
    const shopIds = shopStore.shops.map(s => s.id)
    
    if (shopIds.length > 0) {
      allTables.value = await tableStore.fetchTablesMultiShop(shopIds)
    } else {
      allTables.value = []
    }
    filterTables()
  } catch (error) {
    console.error('テーブル一覧の取得に失敗しました:', error)
    allTables.value = []
  } finally {
    isLoading.value = false
  }
}

const editTable = (table: ShopTable) => {
  editingTable.value = table
  selectedShopId.value = table.shopId
  formData.value = {
    tableNumber: table.tableNumber,
    name: table.name || '',
    capacity: table.capacity,
    isActive: table.isActive
  }
  showCreateModal.value = true
}

const saveTable = async () => {
  if (!selectedShopId.value) {
    alert('店舗を選択してください')
    return
  }

  try {
    if (editingTable.value) {
      // 更新
      await tableStore.updateTable(editingTable.value.id, formData.value)
    } else {
      // 作成
      await tableStore.createTable(selectedShopId.value, formData.value)
    }
    closeModal()
    await fetchAllTables()
  } catch (error: any) {
    alert(error.message || 'テーブルの保存に失敗しました')
  }
}

const deleteTable = async (table: ShopTable) => {
  if (!confirm(`テーブル「${table.tableNumber}」を削除してもよろしいですか？`)) {
    return
  }

  try {
    await tableStore.deleteTable(table.id)
    await fetchAllTables()
  } catch (error: any) {
    alert(error.message || 'テーブルの削除に失敗しました')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingTable.value = null
  formData.value = {
    tableNumber: '',
    name: '',
    capacity: 4,
    isActive: true
  }
}

const openForceReleaseModal = (table: ShopTable) => {
  targetTableForRelease.value = table
  // デフォルトは「清算済み扱い」にしておく
  treatOrdersAsPaid.value = true
  showForceReleaseModal.value = true
}

const closeForceReleaseModal = () => {
  showForceReleaseModal.value = false
  targetTableForRelease.value = null
  isForceReleasing.value = false
}

const executeForceRelease = async () => {
  if (!targetTableForRelease.value || !targetTableForRelease.value.visitorId) {
    return
  }

  if (
    !confirm(
      `テーブル「${targetTableForRelease.value.tableNumber}」の着座を解除します。\nこの操作は元に戻せません。実行してよろしいですか？`
    )
  ) {
    return
  }

  isForceReleasing.value = true
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase

    await $fetch(`${apiBase}/visitors/${targetTableForRelease.value.visitorId}/force-release`, {
      method: 'PUT',
      body: {
        treatOrdersAsPaid: treatOrdersAsPaid.value
      }
    })

    closeForceReleaseModal()
    await fetchAllTables()
    alert('着座状態を解除しました')
  } catch (error: any) {
    console.error('着座強制解除に失敗しました:', error)
    alert(error?.data?.error || error?.message || '着座強制解除に失敗しました')
  } finally {
    isForceReleasing.value = false
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

    // 全店舗のテーブルを取得
    await fetchAllTables()

    // 初期状態では表示対象店舗をすべて展開
    expandedShopIds.value = shopsForDisplay.value.map((shop) => shop.id)
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

