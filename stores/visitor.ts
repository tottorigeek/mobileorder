import { defineStore } from 'pinia'
import type { Visitor } from '~/types'

export const useVisitorStore = defineStore('visitor', {
  state: () => ({
    visitors: [] as Visitor[],
    currentVisitor: null as Visitor | null,
    isLoading: false
  }),

  getters: {
    // 支払い待ちのvisitor
    pendingPaymentVisitors: (state) => {
      return state.visitors.filter(v => v.paymentStatus === 'pending' && v.checkoutTime)
    },
    
    // セット未完了のvisitor
    uncompletedSetVisitors: (state) => {
      return state.visitors.filter(v => v.paymentStatus === 'completed' && !v.isSetCompleted)
    }
  },

  actions: {
    async createVisitor(visitorData: {
      shopId: string
      tableNumber: string
      numberOfGuests: number
      tableId?: string
    }) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const visitor = await $fetch<Visitor>(`${apiBase}/visitors`, {
          method: 'POST',
          body: visitorData,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        
        this.currentVisitor = visitor
        return visitor
      } catch (error) {
        console.error('来店情報の作成に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchVisitors(shopId: string, filters?: {
      tableId?: string
      tableNumber?: string
      paymentStatus?: 'pending' | 'completed'
      isSetCompleted?: boolean
    }) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        
        const headers: Record<string, string> = {
          'Accept': 'application/json'
        }
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
        
        let url = `${apiBase}/visitors?shop_id=${shopId}`
        if (filters) {
          if (filters.tableId) {
            url += `&table_id=${filters.tableId}`
          }
          if (filters.tableNumber) {
            url += `&table_number=${filters.tableNumber}`
          }
          if (filters.paymentStatus) {
            url += `&payment_status=${filters.paymentStatus}`
          }
          if (filters.isSetCompleted !== undefined) {
            url += `&is_set_completed=${filters.isSetCompleted ? 1 : 0}`
          }
        }
        
        const data = await $fetch<Visitor[]>(url, { headers })
        this.visitors = data || []
        return data || []
      } catch (error) {
        console.error('来店情報の取得に失敗しました:', error)
        this.visitors = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchVisitor(visitorId: string) {
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
        
        const visitor = await $fetch<Visitor>(`${apiBase}/visitors/${visitorId}`, { headers })
        this.currentVisitor = visitor
        return visitor
      } catch (error) {
        console.error('来店情報の取得に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async processCheckout(visitorId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const visitor = await $fetch<Visitor>(`${apiBase}/visitors/${visitorId}/checkout`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        
        // ローカルストアを更新
        const index = this.visitors.findIndex(v => v.id === visitorId)
        if (index > -1) {
          this.visitors[index] = visitor
        }
        if (this.currentVisitor?.id === visitorId) {
          this.currentVisitor = visitor
        }
        
        return visitor
      } catch (error) {
        console.error('会計処理に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async processPayment(visitorId: string, paymentMethod: 'cash' | 'credit' | 'paypay') {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const visitor = await $fetch<Visitor>(`${apiBase}/visitors/${visitorId}/payment`, {
          method: 'PUT',
          body: { paymentMethod },
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        
        // ローカルストアを更新
        const index = this.visitors.findIndex(v => v.id === visitorId)
        if (index > -1) {
          this.visitors[index] = visitor
        }
        if (this.currentVisitor?.id === visitorId) {
          this.currentVisitor = visitor
        }
        
        return visitor
      } catch (error) {
        console.error('支払い処理に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async completeTableSet(visitorId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        if (!token) {
          throw new Error('認証トークンが見つかりません')
        }
        
        const visitor = await $fetch<Visitor>(`${apiBase}/visitors/${visitorId}/set-complete`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        })
        
        // ローカルストアを更新
        const index = this.visitors.findIndex(v => v.id === visitorId)
        if (index > -1) {
          this.visitors[index] = visitor
        }
        if (this.currentVisitor?.id === visitorId) {
          this.currentVisitor = visitor
        }
        
        return visitor
      } catch (error) {
        console.error('テーブルセット完了処理に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    }
  }
})

