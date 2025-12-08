// 型定義

export interface Menu {
  id: string
  number: string // メニュー番号（例: "001"）
  name: string
  description?: string
  price: number
  category: MenuCategory
  imageUrl?: string
  isAvailable: boolean
  isRecommended?: boolean
}

export type MenuCategory = string // 店舗独自カテゴリコード

export interface ShopCategory {
  id: string
  shopId: string
  shopCode: string
  shopName: string
  code: string
  name: string
  displayOrder: number
  isActive: boolean
  createdAt?: string
  updatedAt?: string
}

export interface CartItem {
  menu: Menu
  quantity: number
}

export interface Order {
  id: string
  orderNumber: string
  tableNumber: string
  items: OrderItem[]
  status: OrderStatus
  totalAmount: number
  createdAt: Date
  updatedAt: Date
}

export interface OrderItem {
  menuId: string
  menuNumber: string
  menuName: string
  quantity: number
  price: number
}

export type OrderStatus = 'pending' | 'accepted' | 'cooking' | 'completed' | 'cancelled'

export interface Table {
  id: string
  number: string
  isOccupied: boolean
  currentOrderId?: string
}

export interface ShopTable {
  id: string
  shopId: string
  shopCode: string
  shopName: string
  tableNumber: string
  name?: string
  capacity: number
  isActive: boolean
  qrCodeUrl?: string
  createdAt?: string
  updatedAt?: string
}

export interface Payment {
  id: string
  orderId: string
  amount: number
  method: PaymentMethod
  paidAt: Date
}

export type PaymentMethod = 'cash' | 'credit' | 'electronic'

