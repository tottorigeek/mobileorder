<template>
  <NuxtLayout name="default" title="店舗選択" :show-header="false">
    <div class="flex flex-col items-center justify-center min-h-screen p-6 bg-gray-50">
      <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8 text-gray-900">
          店舗を選択してください
        </h1>

        <div v-if="shopStore.isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p class="mt-4 text-gray-500">読み込み中...</p>
        </div>

        <div v-else-if="shopStore.shops.length === 0" class="text-center py-12">
          <p class="text-gray-500 mb-4">店舗が見つかりません</p>
        </div>

        <div v-else class="space-y-3">
          <button
            v-for="shop in shopStore.shops"
            :key="shop.id"
            @click="selectShop(shop)"
            class="w-full p-6 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left touch-target"
          >
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ shop.name }}</h3>
            <p v-if="shop.description" class="text-gray-600 text-sm mb-2">{{ shop.description }}</p>
            <p v-if="shop.address" class="text-gray-500 text-xs">{{ shop.address }}</p>
          </button>
        </div>

        <div class="mt-6 text-center">
          <NuxtLink
            to="/"
            class="text-blue-600 hover:text-blue-700"
          >
            トップに戻る
          </NuxtLink>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import type { Shop } from '~/types'

const shopStore = useShopStore()
const router = useRouter()
const route = useRoute()
const mode = route.query.mode as string || 'customer'

onMounted(async () => {
  await shopStore.fetchShops()
})

const selectShop = (shop: Shop) => {
  shopStore.setCurrentShop(shop)
  
  if (mode === 'staff') {
    router.push(`/staff/login?shop=${shop.code}`)
  } else {
    router.push(`/customer?shop=${shop.code}`)
  }
}
</script>

