<template>
  <div class="space-y-4">
    <div class="bg-white p-6 rounded-lg shadow relative">
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
          class="w-32 px-4 py-4 text-3xl font-bold text-center border-2 rounded-lg focus:ring-2 transition-colors"
          :class="inputValue && foundMenu ? 'border-green-500 focus:ring-green-500 focus:border-green-500' : inputValue && !foundMenu ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-blue-500 focus:ring-blue-500 focus:border-blue-500'"
          placeholder="001"
          @input="handleInput"
          @keyup.enter="handleSubmit"
        />
      </div>
      
      <!-- メニュープレビュー・エラーメッセージ・在庫切れメッセージ用のスペース確保 -->
      <div class="mb-4 min-h-[120px] space-y-2">
        <!-- メニュープレビュー -->
        <div v-if="inputValue && foundMenu" class="p-3 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-green-800">{{ foundMenu.number }}. {{ foundMenu.name }}</p>
              <p class="text-sm text-green-600">¥{{ foundMenu.price.toLocaleString() }}</p>
            </div>
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        
        <!-- エラーメッセージ -->
        <div v-else-if="inputValue && !foundMenu && inputValue.length >= 1" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-700">メニュー番号「{{ formatMenuNumber(inputValue) }}」が見つかりません</p>
        </div>
        
        <!-- 在庫切れメッセージ（メニューが見つかった場合のみ表示） -->
        <div v-if="inputValue && foundMenu && !foundMenu.isAvailable" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-yellow-700">このメニューは現在在庫切れです</p>
        </div>
      </div>
      
      <button
        @click="showConfirmModal = true"
        :disabled="!isValid"
        class="w-full bg-blue-600 text-white py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors touch-target"
      >
        メニューを追加
      </button>
      
      <!-- 成功メッセージ（absolute配置でレイアウトシフトを防止） -->
      <transition name="fade">
        <div
          v-if="showSuccessMessage"
          class="absolute bottom-full left-0 right-0 mb-2 p-3 bg-green-500 text-white rounded-lg text-center font-semibold shadow-lg z-10"
        >
          <div class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>注文書に追加しました</span>
          </div>
        </div>
      </transition>
      
      <!-- 確認モーダル -->
      <div
        v-if="showConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showConfirmModal = false"
      >
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
          <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">メニューを追加しますか？</h3>
          <div v-if="foundMenu" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <span class="font-semibold text-gray-800">{{ foundMenu.number }}. {{ foundMenu.name }}</span>
            </div>
            <div class="text-sm text-gray-600">
              <p>数量: 1</p>
              <p class="text-lg font-bold text-blue-600 mt-1">¥{{ foundMenu.price.toLocaleString() }}</p>
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
              @click="confirmAddMenu"
              class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors touch-target"
            >
              追加する
            </button>
          </div>
        </div>
      </div>
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
          @click="showConfirmModal = true"
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
const showSuccessMessage = ref(false)
const showConfirmModal = ref(false)

// メニュー番号を3桁のゼロパディング形式に変換（例: "1" -> "001", "01" -> "001"）
const formatMenuNumber = (num: string): string => {
  if (!num) return ''
  const numValue = parseInt(num, 10)
  if (isNaN(numValue)) return num
  return numValue.toString().padStart(3, '0')
}

// 入力された番号でメニューを検索（ゼロパディング対応）
const foundMenu = computed(() => {
  if (!inputValue.value) return null
  
  // まず、入力された値そのままで検索
  let menu = menuStore.getMenuByNumber(inputValue.value)
  
  // 見つからない場合、ゼロパディングした値で検索
  if (!menu && inputValue.value.length > 0) {
    const formattedNumber = formatMenuNumber(inputValue.value)
    menu = menuStore.getMenuByNumber(formattedNumber)
  }
  
  return menu || null
})

const isValid = computed(() => {
  return foundMenu.value !== null && foundMenu.value.isAvailable
})

const appendNumber = (num: string) => {
  if (inputValue.value.length < 3) {
    inputValue.value += num
  }
}

const clearInput = () => {
  inputValue.value = ''
  showSuccessMessage.value = false
}

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  inputValue.value = target.value.replace(/[^0-9]/g, '').slice(0, 3)
}

const handleSubmit = () => {
  if (!isValid.value || !foundMenu.value) return
  showConfirmModal.value = true
}

const confirmAddMenu = () => {
  if (!isValid.value || !foundMenu.value) return

  const menu = foundMenu.value
  if (menu.isAvailable) {
    cartStore.addItem(menu, 1)
    
    // モーダルを閉じる
    showConfirmModal.value = false
    
    // 成功メッセージを表示
    showSuccessMessage.value = true
    
    // 入力フィールドをクリア（少し遅延させて成功メッセージを見せる）
    setTimeout(() => {
      inputValue.value = ''
      showSuccessMessage.value = false
    }, 1500)
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

