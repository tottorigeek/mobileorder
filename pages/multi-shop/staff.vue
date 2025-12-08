<template>
  <NuxtLayout name="default" title="スタッフ管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/multi-shop/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/orders"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          注文一覧
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/menus"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          メニュー管理
        </NuxtLink>
        <NuxtLink
          to="/multi-shop/staff"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          スタッフ管理
        </NuxtLink>
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
        >
          ログアウト
        </button>
      </div>

      <!-- フィルター -->
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗で絞り込み
            </label>
            <select
              v-model="selectedShopId"
              @change="filterStaff"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべての店舗</option>
              <option
                v-for="shop in myShops"
                :key="shop.id"
                :value="shop.id"
              >
                {{ shop.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- スタッフ一覧 -->
      <div v-else-if="filteredStaff.length === 0" class="text-center py-12 text-gray-500">
        スタッフが見つかりません
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="staff in filteredStaff"
          :key="staff.id"
          class="bg-white p-4 rounded-lg shadow"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold">{{ staff.name }}</h3>
                <span :class="getRoleBadgeClass(staff.role)">
                  {{ getRoleLabel(staff.role) }}
                </span>
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ getShopName(staff.shopId) }}
                </span>
                <span v-if="!staff.isActive" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm">
                  無効
                </span>
              </div>
              <p class="text-sm text-gray-600 mb-1">ユーザー名: {{ staff.username }}</p>
              <p v-if="staff.email" class="text-sm text-gray-600 mb-1">メール: {{ staff.email }}</p>
              <p v-if="staff.lastLoginAt" class="text-xs text-gray-500">
                最終ログイン: {{ formatDate(staff.lastLoginAt) }}
              </p>
            </div>
            <div class="flex gap-2">
              <NuxtLink
                :to="`/admin/users/${staff.id}/edit`"
                class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors touch-target text-sm"
              >
                編集
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'
import type { Shop, User } from '~/types'

const authStore = useAuthStore()
const shopStore = useShopStore()

const myShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const allStaff = ref<User[]>([])
const filteredStaff = ref<User[]>([])
const isLoading = ref(false)

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const filterStaff = () => {
  if (selectedShopId.value) {
    filteredStaff.value = allStaff.value.filter(s => s.shopId === selectedShopId.value)
  } else {
    filteredStaff.value = allStaff.value
  }
}

const getShopName = (shopId: string) => {
  const shop = myShops.value.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const getRoleLabel = (role: string) => {
  const labels: Record<string, string> = {
    owner: 'オーナー',
    manager: '管理者',
    staff: 'スタッフ'
  }
  return labels[role] || role
}

const getRoleBadgeClass = (role: string) => {
  const classes: Record<string, string> = {
    owner: 'px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm',
    manager: 'px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm',
    staff: 'px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm'
  }
  return classes[role] || ''
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleString('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchAllStaff = async () => {
  isLoading.value = true
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  
  try {
    // 各店舗のスタッフを取得
    const staffPromises = myShops.value.map(async (shop) => {
      try {
        // 各店舗のユーザーを取得するAPIが必要
        // 現在は店舗ごとのユーザー管理APIがないため、全ユーザーからフィルタリング
        const users = await $fetch<User[]>(`${apiBase}/users`)
        return users.filter(u => u.shopId === shop.id)
      } catch (error) {
        console.error(`店舗 ${shop.name} のスタッフ取得に失敗:`, error)
        return []
      }
    })
    
    const staffArrays = await Promise.all(staffPromises)
    allStaff.value = staffArrays.flat()
    filterStaff()
  } catch (error) {
    console.error('スタッフの取得に失敗しました:', error)
    allStaff.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/staff/login')
    return
  }

  // 所属店舗一覧を取得
  try {
    myShops.value = await shopStore.fetchMyShops()
    
    if (myShops.value.length === 0) {
      await navigateTo('/admin/dashboard')
      return
    }

    // 全店舗のスタッフを取得
    await fetchAllStaff()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

