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
              @click="handleAddToCart"
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
  cartStore.addItem(props.menu, quantity.value)
  // 追加後、数量を1にリセット
  quantity.value = 1
  // フィードバック（トーストなど）を表示する場合はここで実装
}
</script>

