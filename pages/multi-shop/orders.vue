<template>
  <NuxtLayout name="default" title="注文一覧">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/multi-shop/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/orders"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          注文一覧
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/menus"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          メニュー管理
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/staff"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          スタッフ管理
        </NuxtLink>
        <NuxtLink
          to="/shop/users/password"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          パスワード変更
        </NuxtLink>
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
        >
          ログアウト
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
              @change="filterOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべての店舗</option>
              <option
                v-for="shop in myShops"
                :key="shop.id"
                :value="shop.id"
              >
                {{ shop.name }}
              </option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              ステータスで絞り込み
            </label>
            <select
              v-model="selectedStatus"
              @change="filterOrders"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="pending">受付待ち</option>
              <option value="accepted">受付済み</option>
              <option value="cooking">調理中</option>
              <option value="completed">完成</option>
              <option value="cancelled">キャンセル</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="orderStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- 注文一覧 -->
      <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-gray-500">
        注文がありません
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <p class="font-semibold text-lg">{{ order.orderNumber }}</p>
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ getShopName(order.shopId) }}
                </span>
                <span :class="getStatusClass(order.status)">
                  {{ getStatusLabel(order.status) }}
                </span>
              </div>
              <p class="text-sm text-gray-600">テーブル: {{ order.tableNumber }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(order.createdAt) }}</p>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-green-600">¥{{ order.totalAmount.toLocaleString() }}</p>
            </div>
          </div>

          <!-- 注文アイテム -->
          <div class="border-t pt-3 mt-3">
            <div class="space-y-1">
              <div
                v-for="item in order.items"
                :key="item.menuId"
                class="flex justify-between text-sm"
              >
                <span>{{ item.menuName }} × {{ item.quantity }}</span>
                <span>¥{{ (item.price * item.quantity).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- アクションボタン -->
          <div class="flex gap-2 mt-4 pt-3 border-t">
            <button
              v-if="order.status === 'pending'"
              @click="updateOrderStatus(order.id, 'accepted')"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm touch-target"
            >
              受付
            </button>
            <button
              v-if="order.status === 'accepted'"
              @click="updateOrderStatus(order.id, 'cooking')"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm touch-target"
            >
              調理開始
            </button>
            <button
              v-if="order.status === 'cooking'"
              @click="updateOrderStatus(order.id, 'completed')"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm touch-target"
            >
              完成
            </button>
            <button
              v-if="['pending', 'accepted', 'cooking'].includes(order.status)"
              @click="updateOrderStatus(order.id, 'cancelled')"
              class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm touch-target ml-auto"
            >
              キャンセル
            </button>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { OrderStatus, Shop, Order } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()
const orderStore = useOrderStore()

const myShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const selectedStatus = ref<string>('')
const filteredOrders = ref<Order[]>([])

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const filterOrders = () => {
  let orders = orderStore.orders

  if (selectedShopId.value) {
    orders = orders.filter(o => o.shopId === selectedShopId.value)
  }

  if (selectedStatus.value) {
    orders = orders.filter(o => o.status === selectedStatus.value)
  }

  filteredOrders.value = orders.sort((a, b) => {
    return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
  })
}

const getShopName = (shopId: string) => {
  const shop = myShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const getStatusLabel = (status: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル'
  }
  return labels[status]
}

const getStatusClass = (status: OrderStatus) => {
  const classes: Record<OrderStatus, string> = {
    pending: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm',
    accepted: 'px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm',
    cooking: 'px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm',
    completed: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm',
    cancelled: 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm'
  }
  return classes[status]
}

const formatDate = (date: Date | string) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return d.toLocaleString('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const updateOrderStatus = async (orderId: string, status: OrderStatus) => {
  try {
    await orderStore.updateOrderStatus(orderId, status)
    filterOrders()
  } catch (error: any) {
    alert(error?.data?.error || 'ステータスの更新に失敗しました')
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }

  // 所属店舗一覧を取得
  try {
    myShops.value = await shopStore.fetchMyShops()
    
    if (myShops.value.length === 0) {
      await navigateTo('/shop/dashboard')
      return
    }

    // 注文一覧を取得（全店舗分）
    const shopIds = myShops.value.map(s => s.id)
    await orderStore.fetchOrders(undefined, undefined, shopIds)
    filterOrders()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

