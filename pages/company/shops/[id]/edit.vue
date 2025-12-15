<template>
    <div class="space-y-6">
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
              
              <!-- 毎週の定休日 -->
              <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">毎週の定休日</h5>
                <div class="flex flex-wrap gap-2">
                  <label
                    v-for="day in weekDays"
                    :key="day.key"
                    class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer transition-colors"
                    :class="isWeeklyHoliday(day.key) 
                      ? 'bg-red-100 border-red-300 text-red-700' 
                      : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                  >
                    <input
                      type="checkbox"
                      :checked="isWeeklyHoliday(day.key)"
                      @change="toggleWeeklyHoliday(day.key)"
                      class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                    />
                    <span class="text-sm font-medium">{{ day.label }}</span>
                  </label>
                </div>
              </div>

              <!-- 毎月第〇曜日の定休日 -->
              <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">毎月第〇曜日の定休日</h5>
                <div class="space-y-2">
                  <div
                    v-for="(holiday, index) in monthlyHolidays"
                    :key="index"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                  >
                    <select
                      v-model="holiday.week"
                      class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                    >
                      <option :value="1">第1</option>
                      <option :value="2">第2</option>
                      <option :value="3">第3</option>
                      <option :value="4">第4</option>
                      <option :value="-1">最終</option>
                    </select>
                    <select
                      v-model="holiday.day"
                      class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                    >
                      <option v-for="day in weekDays" :key="day.key" :value="day.key">
                        {{ day.label }}
                      </option>
                    </select>
                    <button
                      type="button"
                      @click="removeMonthlyHoliday(index)"
                      class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors text-sm"
                    >
                      削除
                    </button>
                  </div>
                  <button
                    type="button"
                    @click="addMonthlyHoliday"
                    class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm"
                  >
                    + 毎月第〇曜日を追加
                  </button>
                </div>
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
          <div class="relative">
            <div class="flex gap-3">
              <div class="flex-1 relative">
                <input
                  v-model="ownerSearchQuery"
                  type="text"
                  placeholder="オーナーを検索（名前、ユーザー名、メールで検索）"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  @focus="showOwnerSuggestions = true"
                  @blur="handleOwnerInputBlur"
                  @input="handleOwnerSearch"
                />
                <!-- 候補リスト -->
                <div
                  v-if="showOwnerSuggestions && filteredOwnerUsers.length > 0"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                >
                  <div
                    v-for="user in filteredOwnerUsers"
                    :key="user.id"
                    @mousedown="selectOwnerUser(user)"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-600">{{ user.username }}</div>
                    <div v-if="user.email" class="text-xs text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
                <div
                  v-if="showOwnerSuggestions && filteredOwnerUsers.length === 0 && ownerSearchQuery"
                  class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-3 text-sm text-gray-500"
                >
                  該当するオーナーが見つかりません
                </div>
              </div>
              <button
                @click="handleAddOwner"
                :disabled="!selectedOwnerUser || isAddingOwner"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
              >
                {{ isAddingOwner ? '追加中...' : '追加' }}
              </button>
            </div>
            <!-- 選択中のユーザー表示 -->
            <div v-if="selectedOwnerUser" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm">
              <span class="font-medium text-blue-900">選択中: {{ selectedOwnerUser.name }} ({{ selectedOwnerUser.username }})</span>
              <button
                @click="clearSelectedOwner"
                class="ml-2 text-blue-600 hover:text-blue-800 underline"
              >
                クリア
              </button>
            </div>
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
</template>

<script setup lang="ts">
import { useShopStore } from '~/stores/shop'
import { useAuthStore } from '~/stores/auth'
import { useUserStore } from '~/stores/user'
import type { Shop, User } from '~/types'

