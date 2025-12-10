import { defineStore } from 'pinia'
import type { ShopCategory } from '~/types'

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [] as ShopCategory[],
    isLoading: false,
    currentShopId: null as string | null
  }),

  getters: {
    activeCategories: (state) => {
      return state.categories.filter(category => category.isActive)
        .sort((a, b) => a.displayOrder - b.displayOrder)
    },
    inactiveCategories: (state) => {
      return state.categories.filter(category => !category.isActive)
    }
  },

  actions: {
    async fetchCategories(shopId: string) {
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
        
        const data = await $fetch<ShopCategory[]>(`${apiBase}/categories?shop_id=${shopId}`, {
          headers: headers
        })
        this.categories = data || []
        return data || []
      } catch (error) {
        console.error('カテゴリ一覧の取得に失敗しました:', error)
        this.categories = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchCategoriesByShopCode(shopCode: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const categories = await $fetch<ShopCategory[]>(`${apiBase}/categories/shop/${encodeURIComponent(shopCode)}`)
        this.categories = categories || []
        return categories || []
      } catch (error) {
        console.error('店舗コードからのカテゴリ一覧取得に失敗しました:', error)
        this.categories = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async createCategory(shopId: string, categoryData: {
      code: string
      name: string
      displayOrder?: number
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
        
        const newCategory = await $fetch<ShopCategory>(`${apiBase}/categories`, {
          method: 'POST',
          body: categoryData,
          headers: headers
        })
        
        // カテゴリ一覧に追加
        this.categories.push(newCategory)
        return newCategory
      } catch (error) {
        console.error('カテゴリの作成に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateCategory(categoryId: string, categoryData: Partial<ShopCategory>) {
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
        
        const updatedCategory = await $fetch<ShopCategory>(`${apiBase}/categories/${categoryId}`, {
          method: 'PUT',
          body: categoryData,
          headers: headers
        })
        
        // カテゴリ一覧を更新
        const index = this.categories.findIndex(c => c.id === categoryId)
        if (index > -1) {
          this.categories[index] = updatedCategory
        }
        
        return updatedCategory
      } catch (error) {
        console.error('カテゴリの更新に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteCategory(categoryId: string) {
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
        
        await $fetch(`${apiBase}/categories/${categoryId}`, {
          method: 'DELETE',
          headers: headers
        })
        
        // カテゴリ一覧から削除
        this.categories = this.categories.filter(c => c.id !== categoryId)
      } catch (error) {
        console.error('カテゴリの削除に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    }
  }
})

