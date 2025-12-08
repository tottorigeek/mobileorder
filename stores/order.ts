import { defineStore } from 'pinia'
import type { Order, OrderStatus } from '~/types'

export const useOrderStore = defineStore('order', {
  state: () => ({
    orders: [] as Order[],
    currentOrder: null as Order | null,
    isLoading: false
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
        // TODO: API呼び出しに置き換え
        const newOrder: Order = {
          id: Date.now().toString(),
          orderNumber: `ORD-${Date.now()}`,
          tableNumber: orderData.tableNumber || '',
          items: orderData.items || [],
          status: 'pending',
          totalAmount: orderData.totalAmount || 0,
          createdAt: new Date(),
          updatedAt: new Date()
        }
        
        this.orders.push(newOrder)
        this.currentOrder = newOrder
        return newOrder
      } finally {
        this.isLoading = false
      }
    },

    async updateOrderStatus(orderId: string, status: OrderStatus) {
      const order = this.orders.find(o => o.id === orderId)
      if (order) {
        order.status = status
        order.updatedAt = new Date()
      }
    },

    async fetchOrders() {
      this.isLoading = true
      try {
        // TODO: API呼び出しに置き換え
      } finally {
        this.isLoading = false
      }
    }
  }
})

