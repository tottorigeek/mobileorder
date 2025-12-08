import { defineStore } from 'pinia'
import type { Menu, MenuCategory } from '~/types'

export const useMenuStore = defineStore('menu', {
  state: () => ({
    menus: [] as Menu[],
    selectedCategory: null as MenuCategory | null,
    isLoading: false
  }),

  getters: {
    // カテゴリ別メニュー
    menusByCategory: (state) => {
      if (!state.selectedCategory) return state.menus
      return state.menus.filter(menu => menu.category === state.selectedCategory)
    },

    // 利用可能なメニューのみ
    availableMenus: (state) => {
      return state.menus.filter(menu => menu.isAvailable)
    },

    // 番号でメニューを検索
    getMenuByNumber: (state) => (number: string) => {
      return state.menus.find(menu => menu.number === number)
    },

    // おすすめメニュー
    recommendedMenus: (state) => {
      return state.menus.filter(menu => menu.isRecommended && menu.isAvailable)
    }
  },

  actions: {
    async fetchMenus() {
      this.isLoading = true
      try {
        const data = await $fetch<Menu[]>('/api/menus')
        this.menus = data || []
      } catch (error) {
        console.error('メニューの取得に失敗しました:', error)
        // フォールバック: モックデータ
        this.menus = [
          {
            id: '1',
            number: '001',
            name: 'ハンバーガー',
            description: 'ジューシーなハンバーガー',
            price: 800,
            category: 'food',
            isAvailable: true,
            isRecommended: true
          },
          {
            id: '2',
            number: '002',
            name: 'フライドポテト',
            description: 'カリッと揚げたポテト',
            price: 400,
            category: 'food',
            isAvailable: true
          },
          {
            id: '3',
            number: '003',
            name: 'コーラ',
            description: '冷たいコーラ',
            price: 300,
            category: 'drink',
            isAvailable: true
          }
        ]
      } finally {
        this.isLoading = false
      }
    },

    setCategory(category: MenuCategory | null) {
      this.selectedCategory = category
    }
  }
})

