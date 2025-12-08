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
    },

    setTableNumber(tableNumber: string) {
      this.tableNumber = tableNumber
    },

    setVisitorId(visitorId: string | null) {
      this.visitorId = visitorId
    }
  }
})

