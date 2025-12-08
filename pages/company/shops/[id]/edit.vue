<template>
  <NuxtLayout name="default" title="店舗編集">
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
        <h2 class="text-2xl font-bold">店舗編集</h2>
        <NuxtLink
          to="/company/shops"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
        >
          一覧に戻る
        </NuxtLink>
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

      <!-- 編集フォーム -->
      <div v-else-if="shop" class="bg-white p-6 rounded-lg shadow">
        <form @submit.prevent="handleUpdateShop" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗コード <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editData.code"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="shop001"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              店舗名 <span class="text-red-500">*</span>
            </label>
            <input
              v-model="editData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="店舗名を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              説明
            </label>
            <textarea
              v-model="editData.description"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              rows="3"
              placeholder="店舗の説明を入力"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              住所
            </label>
            <input
              v-model="editData.address"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="住所を入力"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              電話番号
            </label>
            <input
              v-model="editData.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="03-1234-5678"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              v-model="editData.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="shop@example.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              最大テーブル数
            </label>
            <input
              v-model.number="editData.maxTables"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              placeholder="20"
            />
          </div>

          <div>
            <label class="flex items-center gap-2">
              <input
                v-model="editData.isActive"
                type="checkbox"
                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
              />
              <span class="text-sm font-medium text-gray-700">アクティブ</span>
            </label>
          </div>

          <!-- 営業時間・定休日設定セクション -->
          <div class="border-t pt-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">営業時間・定休日設定</h3>
            
            <!-- 曜日ごとの営業時間 -->
            <div class="mb-6">
              <h4 class="text-md font-medium mb-3">曜日ごとの営業時間</h4>
              <div class="space-y-3">
                <div v-for="day in weekDays" :key="day.key" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                  <div class="w-20">
                    <label class="text-sm font-medium text-gray-700">{{ day.label }}</label>
                  </div>
                  <div class="flex items-center gap-2">
                    <input
                      v-model="editData.settings.businessHours[day.key].isClosed"
                      type="checkbox"
                      class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                      @change="onDayClosedChange(day.key)"
                    />
                    <span class="text-sm text-gray-600">休業</span>
                  </div>
                  <div v-if="!editData.settings.businessHours[day.key].isClosed" class="flex items-center gap-2 flex-1">
                    <input
                      v-model="editData.settings.businessHours[day.key].open"
                      type="time"
                      class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    />
                    <span class="text-gray-600">〜</span>
                    <input
                      v-model="editData.settings.businessHours[day.key].close"
                      type="time"
                      class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- 定休日設定 -->
            <div class="mb-6">
              <h4 class="text-md font-medium mb-3">定休日（規則的な休業日）</h4>
              <div class="flex flex-wrap gap-2">
                <label
                  v-for="day in weekDays"
                  :key="day.key"
                  class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer transition-colors"
                  :class="editData.settings.regularHolidays.includes(day.key) 
                    ? 'bg-red-100 border-red-300 text-red-700' 
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                >
                  <input
                    v-model="editData.settings.regularHolidays"
                    type="checkbox"
                    :value="day.key"
                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                  />
                  <span class="text-sm font-medium">{{ day.label }}</span>
                </label>
              </div>
            </div>

            <!-- 臨時休業日設定 -->
            <div class="mb-6">
              <h4 class="text-md font-medium mb-3">臨時休業日</h4>
              <div class="space-y-2">
                <div
                  v-for="(holiday, index) in editData.settings.temporaryHolidays"
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <input
                    v-model="editData.settings.temporaryHolidays[index]"
                    type="date"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  />
                  <button
                    type="button"
                    @click="removeTemporaryHoliday(index)"
                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm"
                  >
                    削除
                  </button>
                </div>
                <button
                  type="button"
                  @click="addTemporaryHoliday"
                  class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm"
                >
                  + 臨時休業日を追加
                </button>
              </div>
            </div>
          </div>

          <div v-if="updateError" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            {{ updateError }}
          </div>

          <div v-if="success" class="p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            店舗情報を更新しました
          </div>

          <div class="flex gap-3 justify-end">
            <NuxtLink
              to="/company/shops"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              キャンセル
            </NuxtLink>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              {{ isSubmitting ? '更新中...' : '更新' }}
            </button>
          </div>
        </form>
      </div>

      <!-- オーナー管理セクション -->
      <div v-if="shop" class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">オーナー管理</h3>
        
        <!-- 現在のオーナー一覧 -->
        <div class="mb-6">
          <h4 class="text-sm font-medium text-gray-700 mb-3">現在のオーナー</h4>
          <div v-if="shop.owners && shop.owners.length > 0" class="space-y-2">
            <div
              v-for="owner in shop.owners"
              :key="owner.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="font-medium text-gray-900">{{ owner.name }}</p>
                <p v-if="owner.email" class="text-sm text-gray-600">{{ owner.email }}</p>
                <p class="text-xs text-gray-500">ユーザー名: {{ owner.username }}</p>
              </div>
              <button
                @click="handleRemoveOwner(owner.id)"
                :disabled="isRemovingOwner"
                class="px-3 py-1 bg-red-200 text-red-700 rounded hover:bg-red-300 disabled:bg-gray-300 disabled:text-gray-500 transition-colors text-sm"
              >
                解除
              </button>
            </div>
          </div>
          <div v-else class="p-3 bg-gray-50 rounded-lg text-gray-500 text-sm">
            オーナーが設定されていません
          </div>
        </div>

        <!-- オーナー追加 -->
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-3">オーナーを追加</h4>
          <div class="flex gap-3">
            <select
              v-model="selectedUserId"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">ユーザーを選択してください</option>
              <option
                v-for="user in availableUsers"
                :key="user.id"
                :value="user.id"
              >
                {{ user.name }} ({{ user.username }}){{ user.email ? ` - ${user.email}` : '' }}
              </option>
            </select>
            <button
              @click="handleAddOwner"
              :disabled="!selectedUserId || isAddingOwner"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              {{ isAddingOwner ? '追加中...' : '追加' }}
            </button>
          </div>
          <div v-if="ownerError" class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
            {{ ownerError }}
          </div>
          <div v-if="ownerSuccess" class="mt-2 p-2 bg-green-100 border border-green-400 text-green-700 rounded text-sm">
            {{ ownerSuccess }}
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import { useUserStore } from '~/stores/user'
import type { Shop, User } from '~/types'

