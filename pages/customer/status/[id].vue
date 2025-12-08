<template>
  <NuxtLayout name="default" title="注文状況">
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-500">読み込み中...</p>
    </div>
    
    <div v-else-if="order" class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">注文状況</h2>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">注文番号:</span>
            <span class="font-semibold">{{ order.orderNumber }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">テーブル番号:</span>
            <span class="font-semibold">{{ order.tableNumber }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">ステータス:</span>
            <span :class="statusClass" class="font-semibold">{{ statusLabel }}</span>
          </div>
        </div>
      </div>

      <!-- ステータス進行バー -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="space-y-4">
          <div class="flex items-center">
            <div :class="getStepClass('pending')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('pending')">✓</span>
                <span v-else>1</span>
              </div>
              <p class="text-sm text-center">受付待ち</p>
            </div>
            <div :class="getLineClass('pending', 'accepted')" class="flex-1 h-1"></div>
            <div :class="getStepClass('accepted')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('accepted')">✓</span>
                <span v-else>2</span>
              </div>
              <p class="text-sm text-center">受付済み</p>
            </div>
            <div :class="getLineClass('accepted', 'cooking')" class="flex-1 h-1"></div>
            <div :class="getStepClass('cooking')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('cooking')">✓</span>
                <span v-else>3</span>
              </div>
              <p class="text-sm text-center">調理中</p>
            </div>
            <div :class="getLineClass('cooking', 'completed')" class="flex-1 h-1"></div>
            <div :class="getStepClass('completed')" class="flex-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-2">
                <span v-if="isStepCompleted('completed')">✓</span>
                <span v-else>4</span>
              </div>
              <p class="text-sm text-center">完成</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 注文内容 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">注文内容</h3>
        <div class="space-y-2">
          <div
            v-for="item in order.items"
            :key="item.menuId"
            class="flex justify-between py-2 border-b last:border-0"
          >
            <span>{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
            <span>¥{{ (item.price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t flex justify-between text-lg font-bold">
          <span>合計</span>
          <span>¥{{ order.totalAmount.toLocaleString() }}</span>
        </div>
      </div>

      <NuxtLink
        to="/customer"
        class="block w-full bg-blue-600 text-white py-4 rounded-lg text-center text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
      >
        メニューに戻る
      </NuxtLink>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-500">注文が見つかりません</p>
      <NuxtLink
        to="/customer"
        class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        メニューに戻る
      </NuxtLink>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import type { OrderStatus, Order } from '~/types'

const route = useRoute()
const orderStore = useOrderStore()
const orderId = route.params.id as string

// 注文を取得
const order = ref<Order | null>(null)
const isLoading = ref(true)

onMounted(async () => {
  // まずローカルストアから取得を試みる
  const localOrder = orderStore.orders.find(o => o.id === orderId)
  if (localOrder) {
    order.value = localOrder
    isLoading.value = false
  } else {
    // ローカルにない場合はAPIから取得
    try {
      const config = useRuntimeConfig()
      const apiBase = config.public.apiBase
      const orderData = await $fetch<Order>(`${apiBase}/orders/${orderId}`)
      order.value = {
        ...orderData,
        createdAt: new Date(orderData.createdAt),
        updatedAt: new Date(orderData.updatedAt)
      }
    } catch (error) {
      console.error('注文の取得に失敗しました:', error)
    } finally {
      isLoading.value = false
    }
  }
})

const statusLabel = computed(() => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル'
  }
  return order.value ? labels[order.value.status] : ''
})

const statusClass = computed(() => {
  const classes: Record<OrderStatus, string> = {
    pending: 'text-yellow-600',
    accepted: 'text-blue-600',
    cooking: 'text-orange-600',
    completed: 'text-green-600',
    cancelled: 'text-red-600'
  }
  return order.value ? classes[order.value.status] : ''
})

const statusOrder: OrderStatus[] = ['pending', 'accepted', 'cooking', 'completed']

const isStepCompleted = (status: OrderStatus) => {
  if (!order.value) return false
  const currentIndex = statusOrder.indexOf(order.value.status)
  const stepIndex = statusOrder.indexOf(status)
  return currentIndex > stepIndex || order.value.status === status
}

const getStepClass = (status: OrderStatus) => {
  if (!order.value) return ''
  const isCompleted = isStepCompleted(status)
  const isCurrent = order.value.status === status
  
  if (isCompleted) return 'text-green-600'
  if (isCurrent) return 'text-blue-600'
  return 'text-gray-400'
}

const getLineClass = (from: OrderStatus, to: OrderStatus) => {
  if (!order.value) return 'bg-gray-300'
  const currentIndex = statusOrder.indexOf(order.value.status)
  const toIndex = statusOrder.indexOf(to)
  
  if (currentIndex >= toIndex) return 'bg-green-600'
  return 'bg-gray-300'
}
</script>

