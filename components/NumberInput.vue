<template>
  <div class="space-y-4">
    <div class="bg-white p-6 rounded-lg shadow">
      <label class="block text-lg font-semibold mb-4 text-center">
        メニュー番号を入力
      </label>
      <div class="flex items-center justify-center gap-2 mb-4">
        <input
          v-model="inputValue"
          type="text"
          inputmode="numeric"
          pattern="[0-9]*"
          maxlength="3"
          class="w-32 px-4 py-4 text-3xl font-bold text-center border-2 border-blue-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="001"
          @input="handleInput"
          @keyup.enter="handleSubmit"
        />
      </div>
      <button
        @click="handleSubmit"
        :disabled="!isValid"
        class="w-full bg-blue-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
      >
        メニューを追加
      </button>
    </div>

    <!-- 数字キーパッド -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-3 gap-3">
        <button
          v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
          :key="num"
          @click="appendNumber(num.toString())"
          class="aspect-square bg-gray-100 hover:bg-gray-200 rounded-lg text-2xl font-bold transition-colors touch-target"
        >
          {{ num }}
        </button>
        <button
          @click="clearInput"
          class="aspect-square bg-gray-300 hover:bg-gray-400 rounded-lg text-xl font-semibold transition-colors touch-target"
        >
          C
        </button>
        <button
          @click="appendNumber('0')"
          class="aspect-square bg-gray-100 hover:bg-gray-200 rounded-lg text-2xl font-bold transition-colors touch-target"
        >
          0
        </button>
        <button
          @click="handleSubmit"
          :disabled="!isValid"
          class="aspect-square bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-lg text-xl font-semibold text-white transition-colors touch-target"
        >
          ✓
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useMenuStore } from '~/stores/menu'
import { useCartStore } from '~/stores/cart'

const menuStore = useMenuStore()
const cartStore = useCartStore()

const inputValue = ref('')

const isValid = computed(() => {
  return inputValue.value.length > 0 && menuStore.getMenuByNumber(inputValue.value) !== undefined
})

const appendNumber = (num: string) => {
  if (inputValue.value.length < 3) {
    inputValue.value += num
  }
}

const clearInput = () => {
  inputValue.value = ''
}

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  inputValue.value = target.value.replace(/[^0-9]/g, '').slice(0, 3)
}

const handleSubmit = () => {
  if (!isValid.value) return

  const menu = menuStore.getMenuByNumber(inputValue.value)
  if (menu && menu.isAvailable) {
    cartStore.addItem(menu, 1)
    inputValue.value = ''
    // 成功フィードバック（トーストなど）
  } else if (menu && !menu.isAvailable) {
    alert('このメニューは現在在庫切れです')
  } else {
    alert('メニュー番号が見つかりません')
  }
}
</script>

