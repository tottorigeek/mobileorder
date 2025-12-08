import { defineStore } from 'pinia'
import type { Shop } from '~/types'

export const useShopStore = defineStore('shop', {
  state: () => ({
    currentShop: null as Shop | null,
    shops: [] as Shop[],
    isLoading: false
  }),

  getters: {
    hasShop: (state) => {
      return state.currentShop !== null
    }
  },

  actions: {
    async fetchShops() {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        const data = await $fetch<Shop[]>(`${apiBase}/shops`)
        this.shops = data || []
      } catch (error) {
        console.error('店舗一覧の取得に失敗しました:', error)
        this.shops = []
      } finally {
        this.isLoading = false
      }
    },

    async fetchMyShops() {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 認証トークンを取得（ローカルストレージから直接取得）
        const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
        const headers: Record<string, string> = {
          'Accept': 'application/json'
        }
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
        
        const data = await $fetch<Shop[]>(`${apiBase}/my-shops`, {
          headers: headers
        })
        this.shops = data || []
        return data || []
      } catch (error) {
        console.error('所属店舗一覧の取得に失敗しました:', error)
        this.shops = []
        return []
      } finally {
        this.isLoading = false
      }
    },

    async fetchShopByCode(shopCode: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        const shop = await $fetch<Shop>(`${apiBase}/shops/${shopCode}`)
        this.currentShop = shop
        return shop
      } catch (error) {
        console.error('店舗情報の取得に失敗しました:', error)
        this.currentShop = null
        return null
      } finally {
        this.isLoading = false
      }
    },

    setCurrentShop(shop: Shop | null) {
      this.currentShop = shop
      // ローカルストレージに保存
      if (shop) {
        localStorage.setItem('currentShop', JSON.stringify(shop))
      } else {
        localStorage.removeItem('currentShop')
      }
    },

    loadShopFromStorage() {
      const stored = localStorage.getItem('currentShop')
      if (stored) {
        try {
          this.currentShop = JSON.parse(stored)
        } catch (e) {
          console.error('店舗情報の読み込みに失敗しました:', e)
          localStorage.removeItem('currentShop')
        }
      }
    },

    async fetchShopById(shopId: string) {
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
        
        const shop = await $fetch<Shop>(`${apiBase}/shops/${shopId}`, {
          headers: headers
        })
        
        // 店舗一覧も更新
        const index = this.shops.findIndex(s => s.id === shopId)
        if (index > -1) {
          this.shops[index] = shop
        } else {
          this.shops.push(shop)
        }
        
        return shop
      } catch (error) {
        console.error('店舗情報の取得に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateShop(shopId: string, shopData: Partial<Shop>) {
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
        
        const updatedShop = await $fetch<Shop>(`${apiBase}/shops/${shopId}`, {
          method: 'PUT',
          body: shopData,
          headers: headers
        })
        
        // 店舗一覧を更新
        const index = this.shops.findIndex(s => s.id === shopId)
        if (index > -1) {
          this.shops[index] = updatedShop
        }
        
        // 現在の店舗も更新
        if (this.currentShop?.id === shopId) {
          this.currentShop = updatedShop
        }
        
        return updatedShop
      } catch (error) {
        console.error('店舗情報の更新に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    }
  }
})

