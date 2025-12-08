import { defineStore } from 'pinia'
import type { Order, OrderStatus } from '~/types'

export const useOrderStore = defineStore('order', {
  state: () => ({
    orders: [] as Order[],
    currentOrder: null as Order | null,
    isLoading: false
  }),

  getters: {
    // ステータス別の注文
    ordersByStatus: (state) => (status: OrderStatus) => {
      return state.orders.filter(order => order.status === status)
    },

    // テーブル番号で注文を検索
    orderByTableNumber: (state) => (tableNumber: string) => {
      return state.orders.find(order => order.tableNumber === tableNumber && order.status !== 'completed')
    }
  },

  actions: {
    async createOrder(orderData: Partial<Order>) {
      this.isLoading = true
      try {
        // APIから返された注文データを使用
        const newOrder: Order = {
          id: orderData.id || Date.now().toString(),
          orderNumber: orderData.orderNumber || `ORD-${Date.now()}`,
          tableNumber: orderData.tableNumber || '',
          items: orderData.items || [],
          status: orderData.status || 'pending',
          totalAmount: orderData.totalAmount || 0,
          createdAt: orderData.createdAt ? new Date(orderData.createdAt) : new Date(),
          updatedAt: orderData.updatedAt ? new Date(orderData.updatedAt) : new Date()
        }
        
        this.orders.push(newOrder)
        this.currentOrder = newOrder
        return newOrder
      } finally {
        this.isLoading = false
      }
    },

    async updateOrderStatus(orderId: string, status: OrderStatus) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // API経由でステータスを更新
        const updatedOrder = await $fetch(`${apiBase}/orders/${orderId}`, {
          method: 'PUT',
          body: { status }
        })
        
        // ローカルストアも更新
        const order = this.orders.find(o => o.id === orderId)
        if (order) {
          order.status = status
          order.updatedAt = new Date(updatedOrder.updatedAt)
        }
      } catch (error) {
        console.error('注文ステータスの更新に失敗しました:', error)
        // フォールバック: ローカルのみ更新
        const order = this.orders.find(o => o.id === orderId)
        if (order) {
          order.status = status
          order.updatedAt = new Date()
        }
      } finally {
        this.isLoading = false
      }
    },

    async fetchOrders(status?: OrderStatus, shopCode?: string, shopIds?: string[]) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 複数店舗IDが指定されている場合は、各店舗の注文を取得して結合
        if (shopIds && shopIds.length > 0) {
          const orderPromises = shopIds.map(async (shopId) => {
            try {
              // まず店舗コードを取得
              const shopStore = useShopStore()
              const shop = shopStore.shops.find(s => s.id === shopId)
              if (!shop) return []
              
              let url = `${apiBase}/orders?shop=${shop.code}`
              if (status) {
                url += `&status=${status}`
              }
              
              const data = await $fetch<Order[]>(url)
              return data || []
            } catch (error) {
              console.error(`店舗 ${shopId} の注文取得に失敗:`, error)
              return []
            }
          })
          
          const orderArrays = await Promise.all(orderPromises)
          const allOrders = orderArrays.flat()
          
          // 日付文字列をDateオブジェクトに変換
          this.orders = allOrders.map(order => ({
            ...order,
            createdAt: new Date(order.createdAt),
            updatedAt: new Date(order.updatedAt)
          }))
        } else {
          // 既存のロジック（単一店舗）
          let url = `${apiBase}/orders`
          const params: string[] = []
          
          if (shopCode) {
            params.push(`shop=${shopCode}`)
          }
          if (status) {
            params.push(`status=${status}`)
          }
          
          if (params.length > 0) {
            url += `?${params.join('&')}`
          }
          
          const data = await $fetch<Order[]>(url)
          
          // 日付文字列をDateオブジェクトに変換
          this.orders = (data || []).map(order => ({
            ...order,
            createdAt: new Date(order.createdAt),
            updatedAt: new Date(order.updatedAt)
          }))
        }
      } catch (error) {
        console.error('注文の取得に失敗しました:', error)
        this.orders = []
      } finally {
        this.isLoading = false
      }
    }
  }
})

