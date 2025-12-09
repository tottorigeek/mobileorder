<template>
  <NuxtLayout name="default" :title="pageTitle">
    <div class="space-y-6">

      <!-- 期間選択 -->
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">期間選択</label>
            <select
              v-model="selectedPeriod"
              @change="updateChartData"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="7">過去7日間</option>
              <option value="30">過去30日間</option>
              <option value="90">過去90日間</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button
              @click="updateChartData"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              更新
            </button>
          </div>
        </div>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-4 sm:p-6 rounded-xl shadow-lg text-white">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs sm:text-sm font-medium opacity-90">期間合計売上</h3>
            <svg class="w-5 h-5 sm:w-6 sm:h-6 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-2xl sm:text-3xl font-bold">¥{{ totalSales.toLocaleString() }}</p>
        </div>
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border-l-4 border-green-500">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">平均日次売上</h3>
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">¥{{ averageDailySales.toLocaleString() }}</p>
        </div>
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">最高日次売上</h3>
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900">¥{{ maxDailySales.toLocaleString() }}</p>
        </div>
      </div>

      <!-- グラフ -->
      <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">日別売上推移</h3>
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600">
            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
            <span>売上</span>
          </div>
        </div>
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <div v-else-if="chartData.labels.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <p class="text-gray-500">データがありません</p>
        </div>
        <div v-else class="h-64 sm:h-96">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- 日別売上一覧テーブル -->
      <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">日別売上詳細</h3>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
          <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                  <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">売上</th>
                  <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">注文数</th>
                  <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">平均注文額</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(day, index) in dailySalesData" :key="index" class="hover:bg-gray-50">
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                    {{ formatDate(day.date) }}
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right font-semibold text-gray-900">
                    ¥{{ day.sales.toLocaleString() }}
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right text-gray-600">
                    {{ day.orderCount }}件
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-right text-gray-600">
                    ¥{{ day.averageOrderAmount.toLocaleString() }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

import { useOrderStore } from '~/stores/order'
import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'

const authStore = useAuthStore()
const shopStore = useShopStore()
const orderStore = useOrderStore()
const { checkAuth } = useAuthCheck()

const { pageTitle } = useShopPageTitle('売上履歴')

const selectedPeriod = ref('30')
const isLoading = ref(false)

// 日別売上データ
const dailySalesData = ref<Array<{
  date: Date
  sales: number
  orderCount: number
  averageOrderAmount: number
}>>([])

// グラフデータ
const chartData = computed(() => {
  return {
    labels: dailySalesData.value.map(d => formatDate(d.date)),
    datasets: [
      {
        label: '売上',
        data: dailySalesData.value.map(d => d.sales),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: 'rgb(59, 130, 246)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }
    ]
  }
})

// グラフオプション
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: {
        size: 14,
        weight: 'bold' as const
      },
      bodyFont: {
        size: 13
      },
      callbacks: {
        label: (context: any) => {
          return `売上: ¥${context.parsed.y.toLocaleString()}`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: any) => {
          return `¥${value.toLocaleString()}`
        }
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

// 統計値
const totalSales = computed(() => {
  return dailySalesData.value.reduce((sum, day) => sum + day.sales, 0)
})

const averageDailySales = computed(() => {
  if (dailySalesData.value.length === 0) return 0
  return Math.round(totalSales.value / dailySalesData.value.length)
})

const maxDailySales = computed(() => {
  if (dailySalesData.value.length === 0) return 0
  return Math.max(...dailySalesData.value.map(d => d.sales))
})

// 日付フォーマット
const formatDate = (date: Date) => {
  const month = date.getMonth() + 1
  const day = date.getDate()
  return `${month}/${day}`
}

// 日別売上データの集計
const calculateDailySales = () => {
  const days = parseInt(selectedPeriod.value)
  const endDate = new Date()
  endDate.setHours(23, 59, 59, 999)
  const startDate = new Date(endDate)
  startDate.setDate(startDate.getDate() - days + 1)
  startDate.setHours(0, 0, 0, 0)

  // 日付ごとの売上を集計
  const salesByDate = new Map<string, { sales: number; orderCount: number }>()

  // すべての日付を初期化
  for (let i = 0; i < days; i++) {
    const date = new Date(startDate)
    date.setDate(date.getDate() + i)
    const dateKey = date.toISOString().split('T')[0]
    salesByDate.set(dateKey, { sales: 0, orderCount: 0 })
  }

  // 注文データから売上を集計
  orderStore.orders
    .filter(order => {
      const orderDate = new Date(order.createdAt)
      return orderDate >= startDate && orderDate <= endDate && order.status === 'completed'
    })
    .forEach(order => {
      const orderDate = new Date(order.createdAt)
      const dateKey = orderDate.toISOString().split('T')[0]
      const dayData = salesByDate.get(dateKey)
      if (dayData) {
        dayData.sales += order.totalAmount
        dayData.orderCount += 1
      }
    })

  // 配列に変換
  dailySalesData.value = Array.from(salesByDate.entries())
    .map(([dateKey, data]) => {
      const date = new Date(dateKey)
      return {
        date,
        sales: data.sales,
        orderCount: data.orderCount,
        averageOrderAmount: data.orderCount > 0 ? Math.round(data.sales / data.orderCount) : 0
      }
    })
    .sort((a, b) => a.date.getTime() - b.date.getTime())
}

// チャートデータの更新
const updateChartData = async () => {
  isLoading.value = true
  try {
    await orderStore.fetchOrders()
    calculateDailySales()
  } catch (error) {
    console.error('データの取得に失敗しました:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  // 認証チェック
  const isAuthenticated = await checkAuth()
  if (!isAuthenticated) {
    return
  }

  // データを取得
  await updateChartData()
})
</script>

