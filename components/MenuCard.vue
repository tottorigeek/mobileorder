<template>
  <div
    :class="[
      'bg-white rounded-lg shadow p-4 transition-all hover:shadow-lg',
      !menu.isAvailable && 'opacity-50'
    ]"
  >
    <div class="flex gap-4">
      <div v-if="menu.imageUrl" class="flex-shrink-0">
        <img
          :src="menu.imageUrl"
          :alt="menu.name"
          class="w-24 h-24 object-cover rounded"
        />
      </div>
      <div class="flex-1">
        <div class="flex items-start justify-between mb-2">
          <div>
            <span class="text-lg font-bold text-blue-600">{{ menu.number }}</span>
            <h3 class="text-lg font-semibold inline-block ml-2">{{ menu.name }}</h3>
            <span
              v-if="menu.isRecommended"
              class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded"
            >
              おすすめ
            </span>
          </div>
        </div>
        <p v-if="menu.description" class="text-sm text-gray-600 mb-2">
          {{ menu.description }}
        </p>
        <div class="flex items-center justify-between">
          <span class="text-xl font-bold text-gray-900">
            ¥{{ menu.price.toLocaleString() }}
          </span>
          <div v-if="menu.isAvailable" class="flex items-center gap-3">
            <!-- 数量設定 -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-lg">
              <button
                @click="decreaseQuantity"
                :disabled="quantity <= 1"
                class="w-8 h-8 rounded-l-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center font-bold text-gray-700 transition-colors touch-target"
              >
                -
              </button>
              <input
                v-model.number="quantity"
                type="number"
                min="1"
                max="99"
                class="w-12 h-8 text-center text-sm font-semibold bg-transparent border-0 focus:ring-0 focus:outline-none"
                @input="handleQuantityInput"
              />
              <button
                @click="increaseQuantity"
                :disabled="quantity >= 99"
                class="w-8 h-8 rounded-r-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center font-bold text-gray-700 transition-colors touch-target"
              >
                +
              </button>
            </div>
            <button
              @click="showConfirmModal = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors touch-target"
            >
              注文する
            </button>
          </div>
          <span v-else class="px-4 py-2 bg-gray-300 text-gray-600 rounded-lg">
            在庫切れ
          </span>
        </div>
      </div>
    </div>
    
    <!-- 確認モーダル -->
    <div
      v-if="showConfirmModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showConfirmModal = false"
    >
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">メニューを追加しますか？</h3>
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-gray-800">{{ menu.number }}. {{ menu.name }}</span>
          </div>
          <div class="text-sm text-gray-600">
            <p>数量: {{ quantity }}</p>
            <p class="text-lg font-bold text-blue-600 mt-1">¥{{ (menu.price * quantity).toLocaleString() }}</p>
          </div>
        </div>
        <div class="flex gap-3">
          <button
            @click="showConfirmModal = false"
            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors touch-target"
          >
            キャンセル
          </button>
          <button
            @click="confirmAddToCart"
            class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
          >
            追加する
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Menu } from '~/types'
import { useCartStore } from '~/stores/cart'

interface Props {
  menu: Menu
}

const props = defineProps<Props>()
const cartStore = useCartStore()

const quantity = ref(1)
const showConfirmModal = ref(false)

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const increaseQuantity = () => {
  if (quantity.value < 99) {
    quantity.value++
  }
}

const handleQuantityInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  const value = parseInt(target.value) || 1
  if (value < 1) {
    quantity.value = 1
  } else if (value > 99) {
    quantity.value = 99
  } else {
    quantity.value = value
  }
}

const handleAddToCart = () => {
  showConfirmModal.value = true
}

const confirmAddToCart = () => {
  cartStore.addItem(props.menu, quantity.value)
  // モーダルを閉じる
  showConfirmModal.value = false
  // 追加後、数量を1にリセット
  quantity.value = 1
  // フィードバック（トーストなど）を表示する場合はここで実装
}
</script>

