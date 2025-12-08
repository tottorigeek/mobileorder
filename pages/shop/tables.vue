<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />
      <!-- ヘッダー -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-1">テーブル管理</h1>
          <p class="text-gray-600">店舗のテーブルを管理します</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          テーブルを追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="tableStore.isLoading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600"></div>
        <p class="mt-4 text-gray-500 font-medium">読み込み中...</p>
      </div>

      <!-- テーブル一覧 -->
      <div v-else-if="tableStore.tables.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="table in tableStore.tables"
          :key="table.id"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-300"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900">
                  テーブル {{ table.tableNumber }}
                </h3>
                <p v-if="table.name" class="text-sm text-gray-600 mt-1">{{ table.name }}</p>
              </div>
            </div>
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-semibold',
                table.isActive ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md' : 'bg-gray-200 text-gray-700'
              ]"
            >
              {{ table.isActive ? '有効' : '無効' }}
            </span>
          </div>

          <div class="space-y-2 mb-4 p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-700 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="font-medium">定員:</span> {{ table.capacity }}名
            </p>
            <p class="text-sm text-gray-700 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span class="font-medium">店舗:</span> {{ table.shopName }}
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="editTable(table)"
              class="flex-1 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold"
            >
              編集
            </button>
            <button
              @click="deleteTable(table)"
              class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-md hover:shadow-lg text-sm font-semibold"
            >
              削除
            </button>
          </div>

          <!-- QRコード表示 -->
          <div class="mt-4 pt-4 border-t">
            <QRCodeGenerator
              :shop-code="shopStore.currentShop?.code || ''"
              :table-number="table.tableNumber"
            />
          </div>
        </div>
      </div>

      <!-- テーブルが存在しない場合 -->
      <div v-else class="text-center py-16 bg-white rounded-2xl shadow-lg">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 font-medium text-lg mb-6">テーブルが登録されていません</p>
        <button
          @click="showCreateModal = true"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                {{ editingTable ? '更新' : '作成' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useTableStore } from '~/stores/table'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import QRCodeGenerator from '~/components/QRCodeGenerator.vue'
import type { ShopTable } from '~/types'

const { navigationItems } = useShopNavigation()
const { pageTitle } = useShopPageTitle('テーブル管理')

const tableStore = useTableStore()
const authStore = useAuthStore()
const shopStore = useShopStore()
const { checkAuth } = useAuthCheck()

const showCreateModal = ref(false)
const editingTable = ref<ShopTable | null>(null)

const formData = ref({
  tableNumber: '',
  name: '',
  capacity: 4,
  isActive: true
})

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  // テーブル一覧の取得
  if (shopStore.currentShop) {
    await tableStore.fetchTables(shopStore.currentShop.id)
  }
})

const editTable = (table: ShopTable) => {
  editingTable.value = table
  formData.value = {
    tableNumber: table.tableNumber,
    name: table.name || '',
    capacity: table.capacity,
    isActive: table.isActive
  }
  showCreateModal.value = true
}

const saveTable = async () => {
  if (!shopStore.currentShop) {
    alert('店舗が選択されていません')
    return
  }

  try {
    if (editingTable.value) {
      // 更新
      await tableStore.updateTable(editingTable.value.id, formData.value)
    } else {
      // 作成
      await tableStore.createTable(shopStore.currentShop.id, formData.value)
    }
    closeModal()
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
</script>