definePageMeta({
  layout: 'default'
})

const route = useRoute()
const shopStore = useShopStore()
const authStore = useAuthStore()
const userStore = useUserStore()

const shopId = route.params.id as string

const shop = ref<Shop | null>(null)
const isLoading = ref(true)
const isSubmitting = ref(false)
const error = ref('')
const errorDetails = ref('')
const updateError = ref('')
const success = ref(false)
const debugInfo = ref('')

// オーナー管理関連
const selectedUserId = ref('')
const isAddingOwner = ref(false)
const isRemovingOwner = ref(false)
const ownerError = ref('')
const ownerSuccess = ref('')
const allUsers = ref<User[]>([])

// 即座にデバッグ情報を設定
debugInfo.value = `ページ読み込み開始\nshopId: ${shopId}\nroute.path: ${route.path}\nroute.fullPath: ${route.fullPath}`

const weekDays = [
  { key: 'monday', label: '月曜日' },
  { key: 'tuesday', label: '火曜日' },
  { key: 'wednesday', label: '水曜日' },
  { key: 'thursday', label: '木曜日' },
  { key: 'friday', label: '金曜日' },
  { key: 'saturday', label: '土曜日' },
  { key: 'sunday', label: '日曜日' }
]

const editData = ref({
  code: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  email: '',
  maxTables: 20,
  isActive: true,
  settings: {
    regularHolidays: [] as string[],
    temporaryHolidays: [] as string[],
    businessHours: {
      monday: { open: '10:00', close: '22:00', isClosed: false },
      tuesday: { open: '10:00', close: '22:00', isClosed: false },
      wednesday: { open: '10:00', close: '22:00', isClosed: false },
      thursday: { open: '10:00', close: '22:00', isClosed: false },
      friday: { open: '10:00', close: '22:00', isClosed: false },
      saturday: { open: '10:00', close: '22:00', isClosed: false },
      sunday: { open: '10:00', close: '22:00', isClosed: false }
    }
  }
})

const onDayClosedChange = (dayKey: string) => {
  const day = editData.value.settings.businessHours[dayKey as keyof typeof editData.value.settings.businessHours]
  if (day.isClosed) {
    // 休業に設定した場合、定休日に追加
    if (!editData.value.settings.regularHolidays.includes(dayKey)) {
      editData.value.settings.regularHolidays.push(dayKey)
    }
  } else {
    // 営業に戻した場合、定休日から削除
    editData.value.settings.regularHolidays = editData.value.settings.regularHolidays.filter(d => d !== dayKey)
  }
}

const addTemporaryHoliday = () => {
  // 今日の日付をデフォルト値として設定
  const today = new Date().toISOString().split('T')[0]
  editData.value.settings.temporaryHolidays.push(today)
}

const removeTemporaryHoliday = (index: number) => {
  editData.value.settings.temporaryHolidays.splice(index, 1)
}

