<template>
  <NuxtLayout name="default" title="注文確認">
    <div v-if="order" class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">注文が完了しました</h2>
        <div class="space-y-2">
          <p><span class="font-semibold">注文番号:</span> {{ order.orderNumber }}</p>
          <p><span class="font-semibold">テーブル番号:</span> {{ order.tableNumber }}</p>
          <p><span class="font-semibold">合計金額:</span> ¥{{ order.totalAmount.toLocaleString() }}</p>
          <p><span class="font-semibold">ステータス:</span> 
            <span :class="statusClass">{{ statusLabel }}</span>
          </p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-bold mb-4">注文内容</h3>
        <div class="space-y-3">
          <div
            v-for="item in order.items"
            :key="item.menuId"
            class="flex justify-between"
          >
            <span>{{ item.menuNumber }}. {{ item.menuName }} × {{ item.quantity }}</span>
            <span>¥{{ (item.price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <NuxtLink
          to="/customer"
          class="block w-full bg-blue-600 text-white py-4 rounded-lg text-center text-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
        >
          メニューに戻る
        </NuxtLink>
        <NuxtLink
          :to="`/customer/status/${order.id}`"
          class="block w-full bg-gray-600 text-white py-4 rounded-lg text-center text-lg font-semibold hover:bg-gray-700 transition-colors touch-target"
        >
          注文状況を確認
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useOrderStore } from '~/stores/order'
import { useCartStore } from '~/stores/cart'
import type { OrderStatus } from '~/types'

const route = useRoute()
const orderStore = useOrderStore()
const cartStore = useCartStore()

const order = computed(() => {
  return orderStore.orders.find(o => o.id === route.params.id as string)
})

// 注文完了時にセッション情報を保存（精算完了まで/shop-selectから/customerにリダイレクトするため）
onMounted(() => {
  if (order.value && cartStore.visitorId) {
    // ローカルストレージにアクティブな注文IDとvisitorIdを保存
    if (typeof window !== 'undefined') {
      localStorage.setItem('activeOrderId', order.value.id)
      localStorage.setItem('activeVisitorId', cartStore.visitorId)
    }
  }
})

const statusLabel = computed(() => {
  const labels: Record<OrderStatus, string> = {
    pending: '受付待ち',
    accepted: '受付済み',
    cooking: '調理中',
    completed: '完成',
    cancelled: 'キャンセル',
    checkout_pending: '会計前'
  }
  return order.value ? labels[order.value.status] : ''
})

const statusClass = computed(() => {
  const classes: Record<OrderStatus, string> = {
    pending: 'text-yellow-600',
    accepted: 'text-blue-600',
    cooking: 'text-orange-600',
    completed: 'text-green-600',
    cancelled: 'text-red-600',
    checkout_pending: 'text-orange-600'
  }
  return order.value ? classes[order.value.status] : ''
})
</script>

