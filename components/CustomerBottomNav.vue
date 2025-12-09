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
        @click="goToCheckout"
        :disabled="!canCheckout"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full transition-colors',
          canCheckout
            ? isActive('/visitor/status') 
              ? 'text-blue-600' 
              : 'text-gray-500 hover:text-blue-600'
            : 'text-gray-300 cursor-not-allowed',
          !canCheckout && 'opacity-50'
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
import { useOrderStore } from '~/stores/order'
import { useVisitorStore } from '~/stores/visitor'
import type { Visitor } from '~/types'

const cartStore = useCartStore()
const orderStore = useOrderStore()
const visitorStore = useVisitorStore()
const route = useRoute()
const router = useRouter()

const activeOrderId = ref<string | null>(null)
const currentVisitor = ref<Visitor | null>(null)

const isActive = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

// 会計可能かどうかを判定
const canCheckout = computed(() => {
  // visitorIdが存在する
  const visitorId = cartStore.visitorId || (typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null)
  if (!visitorId) return false
  
  // visitorのpaymentStatusが'pending'である（支払いが完了していない）
  if (currentVisitor.value && currentVisitor.value.paymentStatus === 'completed') {
    return false
  }
  
  // 完成した注文（status='completed'）が存在する
  const completedOrders = orderStore.orders.filter(order => order.status === 'completed')
  if (completedOrders.length === 0) {
    // ローカルストレージからactiveOrderIdを確認
    const storedOrderId = typeof window !== 'undefined' ? localStorage.getItem('activeOrderId') : null
    return !!storedOrderId
  }
  
  return true
})

// 会計ページに遷移するための注文IDを取得
const checkoutOrderId = computed(() => {
  // 完成した注文のうち最新のものを取得
  const completedOrders = orderStore.orders
    .filter(order => order.status === 'completed')
    .sort((a, b) => {
      const dateA = typeof a.createdAt === 'string' ? new Date(a.createdAt) : a.createdAt
      const dateB = typeof b.createdAt === 'string' ? new Date(b.createdAt) : b.createdAt
      return dateB.getTime() - dateA.getTime()
    })
  
  if (completedOrders.length > 0) {
    return completedOrders[0].id
  }
  
  // ローカルストレージからactiveOrderIdを取得
  return typeof window !== 'undefined' ? localStorage.getItem('activeOrderId') : null
})

const goToCheckout = () => {
  if (!canCheckout.value) return
  
  const orderId = checkoutOrderId.value
  if (orderId) {
    router.push(`/visitor/status/${orderId}`)
  } else {
    // 注文IDが見つからない場合は注文一覧ページに遷移
    router.push('/visitor/orders')
  }
}

// visitor情報を取得
const fetchVisitorInfo = async () => {
  const visitorId = cartStore.visitorId || (typeof window !== 'undefined' ? localStorage.getItem('activeVisitorId') : null)
  if (visitorId) {
    try {
      currentVisitor.value = await visitorStore.fetchVisitor(visitorId)
    } catch (error) {
      console.error('visitor情報の取得に失敗しました:', error)
      currentVisitor.value = null
    }
  } else {
    currentVisitor.value = null
  }
}

// アクティブな注文IDを監視
watchEffect(() => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
  }
})

// ページ遷移時と定期的にactiveOrderIdとvisitor情報を更新
onMounted(async () => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
    await fetchVisitorInfo()
    
    // 定期的にチェック（同じタブ内での変更も検知）
    const interval = setInterval(async () => {
      activeOrderId.value = localStorage.getItem('activeOrderId')
      await fetchVisitorInfo()
    }, 1000)
    
    onUnmounted(() => {
      clearInterval(interval)
    })
  }
})

// ページ遷移時にactiveOrderIdとvisitor情報を更新
watch(() => route.path, async () => {
  if (typeof window !== 'undefined') {
    activeOrderId.value = localStorage.getItem('activeOrderId')
    await fetchVisitorInfo()
  }
})

// orderStoreのordersが変更されたときに再評価
watch(() => orderStore.orders, () => {
  // 会計可能状態を再評価するために何もしない（computedが自動的に再計算される）
}, { deep: true })
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