// オーナーとして追加可能なユーザー一覧（既にオーナーになっているユーザーを除外）
const availableUsers = computed(() => {
  if (!shop.value) return []
  
  const ownerIds = (shop.value.owners || []).map(o => o.id)
  return allUsers.value.filter(user => !ownerIds.includes(user.id))
})

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const handleUpdateShop = async () => {
  isSubmitting.value = true
  updateError.value = ''
  success.value = false
  
  try {
    await shopStore.updateShop(shopId, editData.value)
    success.value = true
    
    // 2秒後にリダイレクト
    setTimeout(() => {
      navigateTo('/company/shops')
    }, 2000)
  } catch (err: any) {
    updateError.value = err?.data?.error || err?.message || '店舗情報の更新に失敗しました'
  } finally {
    isSubmitting.value = false
  }
}

const handleAddOwner = async () => {
  if (!selectedUserId.value) return
  
  isAddingOwner.value = true
  ownerError.value = ''
  ownerSuccess.value = ''
  
  try {
    await shopStore.addShopOwner(shopId, selectedUserId.value)
    ownerSuccess.value = 'オーナーを追加しました'
    selectedUserId.value = ''
    
    // 店舗情報を再取得
    const fetchedShop = await shopStore.fetchShopById(shopId)
    if (fetchedShop) {
      shop.value = fetchedShop
    }
    
    // 3秒後に成功メッセージを消す
    setTimeout(() => {
      ownerSuccess.value = ''
    }, 3000)
  } catch (err: any) {
    ownerError.value = err?.data?.error || err?.message || 'オーナーの追加に失敗しました'
  } finally {
    isAddingOwner.value = false
  }
}

const handleRemoveOwner = async (userId: string) => {
  if (!confirm('このオーナーを解除しますか？')) {
    return
  }
  
  isRemovingOwner.value = true
  ownerError.value = ''
  ownerSuccess.value = ''
  
  try {
    await shopStore.removeShopOwner(shopId, userId)
    ownerSuccess.value = 'オーナーを解除しました'
    
    // 店舗情報を再取得
    const fetchedShop = await shopStore.fetchShopById(shopId)
    if (fetchedShop) {
      shop.value = fetchedShop
    }
    
    // 3秒後に成功メッセージを消す
    setTimeout(() => {
      ownerSuccess.value = ''
    }, 3000)
  } catch (err: any) {
    ownerError.value = err?.data?.error || err?.message || 'オーナーの解除に失敗しました'
  } finally {
    isRemovingOwner.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  authStore.loadUserFromStorage()
  if (!authStore.isAuthenticated) {
    await navigateTo('/company/login')
    return
  }

  // 店舗情報とユーザー一覧を取得
  try {
    const [fetchedShop, fetchedUsers] = await Promise.all([
      shopStore.fetchShopById(shopId),
      userStore.fetchAllUsers().catch(() => []) // エラー時は空配列を返す
    ])
    
    if (!fetchedShop) {
      error.value = '店舗が見つかりませんでした'
      isLoading.value = false
      return
    }
    
    shop.value = fetchedShop
    allUsers.value = fetchedUsers || []
    
    // デフォルトの営業時間設定
    const defaultBusinessHours = {
      monday: { open: '10:00', close: '22:00', isClosed: false },
      tuesday: { open: '10:00', close: '22:00', isClosed: false },
      wednesday: { open: '10:00', close: '22:00', isClosed: false },
      thursday: { open: '10:00', close: '22:00', isClosed: false },
      friday: { open: '10:00', close: '22:00', isClosed: false },
      saturday: { open: '10:00', close: '22:00', isClosed: false },
      sunday: { open: '10:00', close: '22:00', isClosed: false }
    }
    
    // 既存の設定があればマージ
    const existingSettings = fetchedShop.settings || {}
    const existingBusinessHours = existingSettings.businessHours || {}
    
    // 既存の営業時間とデフォルトをマージ
    const mergedBusinessHours: any = {}
    for (const day of weekDays) {
      if (existingBusinessHours[day.key]) {
        mergedBusinessHours[day.key] = {
          open: existingBusinessHours[day.key].open || '10:00',
          close: existingBusinessHours[day.key].close || '22:00',
          isClosed: existingBusinessHours[day.key].isClosed || false
        }
      } else {
        mergedBusinessHours[day.key] = defaultBusinessHours[day.key as keyof typeof defaultBusinessHours]
      }
    }
    
    editData.value = {
      code: fetchedShop.code,
      name: fetchedShop.name,
      description: fetchedShop.description || '',
      address: fetchedShop.address || '',
      phone: fetchedShop.phone || '',
      email: fetchedShop.email || '',
      maxTables: fetchedShop.maxTables || 20,
      isActive: fetchedShop.isActive,
      settings: {
        regularHolidays: existingSettings.regularHolidays || [],
        temporaryHolidays: existingSettings.temporaryHolidays || [],
        businessHours: mergedBusinessHours
      }
    }
  } catch (err: any) {
    error.value = err?.data?.error || err?.message || '店舗情報の取得に失敗しました'
  } finally {
    isLoading.value = false
  }
})
</script>

