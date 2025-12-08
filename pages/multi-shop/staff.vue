<template>
  <NuxtLayout name="default" title="スタッフ管理">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <AdminNavigation
        :navigation-items="navigationItems"
        active-color="green"
      />

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
        <StaffCard
          v-for="staff in filteredStaff"
          :key="staff.id"
          :staff="staff"
          :shops="myShops"
          :show-shop-name="true"
          :show-current-user-badge="true"
          :show-password-button="false"
          :show-delete-button="false"
          :edit-path="`/shop/users/${staff.id}/edit`"
        />
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
const { handleLogout, checkAuthMultiShop } = useAuthCheck()

const myShops = ref<Shop[]>([])
const selectedShopId = ref<string>('')
const allStaff = ref<User[]>([])
const filteredStaff = ref<User[]>([])
const isLoading = ref(false)

const { navigationItems } = useMultiShopNavigation()

const filterStaff = () => {
  if (selectedShopId.value) {
    filteredStaff.value = allStaff.value.filter(s => s.shopId === selectedShopId.value)
  } else {
    filteredStaff.value = allStaff.value
  }
}


const fetchAllStaff = async () => {
  isLoading.value = true
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  
  try {
    // 認証トークンを取得
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }
    
    // 全店舗のユーザーを取得（company-users APIを使用）
    const allUsers = await $fetch<User[]>(`${apiBase}/company-users`, {
      headers: headers
    })
    
    // 所属店舗のIDリストを作成
    const shopIds = myShops.value.map(s => s.id)
    
    // 所属店舗のスタッフのみをフィルタリング
    allStaff.value = allUsers.filter(u => shopIds.includes(u.shopId))
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
  const isAuthenticated = await checkAuthMultiShop()
  if (!isAuthenticated) {
    return
  }

  // 所属店舗一覧を取得
  try {
    myShops.value = await shopStore.fetchMyShops()
    
    if (myShops.value.length === 0) {
      await navigateTo('/shop/dashboard')
      return
    }

    // 全店舗のスタッフを取得
    await fetchAllStaff()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  }
})
</script>

