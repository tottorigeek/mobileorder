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
          <button
            v-if="menu.isAvailable"
            @click="handleAddToCart"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors touch-target"
          >
            カートに追加
          </button>
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

const handleAddToCart = () => {
  cartStore.addItem(props.menu, 1)
  // フィードバック（トーストなど）を表示する場合はここで実装
}
</script>

