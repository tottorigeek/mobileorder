<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-2xl z-50 safe-area-bottom">
    <div class="flex items-center justify-around h-16 px-2">
      <!-- メニュー -->
      <NuxtLink
        to="/visitor"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          isActive('/visitor') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="text-xs font-medium">メニュー</span>
      </NuxtLink>

      <!-- 未発注 -->
      <NuxtLink
        to="/visitor/cart"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors relative',
          isActive('/visitor/cart') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <!-- フワフワ動く吹き出し -->
        <div
          v-if="cartStore.totalItems > 0"
          class="absolute -top-12 left-1/2 transform -translate-x-1/2 floating-bubble"
        >
          <div class="bg-orange-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap relative">
            未注文があります
            <!-- 吹き出しのしっぽ -->
            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full">
              <div class="w-0 h-0 border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent border-t-orange-500"></div>
            </div>
          </div>
        </div>
        <div class="relative">
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span
            v-if="cartStore.totalItems > 0"
            class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartStore.totalItems }}
          </span>
        </div>
        <span class="text-xs font-medium">未発注</span>
      </NuxtLink>

      <!-- 注文一覧 -->
      <NuxtLink
        to="/visitor/orders"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          isActive('/visitor/orders') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span class="text-xs font-medium">注文</span>
      </NuxtLink>

      <!-- お会計 -->
      <button
        v-if="activeOrderId"
        @click="goToCheckout"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          isActive('/visitor/status') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-xs font-medium">お会計</span>
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'

const cartStore = useCartStore()
const route = useRoute()
const router = useRouter()

const activeOrderId = ref<string | null>(null)

const isActive = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const goToCheckout = () => {
  if (activeOrderId.value) {
    router.push(`/visitor/status/${activeOrderId.value}`)
  }
}

// アクティブな注文IDを監視
watchEffect(() => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
  }
})

// ページ遷移時と定期的にactiveOrderIdを更新
onMounted(() => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
    
    // 定期的にチェック（同じタブ内での変更も検知）
    const interval = setInterval(() => {
      activeOrderId.value = localStorage.getItem('activeOrderId')
    }, 500)
    
    onUnmounted(() => {
      clearInterval(interval)
    })
  }
})

// ページ遷移時にactiveOrderIdを更新
watch(() => route.path, () => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
  }
})
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}

/* フワフワ動くアニメーション */
@keyframes float {
  0%, 100% {
    transform: translate(-50%, 0);
  }
  50% {
    transform: translate(-50%, -8px);
  }
}

.floating-bubble {
  animation: float 2s ease-in-out infinite;
  z-index: 60;
}
</style>
