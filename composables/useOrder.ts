import { useCartStore } from '~/stores/cart'
import { useOrderStore } from '~/stores/order'

export const useOrder = () => {
  const cartStore = useCartStore()
  const orderStore = useOrderStore()

  const submitOrder = async () => {
    if (cartStore.isEmpty || !cartStore.tableNumber) {
      throw new Error('カートが空か、テーブル番号が設定されていません')
    }

    const orderItems = cartStore.items.map(item => ({
      menuId: item.menu.id,
      menuNumber: item.menu.number,
      menuName: item.menu.name,
      quantity: item.quantity,
      price: item.menu.price
    }))

    // API経由で注文を作成
    const orderData = await $fetch('/api/orders', {
      method: 'POST',
      body: {
        tableNumber: cartStore.tableNumber,
        items: orderItems,
        totalAmount: cartStore.totalAmount
      }
    })

    const order = await orderStore.createOrder(orderData)

    // 注文後はカートをクリア
    cartStore.clearCart()

    return order
  }

  return {
    submitOrder
  }
}

