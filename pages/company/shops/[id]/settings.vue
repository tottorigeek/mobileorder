<template>
  <NuxtLayout name="default" title="店舗詳細設定">
    <div class="space-y-6">
      <!-- ナビゲーション -->
      <div class="flex gap-3 overflow-x-auto pb-2">
        <NuxtLink
          to="/company/dashboard"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ダッシュボード
        </NuxtLink>
        <NuxtLink
          to="/company/shops"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          店舗管理
        </NuxtLink>
        <NuxtLink
          to="/company/users"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          ユーザー管理
        </NuxtLink>
        <NuxtLink
          to="/company/error-logs"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          エラーログ
        </NuxtLink>
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
        >
          ログアウト
        </button>
      </div>

      <!-- ヘッダー -->
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold">店舗詳細設定</h2>
          <p v-if="shop" class="text-gray-600 mt-1">{{ shop.name }}</p>
        </div>
        <div class="flex gap-2">
          <NuxtLink
            :to="`/company/shops/${shopId}/edit`"
            class="px-4 py-2 bg-blue-200 text-blue-700 rounded-lg hover:bg-blue-300 transition-colors"
          >
            基本情報を編集
          </NuxtLink>
          <NuxtLink
            to="/company/shops"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
          >
            一覧に戻る
          </NuxtLink>
        </div>
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- エラー -->
      <div v-else-if="error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <p class="font-semibold mb-2">エラーが発生しました</p>
        <p>{{ error }}</p>
        <NuxtLink
          to="/company/shops"
          class="mt-4 inline-block px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
        >
          一覧に戻る
        </NuxtLink>
      </div>

      <!-- 詳細設定フォーム -->
      <div v-else-if="shop" class="space-y-6">
        <!-- 基本情報セクション -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">基本情報</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">店舗コード</label>
              <p class="text-gray-900">{{ shop.code }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">店舗名</label>
              <p class="text-gray-900">{{ shop.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
              <span :class="shop.isActive ? 'px-2 py-1 bg-green-100 text-green-800 rounded text-sm' : 'px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm'">
                {{ shop.isActive ? 'アクティブ' : '無効' }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">最大テーブル数</label>
              <p class="text-gray-900">{{ shop.maxTables || 20 }}</p>
            </div>
          </div>
        </div>

        <!-- オーナー情報セクション -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">オーナー情報</h3>
          <div v-if="shop.owners && shop.owners.length > 0" class="space-y-2">
            <div v-for="owner in shop.owners" :key="owner.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">{{ owner.name }}</p>
                <p v-if="owner.email" class="text-sm text-gray-600">{{ owner.email }}</p>
                <p class="text-xs text-gray-500">ユーザー名: {{ owner.username }}</p>
              </div>
            </div>
          </div>
          <div v-else-if="shop.owner" class="p-3 bg-gray-50 rounded-lg">
            <p class="font-medium text-gray-900">{{ shop.owner.name }}</p>
            <p v-if="shop.owner.email" class="text-sm text-gray-600">{{ shop.owner.email }}</p>
            <p class="text-xs text-gray-500">ユーザー名: {{ shop.owner.username }}</p>
          </div>
          <p v-else class="text-gray-500">オーナー未設定</p>
        </div>

        <!-- 連絡先情報セクション -->
        <div class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">連絡先情報</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">住所</label>
              <p class="text-gray-900">{{ shop.address || '未設定' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
              <p class="text-gray-900">{{ shop.phone || '未設定' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
              <p class="text-gray-900">{{ shop.email || '未設定' }}</p>
            </div>
          </div>
        </div>

        <!-- 説明セクション -->
        <div v-if="shop.description" class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">説明</h3>
          <p class="text-gray-700 whitespace-pre-wrap">{{ shop.description }}</p>
        </div>

        <!-- 営業時間・定休日セクション -->
        <div v-if="shop.settings" class="bg-white p-6 rounded-lg shadow">
          <h3 class="text-lg font-semibold mb-4">営業時間・定休日</h3>
          
          <!-- 曜日ごとの営業時間 -->
          <div class="mb-6">
            <h4 class="text-md font-medium mb-3">営業時間</h4>
            <div class="space-y-2">
              <div
                v-for="day in weekDays"
                :key="day.key"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <span class="text-sm font-medium text-gray-700 w-20">{{ day.label }}</span>
                <div v-if="shop.settings.businessHours?.[day.key]">
                  <span
                    v-if="shop.settings.businessHours[day.key].isClosed"
                    class="text-sm text-red-600 font-medium"
                  >
                    休業
                  </span>
                  <span
                    v-else
                    class="text-sm text-gray-900"
                  >
                    {{ shop.settings.businessHours[day.key].open }} 〜 {{ shop.settings.businessHours[day.key].close }}
                  </span>
                </div>
                <span v-else class="text-sm text-gray-500">未設定</span>
              </div>
            </div>
          </div>

          <!-- 定休日 -->
          <div v-if="shop.settings.regularHolidays && shop.settings.regularHolidays.length > 0" class="mb-6">
            <h4 class="text-md font-medium mb-3">定休日</h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="dayKey in shop.settings.regularHolidays"
                :key="dayKey"
                class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm font-medium"
              >
                {{ getDayLabel(dayKey) }}
              </span>
            </div>
          </div>

          <!-- 臨時休業日 -->
          <div v-if="shop.settings.temporaryHolidays && shop.settings.temporaryHolidays.length > 0" class="mb-6">
            <h4 class="text-md font-medium mb-3">臨時休業日</h4>
            <div class="space-y-2">
              <div
                v-for="(holiday, index) in shop.settings.temporaryHolidays"
                :key="index"
                class="p-2 bg-yellow-50 border border-yellow-200 rounded-lg"
              >
                <span class="text-sm text-gray-900">{{ formatDate(holiday) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import type { Shop } from '~/types'

definePageMeta({
  layout: 'default'
})

const route = useRoute()
const shopStore = useShopStore()
const authStore = useAuthStore()

const shopId = route.params.id as string
const shop = ref<Shop | null>(null)
const isLoading = ref(true)
const error = ref('')

const weekDays = [
  { key: 'monday', label: '月曜日' },
  { key: 'tuesday', label: '火曜日' },
  { key: 'wednesday', label: '水曜日' },
  { key: 'thursday', label: '木曜日' },
  { key: 'friday', label: '金曜日' },
  { key: 'saturday', label: '土曜日' },
  { key: 'sunday', label: '日曜日' }
]

const getDayLabel = (dayKey: string) => {
  const day = weekDays.find(d => d.key === dayKey)
  return day ? day.label : dayKey
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const weekdays = ['日', '月', '火', '水', '木', '金', '土']
  const weekday = weekdays[date.getDay()]
  return `${year}年${month}月${day}日（${weekday}）`
}

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 店舗情報を取得
  try {
    const fetchedShop = await shopStore.fetchShopById(shopId)
    
    if (!fetchedShop) {
      error.value = '店舗が見つかりませんでした'
      isLoading.value = false
      return
    }
    
    shop.value = fetchedShop
  } catch (err: any) {
    error.value = err?.data?.error || err?.message || '店舗情報の取得に失敗しました'
  } finally {
    isLoading.value = false
  }
})
</script>

