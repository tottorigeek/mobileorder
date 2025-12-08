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
    }
  }
})

