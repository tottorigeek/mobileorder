<template>
  <NuxtLayout name="default" title="エラーログ">
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
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
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
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium whitespace-nowrap"
        >
          エラーログ
        </NuxtLink>
        <NuxtLink
          to="/admin/users/password"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium whitespace-nowrap hover:bg-gray-100"
        >
          パスワード変更
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
        <h2 class="text-2xl font-bold">エラーログ</h2>
        <div class="flex gap-2">
          <button
            @click="showFilters = !showFilters"
            class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium hover:bg-gray-100"
          >
            フィルター
          </button>
          <button
            v-if="authStore.isOwner"
            @click="confirmDeleteAll"
            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium hover:bg-red-200"
          >
            一括削除
          </button>
          <button
            @click="refreshLogs"
            class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700"
          >
            更新
          </button>
        </div>
      </div>

      <!-- フィルター -->
      <div v-if="showFilters" class="bg-white p-6 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">エラーレベル</label>
            <select
              v-model="filters.level"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
              <option value="">すべて</option>
              <option value="error">エラー</option>
              <option value="warning">警告</option>
              <option value="info">情報</option>
              <option value="debug">デバッグ</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
            <input
              v-model="filters.startDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
            <input
              v-model="filters.endDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            />
          </div>
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700"
            >
              適用
            </button>
          </div>
        </div>
      </div>

      <!-- 説明 -->
      <div class="bg-white p-6 rounded-lg shadow">
        <p class="text-gray-600">
          システムで発生したエラーログを確認できます。エラーの詳細情報や発生場所を確認して、問題の解決に役立ててください。
        </p>
      </div>

      <!-- エラーメッセージ -->
      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        {{ errorMessage }}
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- エラーログ一覧 -->
      <div v-else-if="logs.length === 0 && !errorMessage" class="text-center py-12 text-gray-500">
        エラーログがありません
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="log in logs"
          :key="log.id"
          class="bg-white p-4 rounded-lg shadow cursor-pointer hover:shadow-md transition-shadow"
          @click="showLogDetail(log)"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="getLevelBadgeClass(log.level)">
                  {{ getLevelLabel(log.level) }}
                </span>
                <span class="text-sm text-gray-500">{{ formatDate(log.createdAt) }}</span>
                <span v-if="log.shop" class="text-sm text-gray-600">
                  店舗: {{ log.shop.name }}
                </span>
                <span v-if="log.user" class="text-sm text-gray-600">
                  ユーザー: {{ log.user.name }}
                </span>
              </div>
              <p class="text-sm font-medium text-gray-900 mb-1">{{ log.message }}</p>
              <div v-if="log.file" class="text-xs text-gray-500">
                {{ log.file }}{{ log.line ? `:${log.line}` : '' }}
              </div>
              <div v-if="log.requestUri" class="text-xs text-gray-500 mt-1">
                {{ log.requestMethod }} {{ log.requestUri }}
              </div>
            </div>
            <button
              v-if="authStore.isOwner"
              @click.stop="confirmDelete(log)"
              class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors touch-target text-sm"
            >
              削除
            </button>
          </div>
        </div>
      </div>

      <!-- ページネーション -->
      <div v-if="pagination.totalPages > 1" class="flex justify-center items-center gap-2">
        <button
          @click="changePage(pagination.page - 1)"
          :disabled="pagination.page <= 1"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium hover:bg-gray-100 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
        >
          前へ
        </button>
        <span class="text-gray-700">
          {{ pagination.page }} / {{ pagination.totalPages }} (全{{ pagination.total }}件)
        </span>
        <button
          @click="changePage(pagination.page + 1)"
          :disabled="pagination.page >= pagination.totalPages"
          class="px-4 py-2 bg-white text-gray-700 rounded-lg font-medium hover:bg-gray-100 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
        >
          次へ
        </button>
      </div>
    </div>

    <!-- ログ詳細モーダル -->
    <div
      v-if="selectedLog"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="selectedLog = null"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">エラーログ詳細</h3>
            <button
              @click="selectedLog = null"
              class="text-gray-500 hover:text-gray-700"
            >
              ✕
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">エラーレベル</label>
              <span :class="getLevelBadgeClass(selectedLog.level)">
                {{ getLevelLabel(selectedLog.level) }}
              </span>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">メッセージ</label>
              <div class="bg-gray-50 p-3 rounded-lg text-sm font-mono whitespace-pre-wrap">{{ selectedLog.message }}</div>
            </div>

            <div v-if="selectedLog.file">
              <label class="block text-sm font-medium text-gray-700 mb-1">ファイル</label>
              <div class="bg-gray-50 p-3 rounded-lg text-sm font-mono">
                {{ selectedLog.file }}{{ selectedLog.line ? `:${selectedLog.line}` : '' }}
              </div>
            </div>

            <div v-if="selectedLog.trace && selectedLog.trace.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-1">スタックトレース</label>
              <div class="bg-gray-50 p-3 rounded-lg text-sm font-mono max-h-64 overflow-y-auto">
                <div v-for="(frame, index) in selectedLog.trace" :key="index" class="mb-2">
                  <div class="text-gray-600">#{{ index }} {{ frame.class || '' }}{{ frame.function || '' }}()</div>
                  <div class="text-gray-500 ml-4">{{ frame.file }}:{{ frame.line }}</div>
                </div>
              </div>
            </div>

            <div v-if="selectedLog.shop">
              <label class="block text-sm font-medium text-gray-700 mb-1">店舗</label>
              <div class="text-sm">{{ selectedLog.shop.name }} ({{ selectedLog.shop.code }})</div>
            </div>

            <div v-if="selectedLog.user">
              <label class="block text-sm font-medium text-gray-700 mb-1">ユーザー</label>
              <div class="text-sm">{{ selectedLog.user.name }} ({{ selectedLog.user.username }})</div>
            </div>

            <div v-if="selectedLog.requestMethod">
              <label class="block text-sm font-medium text-gray-700 mb-1">リクエスト</label>
              <div class="text-sm font-mono">{{ selectedLog.requestMethod }} {{ selectedLog.requestUri }}</div>
            </div>

            <div v-if="selectedLog.ipAddress">
              <label class="block text-sm font-medium text-gray-700 mb-1">IPアドレス</label>
              <div class="text-sm">{{ selectedLog.ipAddress }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">発生日時</label>
              <div class="text-sm">{{ formatDate(selectedLog.createdAt) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

interface ErrorLog {
  id: string
  level: 'error' | 'warning' | 'info' | 'debug'
  message: string
  file?: string
  line?: number
  trace?: Array<{
    file: string
    line: number
    function: string
    class?: string
  }>
  userId?: string
  user?: {
    id: string
    name: string
    username: string
  }
  shopId?: string
  shop?: {
    id: string
    name: string
    code: string
  }
  requestMethod?: string
  requestUri?: string
  ipAddress?: string
  createdAt: string
}

interface Pagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

const authStore = useAuthStore()

// 認証トークンを取得するヘルパー関数
function getAuthHeaders(): Record<string, string> {
  const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }
  return headers
}

const logs = ref<ErrorLog[]>([])
const pagination = ref<Pagination>({
  page: 1,
  limit: 50,
  total: 0,
  totalPages: 0
})
const isLoading = ref(false)
const showFilters = ref(false)
const selectedLog = ref<ErrorLog | null>(null)
const errorMessage = ref('')

const filters = ref({
  level: '',
  startDate: '',
  endDate: ''
})

const handleLogout = async () => {
  if (confirm('ログアウトしますか？')) {
    await authStore.logout()
  }
}

const getLevelLabel = (level: string) => {
  const labels: Record<string, string> = {
    error: 'エラー',
    warning: '警告',
    info: '情報',
    debug: 'デバッグ'
  }
  return labels[level] || level
}

const getLevelBadgeClass = (level: string) => {
  const classes: Record<string, string> = {
    error: 'px-2 py-1 bg-red-100 text-red-800 rounded text-sm font-medium',
    warning: 'px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm font-medium',
    info: 'px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-medium',
    debug: 'px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm font-medium'
  }
  return classes[level] || ''
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleString('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const fetchLogs = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase
    
    const params = new URLSearchParams({
      page: pagination.value.page.toString(),
      limit: pagination.value.limit.toString()
    })
    
    if (filters.value.level) {
      params.append('level', filters.value.level)
    }
    if (filters.value.startDate) {
      params.append('start_date', filters.value.startDate)
    }
    if (filters.value.endDate) {
      params.append('end_date', filters.value.endDate)
    }
    
    const response = await $fetch<{ logs: ErrorLog[]; pagination: Pagination }>(
      `${apiBase}/error-logs?${params.toString()}`,
      {
        headers: getAuthHeaders()
      }
    )
    
    logs.value = response.logs || []
    pagination.value = response.pagination || pagination.value
  } catch (error: any) {
    console.error('エラーログの取得に失敗しました:', error)
    // 権限エラーの場合はエラーメッセージを表示
    if (error?.statusCode === 403 || error?.data?.status === 403 || error?.status === 403) {
      errorMessage.value = 'オーナーまたはマネージャー権限が必要です。このページにアクセスするには、オーナーまたはマネージャーロールが必要です。'
    } else {
      errorMessage.value = error?.data?.error || 'エラーログの取得に失敗しました'
    }
  } finally {
    isLoading.value = false
  }
}

const refreshLogs = () => {
  fetchLogs()
}

const applyFilters = () => {
  pagination.value.page = 1
  fetchLogs()
}

const changePage = (page: number) => {
  pagination.value.page = page
  fetchLogs()
}

const showLogDetail = (log: ErrorLog) => {
  selectedLog.value = log
}

const confirmDelete = async (log: ErrorLog) => {
  if (confirm('このエラーログを削除しますか？')) {
    try {
      const config = useRuntimeConfig()
      const apiBase = config.public.apiBase
      
      await $fetch(`${apiBase}/error-logs/${log.id}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      })
      
      await fetchLogs()
    } catch (error: any) {
      alert(error?.data?.error || 'エラーログの削除に失敗しました')
    }
  }
}

const confirmDeleteAll = async () => {
  const conditions: string[] = []
  if (filters.value.level) conditions.push(`レベル: ${getLevelLabel(filters.value.level)}`)
  if (filters.value.startDate) conditions.push(`開始日: ${filters.value.startDate}`)
  if (filters.value.endDate) conditions.push(`終了日: ${filters.value.endDate}`)
  
  const message = conditions.length > 0
    ? `フィルター条件に一致するエラーログをすべて削除しますか？\n条件: ${conditions.join(', ')}`
    : 'すべてのエラーログを削除しますか？'
  
  if (confirm(message)) {
    try {
      const config = useRuntimeConfig()
      const apiBase = config.public.apiBase
      
      const body: any = {}
      if (filters.value.level) body.level = filters.value.level
      if (filters.value.startDate) body.start_date = filters.value.startDate
      if (filters.value.endDate) body.end_date = filters.value.endDate
      
      await $fetch(`${apiBase}/error-logs`, {
        method: 'DELETE',
        headers: getAuthHeaders(),
        body: Object.keys(body).length > 0 ? body : undefined
      })
      
      await fetchLogs()
    } catch (error: any) {
      alert(error?.data?.error || 'エラーログの削除に失敗しました')
    }
  }
}

onMounted(async () => {
  console.log('[company/error-logs] onMounted called')
  
  // 認証チェック
  authStore.loadUserFromStorage()
  console.log('[company/error-logs] isAuthenticated:', authStore.isAuthenticated)
  console.log('[company/error-logs] user:', authStore.user)
  console.log('[company/error-logs] isOwner:', authStore.isOwner)
  console.log('[company/error-logs] isManager:', authStore.isManager)
  
  if (!authStore.isAuthenticated) {
    console.log('[company/error-logs] Not authenticated, redirecting to login')
    await navigateTo('/company/login')
    return
  }
  
  // API側で権限チェックを行うため、フロントエンドでは認証のみ確認
  await fetchLogs()
})
</script>

