<template>
  <NuxtLayout name="visitor" title="注文書">
    <div v-if="cartStore.isEmpty" class="text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <p class="text-gray-500 text-xl font-medium mb-6">注文書が空です</p>
      <NuxtLink
        to="/visitor"
        class="inline-block px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target font-semibold text-lg"
      >
        メニューに戻る
      </NuxtLink>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- 注文書アイテム一覧 -->
      <div class="space-y-4">
        <div
          v-for="item in cartStore.items"
          :key="item.menu.id"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-300 relative"
        >
          <!-- 削除ボタン -->
          <button
            @click="removeItem(item.menu.id)"
            class="absolute top-4 right-4 w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-700 flex items-center justify-center transition-all touch-target group"
            title="削除"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          
          <div class="flex justify-between items-start mb-4 pr-10">
            <div class="flex-1">
              <h3 class="font-bold text-xl text-gray-900 mb-2">{{ item.menu.number }}. {{ item.menu.name }}</h3>
              <p class="text-lg font-semibold text-blue-600">¥{{ item.menu.price.toLocaleString() }}</p>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-2">
              <button
                @click="cartStore.updateQuantity(item.menu.id, item.quantity - 1)"
                class="w-10 h-10 rounded-xl bg-white border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 flex items-center justify-center touch-target transition-all font-bold text-gray-700 hover:text-blue-600"
              >
                -
              </button>
              <span class="text-xl font-bold w-10 text-center text-gray-900">{{ item.quantity }}</span>
              <button
                @click="cartStore.updateQuantity(item.menu.id, item.quantity + 1)"
                class="w-10 h-10 rounded-xl bg-white border-2 border-gray-200 hover:border-blue-500 hover:bg-blue-50 flex items-center justify-center touch-target transition-all font-bold text-gray-700 hover:text-blue-600"
              >
                +
              </button>
            </div>
          </div>
          <div class="pt-4 border-t border-gray-200 text-right">
            <span class="text-xl font-bold text-gray-900">小計: <span class="text-blue-600">¥{{ (item.menu.price * item.quantity).toLocaleString() }}</span></span>
          </div>
        </div>
      </div>

      <!-- 合計 -->
      <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl shadow-xl text-white">
        <div class="flex justify-between items-center">
          <span class="text-2xl font-bold">合計</span>
          <span class="text-3xl font-bold">¥{{ cartStore.totalAmount.toLocaleString() }}</span>
        </div>
      </div>

      <!-- 注文ボタン -->
      <button
        @click="handleOrder"
        :disabled="!cartStore.tableNumber || isSubmitting"
        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-5 rounded-xl text-xl font-bold hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target"
      >
        {{ isSubmitting ? '注文中...' : '注文を確定する' }}
      </button>

      <!-- bottom-nav用のスペーサー -->
      <div class="h-20"></div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useOrder } from '~/composables/useOrder'

const cartStore = useCartStore()
const { submitOrder } = useOrder()
const isSubmitting = ref(false)

onMounted(() => {
  // テーブル番号が設定されていない場合は店舗選択ページにリダイレクト
  if (!cartStore.tableNumber) {
    navigateTo('/shop-select')
  }
})

const removeItem = (menuId: string) => {
  cartStore.removeItem(menuId)
}

const handleOrder = async () => {
  if (!cartStore.tableNumber) {
    alert('テーブル番号が設定されていません。店舗選択画面から再度選択してください。')
    await navigateTo('/shop-select')
    return
  }

  if (confirm('注文を確定しますか？')) {
    isSubmitting.value = true
    try {
      const order = await submitOrder()
      await navigateTo(`/visitor/order/${order.id}`)
    } catch (error) {
      alert('注文に失敗しました: ' + (error as Error).message)
    } finally {
      isSubmitting.value = false
    }
  }
}
</script>

