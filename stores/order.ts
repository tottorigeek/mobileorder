import { defineStore } from 'pinia'
import type { Order, OrderStatus } from '~/types'

export const useOrderStore = defineStore('order', {
  state: () => ({
    orders: [] as Order[],
    currentOrder: null as Order | null,
    isLoading: false,
    pollingInterval: null as ReturnType<typeof setInterval> | null,
    isPolling: false
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
        
        // 認証トークンを取得（ローカルストレージから直接取得）
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        const headers: Record<string, string> = {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
        
        // API経由でステータスを更新
        const updatedOrder = await $fetch<Order>(`${apiBase}/orders/${orderId}`, {
          method: 'PUT',
          body: { status },
          headers: headers
        })
        
        // ローカルストアも更新
        const order = this.orders.find(o => o.id === orderId)
        if (order) {
          order.status = status
          order.updatedAt = new Date(updatedOrder.updatedAt)
        }
        
        return updatedOrder
      } catch (error: any) {
        console.error('注文ステータスの更新に失敗しました:', error)
        // エラーを再スローして、呼び出し元で処理できるようにする
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchOrder(orderId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得（オプション）
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        const headers: Record<string, string> = {
          'Accept': 'application/json'
        }
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
        
        const order = await $fetch<Order>(`${apiBase}/orders/${orderId}`, {
          headers
        })
        
        // 日付文字列をDateオブジェクトに変換
        const formattedOrder: Order = {
          ...order,
          createdAt: new Date(order.createdAt),
          updatedAt: new Date(order.updatedAt)
        }
        
        // 既存の注文を更新または追加
        const index = this.orders.findIndex(o => o.id === orderId)
        if (index > -1) {
          this.orders[index] = formattedOrder
        } else {
          this.orders.push(formattedOrder)
        }
        
        this.currentOrder = formattedOrder
        return formattedOrder
      } catch (error) {
        console.error('注文の取得に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchOrders(status?: OrderStatus, shopCode?: string, shopIds?: string[], tableNumber?: string, visitorId?: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得（オプション）
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        const headers: Record<string, string> = {
          'Accept': 'application/json'
        }
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
        
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
              if (tableNumber) {
                url += `&tableNumber=${tableNumber}`
              }
              if (visitorId) {
                url += `&visitorId=${visitorId}`
              }
              
              const data = await $fetch<Order[]>(url, { headers })
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
          if (tableNumber) {
            params.push(`tableNumber=${tableNumber}`)
          }
          if (visitorId) {
            params.push(`visitorId=${visitorId}`)
          }
          
          if (params.length > 0) {
            url += `?${params.join('&')}`
          }
          
          const data = await $fetch<Order[]>(url, { headers })
          
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
    },

    // 注文状況の監視を開始（重複を防ぐ）
    startPolling(options?: {
      status?: OrderStatus
      shopCode?: string
      shopIds?: string[]
      tableNumber?: string
      visitorId?: string
      interval?: number
    }) {
      // 既に監視中の場合は停止してから再開
      if (this.pollingInterval) {
        this.stopPolling()
      }

      const interval = options?.interval || 5000
      const pollParams = {
        status: options?.status,
        shopCode: options?.shopCode,
        shopIds: options?.shopIds,
        tableNumber: options?.tableNumber,
        visitorId: options?.visitorId
      }

      this.isPolling = true
      this.pollingInterval = setInterval(async () => {
        if (!this.isPolling) return
        try {
          await this.fetchOrders(
            pollParams.status,
            pollParams.shopCode,
            pollParams.shopIds,
            pollParams.tableNumber,
            pollParams.visitorId
          )
        } catch (error) {
          console.error('注文状況の監視中にエラーが発生しました:', error)
        }
      }, interval)
    },

    // 注文状況の監視を停止
    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval)
        this.pollingInterval = null
      }
      this.isPolling = false
    }
  }
})

