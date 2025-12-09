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

    // 番号でメニューを検索（ゼロパディング対応）
    getMenuByNumber: (state) => (number: string) => {
      if (!number) return undefined
      
      // まず、入力された値そのままで検索
      let menu = state.menus.find(menu => menu.number === number)
      
      // 見つからない場合、ゼロパディングした値で検索
      if (!menu) {
        const numValue = parseInt(number, 10)
        if (!isNaN(numValue)) {
          const formattedNumber = numValue.toString().padStart(3, '0')
          menu = state.menus.find(menu => menu.number === formattedNumber)
        }
      }
      
      // まだ見つからない場合、数値として比較（"001" と "1" をマッチさせる）
      if (!menu) {
        const numValue = parseInt(number, 10)
        if (!isNaN(numValue)) {
          menu = state.menus.find(menu => {
            const menuNum = parseInt(menu.number, 10)
            return !isNaN(menuNum) && menuNum === numValue
          })
        }
      }
      
      return menu
    },

    // おすすめメニュー
    recommendedMenus: (state) => {
      return state.menus.filter(menu => menu.isRecommended && menu.isAvailable)
    }
  },

  actions: {
    async fetchMenus(shopCode?: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // 店舗コードをクエリパラメータに追加
        let url = `${apiBase}/menus`
        if (shopCode) {
          url += `?shop=${shopCode}`
        }
        
        const data = await $fetch<Menu[]>(url)
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