definePageMeta({
  layout: 'company'
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
const ownerSearchQuery = ref('')
const selectedOwnerUser = ref<User | null>(null)
const showOwnerSuggestions = ref(false)
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
    regularHolidays: [] as (string | { type: string; day: string; week?: number })[],
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

// 毎週の定休日を取得
const weeklyHolidays = computed(() => {
  return editData.value.settings.regularHolidays.filter(h => {
    if (typeof h === 'string') return true
    return h.type === 'weekly'
  }).map(h => typeof h === 'string' ? h : h.day)
})

// 毎月第〇曜日の定休日を取得
const monthlyHolidays = computed({
  get() {
    return editData.value.settings.regularHolidays
      .filter(h => typeof h === 'object' && h.type === 'monthly')
      .map(h => {
        const obj = h as { type: string; day: string; week?: number }
        return {
          week: obj.week || 1,
          day: obj.day
        }
      })
  },
  set(newValue: { week: number; day: string }[]) {
    // 毎週の定休日を保持
    const weekly = editData.value.settings.regularHolidays.filter(h => {
      if (typeof h === 'string') return true
      return h.type === 'weekly'
    })
    
    // 毎月の定休日を更新
    const monthly = newValue.map(h => ({
      type: 'monthly' as const,
      day: h.day,
      week: h.week
    }))
    
    editData.value.settings.regularHolidays = [...weekly, ...monthly]
  }
})

const isWeeklyHoliday = (dayKey: string): boolean => {
  return weeklyHolidays.value.includes(dayKey)
}

const toggleWeeklyHoliday = (dayKey: string) => {
  const currentHolidays = editData.value.settings.regularHolidays
  const isHoliday = weeklyHolidays.value.includes(dayKey)
  
  if (isHoliday) {
    // 削除
    editData.value.settings.regularHolidays = currentHolidays.filter(h => {
      if (typeof h === 'string') return h !== dayKey
      return h.type !== 'weekly' || h.day !== dayKey
    })
  } else {
    // 追加（既存のmonthlyを保持）
    const monthly = currentHolidays.filter(h => typeof h === 'object' && h.type === 'monthly')
    editData.value.settings.regularHolidays = [
      ...currentHolidays.filter(h => typeof h === 'string' && h !== dayKey),
      { type: 'weekly', day: dayKey },
      ...monthly
    ]
  }
}

const addMonthlyHoliday = () => {
  const newHoliday = { week: 1, day: 'monday' }
  monthlyHolidays.value = [...monthlyHolidays.value, newHoliday]
}

const removeMonthlyHoliday = (index: number) => {
  monthlyHolidays.value = monthlyHolidays.value.filter((_, i) => i !== index)
}

const onDayClosedChange = (dayKey: string) => {
  const day = editData.value.settings.businessHours[dayKey as keyof typeof editData.value.settings.businessHours]
  if (day.isClosed) {
    // 休業に設定した場合、毎週の定休日に追加
    if (!isWeeklyHoliday(dayKey)) {
      toggleWeeklyHoliday(dayKey)
    }
  } else {
    // 営業に戻した場合、毎週の定休日から削除
    if (isWeeklyHoliday(dayKey)) {
      toggleWeeklyHoliday(dayKey)
    }
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

// オーナー権限のユーザー一覧（既にこの店舗のオーナーになっているユーザーを除外）
const ownerUsers = computed(() => {
  if (!shop.value) return []
  
  const ownerIds = (shop.value.owners || []).map(o => o.id)
  // オーナー権限のユーザーのみをフィルタリング
  return allUsers.value.filter(user => 
    user.role === 'owner' && !ownerIds.includes(user.id)
  )
})

// 検索クエリに基づいてオーナー候補をフィルタリング
const filteredOwnerUsers = computed(() => {
  if (!ownerSearchQuery.value.trim()) {
    return ownerUsers.value.slice(0, 10) // 検索クエリがない場合は最大10件表示
  }
  
  const query = ownerSearchQuery.value.toLowerCase().trim()
  return ownerUsers.value.filter(user => {
    const nameMatch = user.name.toLowerCase().includes(query)
    const usernameMatch = user.username.toLowerCase().includes(query)
    const emailMatch = user.email?.toLowerCase().includes(query) || false
    return nameMatch || usernameMatch || emailMatch
  }).slice(0, 10) // 最大10件まで表示
})

const handleOwnerSearch = () => {
  showOwnerSuggestions.value = true
}

const selectOwnerUser = (user: User) => {
  selectedOwnerUser.value = user
  ownerSearchQuery.value = `${user.name} (${user.username})`
  showOwnerSuggestions.value = false
}

const clearSelectedOwner = () => {
  selectedOwnerUser.value = null
  ownerSearchQuery.value = ''
  showOwnerSuggestions.value = false
}

const handleOwnerInputBlur = () => {
  // 少し遅延させて、クリックイベントが発火するようにする
  setTimeout(() => {
    showOwnerSuggestions.value = false
  }, 200)
}

const { handleLogout } = useAuthCheck()

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
  if (!selectedOwnerUser.value) return
  
  isAddingOwner.value = true
  ownerError.value = ''
  ownerSuccess.value = ''
  
  try {
    await shopStore.addShopOwner(shopId, selectedOwnerUser.value.id)
    ownerSuccess.value = 'オーナーを追加しました'
    clearSelectedOwner()
    
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
    
    // 既存の定休日を正規化（後方互換性のため）
    const normalizedRegularHolidays: (string | { type: string; day: string; week?: number })[] = []
    if (existingSettings.regularHolidays) {
      for (const holiday of existingSettings.regularHolidays) {
        if (typeof holiday === 'string') {
          // 既存の文字列形式（毎週）
          normalizedRegularHolidays.push(holiday)
        } else if (typeof holiday === 'object' && holiday !== null) {
          // オブジェクト形式
          normalizedRegularHolidays.push(holiday)
        }
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
        regularHolidays: normalizedRegularHolidays,
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

