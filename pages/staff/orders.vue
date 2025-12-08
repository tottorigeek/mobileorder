<template>
  <NuxtLayout name="default" title="注文管理">
    <div class="space-y-4">
      <!-- ステータスフィルター -->
      <div class="flex gap-2 overflow-x-auto pb-2">
        <button
          v-for="status in statuses"
          :key="status.value"
          :class="[
            'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors touch-target',
            selectedStatus === status.value
              ? 'bg-purple-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
          @click="selectedStatus = status.value"
        >
          {{ status.label }}
        </button>
      </div>

      <!-- 注文一覧 -->
      <div v-if="orderStore.isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white p-4 rounded-lg shadow"
        >
          <div class="flex justify-between items-start mb-3">
            <div>
              <h3 class="text-lg font-semibold">注文番号: {{ order.orderNumber }}</h3>
              <p class="text-sm text-gray-600">テーブル: {{ order.tableNumber }}</p>
              <p class="text-sm text-gray-600">
                {{ new Date(order.createdAt).toLocaleString('ja-JP') }}
              </p>
            </div>
            <span :class="getStatusClass(order.status)">
              {{ getStatusLabel(order.status) }}
            </span>
          </div>

          <div class="mb-3">
            <h4 class="font-medium mb-2">注文内容:</h4>
            <ul class="space-y-1">
              <li
                v-for="item in order.items"
                :key="item.menuId"
                class="text-sm"
              >
                {{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}
              </li>
            </ul>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-lg font-bold">合計: ¥{{ order.totalAmount.toLocaleString() }}</span>
            <div class="flex gap-2">
              <button
                v-if="order.status === 'pending'"
                @click="updateStatus(order.id, 'accepted')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors touch-target"
              >
                受付
              </button>
              <button
                v-if="order.status === 'accepted'"
                @click="updateStatus(order.id, 'cooking')"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors touch-target"
              >
                調理開始
              </button>
              <button
                v-if="order.status === 'cooking'"
                @click="updateStatus(order.id, 'completed')"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors touch-target"
              >
                完成
              </button>
            </div>
          </div>
        </div>

        <div v-if="filteredOrders.length === 0" class="text-center py-12 text-gray-500">
          注文がありません
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import type { OrderStatus } from '~/types'

const orderStore = useOrderStore()
const selectedStatus = ref<OrderStatus | 'all'>('all')

const statuses = [
  { value: 'all', label: 'すべて' },
  { value: 'pending', label: '受付待ち' },
  { value: 'accepted', label: '受付済み' },
  { value: 'cooking', label: '調理中' },
  { value: 'completed', label: '完成' }
]

const filteredOrders = computed(() => {
  if (selectedStatus.value === 'all') {
    return orderStore.orders
  }
  return orderStore.ordersByStatus(selectedStatus.value)
})

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

const updateStatus = async (orderId: string, status: OrderStatus) => {
  await orderStore.updateOrderStatus(orderId, status)
}

onMounted(() => {
  orderStore.fetchOrders()
})
</script>

