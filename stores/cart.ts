import { defineStore } from 'pinia'
import type { CartItem, Menu } from '~/types'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
    tableNumber: '',
    visitorId: null as string | null
  }),

  getters: {
    totalItems: (state) => {
      return state.items.reduce((sum, item) => sum + item.quantity, 0)
    },

    totalAmount: (state) => {
      return state.items.reduce((sum, item) => sum + (item.menu.price * item.quantity), 0)
    },

    isEmpty: (state) => {
      return state.items.length === 0
    }
  },

  actions: {
    addItem(menu: Menu, quantity: number = 1) {
      const existingItem = this.items.find(item => item.menu.id === menu.id)
      
      if (existingItem) {
        existingItem.quantity += quantity
      } else {
        this.items.push({ menu, quantity })
      }
    },

    removeItem(menuId: string) {
      const index = this.items.findIndex(item => item.menu.id === menuId)
      if (index > -1) {
        this.items.splice(index, 1)
      }
    },

    updateQuantity(menuId: string, quantity: number) {
      const item = this.items.find(item => item.menu.id === menuId)
      if (item) {
        if (quantity <= 0) {
          this.removeItem(menuId)
        } else {
          item.quantity = quantity
        }
      }
    },

    clearCart() {
      this.items = []
      this.tableNumber = ''
      // ストレージからも削除（会計完了時以外の注文書クリア時はテーブル番号は保持）
      // 注意: 会計完了時は clearSession() を使用すること
    },

    setTableNumber(tableNumber: string) {
      this.tableNumber = tableNumber
      // ローカルストレージに保存
      if (typeof window !== 'undefined') {
        if (tableNumber) {
          localStorage.setItem('tableNumber', tableNumber)
        } else {
          localStorage.removeItem('tableNumber')
        }
      }
    },

    loadTableNumberFromStorage() {
      if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('tableNumber')
        if (stored) {
          this.tableNumber = stored
        }
      }
    },

    clearSession() {
      // 会計完了時にセッション情報をクリア
      this.items = []
      this.tableNumber = ''
      this.visitorId = null
      if (typeof window !== 'undefined') {
        localStorage.removeItem('tableNumber')
        localStorage.removeItem('activeOrderId')
        localStorage.removeItem('activeVisitorId')
      }
    },

    setVisitorId(visitorId: string | null) {
      this.visitorId = visitorId
      // ブラウザリロード後も来店情報を維持するため、ローカルストレージに保存
      if (typeof window !== 'undefined') {
        if (visitorId) {
          localStorage.setItem('activeVisitorId', visitorId)
        } else {
          localStorage.removeItem('activeVisitorId')
        }
      }
    }
  }
})

