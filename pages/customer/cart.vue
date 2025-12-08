<template>
  <NuxtLayout name="default" title="カート">
    <div v-if="cartStore.isEmpty" class="text-center py-12">
      <p class="text-gray-500 text-lg mb-4">カートが空です</p>
      <NuxtLink
        to="/customer"
        class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors touch-target"
      >
        メニューに戻る
      </NuxtLink>
    </div>

    <div v-else class="space-y-4">
      <!-- カートアイテム一覧 -->
      <div class="space-y-3">
        <div
          v-for="item in cartStore.items"
          :key="item.menu.id"
          class="bg-white p-4 rounded-lg shadow"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="font-semibold text-lg">{{ item.menu.number }}. {{ item.menu.name }}</h3>
              <p class="text-gray-600">¥{{ item.menu.price.toLocaleString() }}</p>
            </div>
            <div class="flex items-center gap-3">
              <button
                @click="cartStore.updateQuantity(item.menu.id, item.quantity - 1)"
                class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center touch-target"
              >
                -
              </button>
              <span class="text-lg font-semibold w-8 text-center">{{ item.quantity }}</span>
              <button
                @click="cartStore.updateQuantity(item.menu.id, item.quantity + 1)"
                class="w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center touch-target"
              >
                +
              </button>
            </div>
          </div>
          <div class="mt-2 text-right">
            <span class="text-lg font-bold">小計: ¥{{ (item.menu.price * item.quantity).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <!-- 合計 -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex justify-between items-center text-xl font-bold">
          <span>合計</span>
          <span>¥{{ cartStore.totalAmount.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 注文ボタン -->
      <button
        @click="handleOrder"
        :disabled="!cartStore.tableNumber || isSubmitting"
        class="w-full bg-blue-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
      >
        {{ isSubmitting ? '注文中...' : '注文を確定する' }}
      </button>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useOrder } from '~/composables/useOrder'

const cartStore = useCartStore()
const { submitOrder } = useOrder()
const isSubmitting = ref(false)

const handleOrder = async () => {
  if (!cartStore.tableNumber) {
    alert('テーブル番号を入力してください')
    return
  }

  if (confirm('注文を確定しますか？')) {
    isSubmitting.value = true
    try {
      const order = await submitOrder()
      await navigateTo(`/customer/order/${order.id}`)
    } catch (error) {
      alert('注文に失敗しました: ' + (error as Error).message)
    } finally {
      isSubmitting.value = false
    }
  }
}
</script>

