<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="blue"
      />
      <!-- ヘッダー -->
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">テーブル管理</h1>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
        >
          + テーブルを追加
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="tableStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500">読み込み中...</p>
      </div>

      <!-- テーブル一覧 -->
      <div v-else-if="tableStore.tables.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="table in tableStore.tables"
          :key="table.id"
          class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-900">
                テーブル {{ table.tableNumber }}
              </h3>
              <p v-if="table.name" class="text-sm text-gray-600 mt-1">{{ table.name }}</p>
            </div>
            <span
              :class="[
                'px-2 py-1 rounded text-xs font-semibold',
                table.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
              ]"
            >
              {{ table.isActive ? '有効' : '無効' }}
            </span>
          </div>

          <div class="space-y-2 mb-4">
            <p class="text-sm text-gray-600">
              <span class="font-medium">定員:</span> {{ table.capacity }}名
            </p>
            <p class="text-sm text-gray-600">
              <span class="font-medium">店舗:</span> {{ table.shopName }}
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="editTable(table)"
              class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium"
            >
              編集
            </button>
            <button
              @click="deleteTable(table)"
              class="flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium"
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
      <div v-else class="text-center py-12 text-gray-500">
        <p class="mb-4">テーブルが登録されていません</p>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
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

