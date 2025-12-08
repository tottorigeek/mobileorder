import { defineStore } from 'pinia'
import type { ShopTable } from '~/types'

export const useTableStore = defineStore('table', {
  state: () => ({
    tables: [] as ShopTable[],
    isLoading: false,
    currentShopId: null as string | null
  }),

  getters: {
    activeTables: (state) => {
      return state.tables.filter(table => table.isActive)
    },
    inactiveTables: (state) => {
      return state.tables.filter(table => !table.isActive)
    }
  },

  actions: {
    async fetchTables(shopId: string) {
      this.isLoading = true
      this.currentShopId = shopId
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        if (!token) {
          throw new Error('認証トークンが見つかりません')
        }
        
        const headers: Record<string, string> = {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
        
        const data = await $fetch<ShopTable[]>(`${apiBase}/tables?shop_id=${shopId}`, {
          headers: headers
        })
        this.tables = data || []
        return data || []
      } catch (error) {
        console.error('テーブル一覧の取得に失敗しました:', error)
        this.tables = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async createTable(shopId: string, tableData: {
      tableNumber: string
      name?: string
      capacity?: number
      isActive?: boolean
    }) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        if (!token) {
          throw new Error('認証トークンが見つかりません')
        }
        
        const headers: Record<string, string> = {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
        
        const newTable = await $fetch<ShopTable>(`${apiBase}/tables`, {
          method: 'POST',
          body: tableData,
          headers: headers
        })
        
        // テーブル一覧に追加
        this.tables.push(newTable)
        return newTable
      } catch (error) {
        console.error('テーブルの作成に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateTable(tableId: string, tableData: Partial<ShopTable>) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        if (!token) {
          throw new Error('認証トークンが見つかりません')
        }
        
        const headers: Record<string, string> = {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
        
        const updatedTable = await $fetch<ShopTable>(`${apiBase}/tables/${tableId}`, {
          method: 'PUT',
          body: tableData,
          headers: headers
        })
        
        // テーブル一覧を更新
        const index = this.tables.findIndex(t => t.id === tableId)
        if (index > -1) {
          this.tables[index] = updatedTable
        }
        
        return updatedTable
      } catch (error) {
        console.error('テーブルの更新に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteTable(tableId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        if (!token) {
          throw new Error('認証トークンが見つかりません')
        }
        
        const headers: Record<string, string> = {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
        
        await $fetch(`${apiBase}/tables/${tableId}`, {
          method: 'DELETE',
          headers: headers
        })
        
        // テーブル一覧から削除
        this.tables = this.tables.filter(t => t.id !== tableId)
      } catch (error) {
        console.error('テーブルの削除に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchTableByQRCode(shopCode: string, tableNumber: string) {
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const table = await $fetch<ShopTable>(`${apiBase}/tables/qr/${shopCode}/${encodeURIComponent(tableNumber)}`)
        return table
      } catch (error) {
        console.error('QRコードからのテーブル情報取得に失敗しました:', error)
        throw error
      }
    },

    generateQRCodeUrl(shopCode: string, tableNumber: string): string {
      // フロントエンドのベースURLを取得
      const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
      return `${baseUrl}/customer?shop=${encodeURIComponent(shopCode)}&table=${encodeURIComponent(tableNumber)}`
    }
  }
})

