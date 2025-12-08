<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-200 shadow-2xl z-50 safe-area-bottom">
    <div class="flex items-center justify-around h-16 px-2">
      <!-- メニュー -->
      <NuxtLink
        to="/customer"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          isActive('/customer') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="text-xs font-medium">メニュー</span>
      </NuxtLink>

      <!-- カート -->
      <NuxtLink
        to="/customer/cart"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors relative',
          isActive('/customer/cart') ? 'text-blue-600' : 'text-gray-500'
        ]"
      >
        <div class="relative">
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span
            v-if="cartStore.totalItems > 0"
            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ cartStore.totalItems }}
          </span>
        </div>
        <span class="text-xs font-medium">カート</span>
      </NuxtLink>

      <!-- お会計 -->
      <button
        v-if="activeOrderId"
        @click="goToCheckout"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          isActive('/customer/status') ? 'text-blue-600' : 'text-gray-500'
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
    router.push(`/customer/status/${activeOrderId.value}`)
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
</style>

