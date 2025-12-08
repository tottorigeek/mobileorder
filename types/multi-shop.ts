// 複数店舗対応 型定義

export interface Shop {
  id: string
  code: string // 店舗コード（URL用）
  name: string
  description?: string
  address?: string
  phone?: string
  email?: string
  maxTables?: number
  isActive: boolean
}

export interface User {
  id: string
  shopId: string
  username: string
  name: string
  email?: string
  role: UserRole
  isActive: boolean
  lastLoginAt?: string
  createdAt: string
  updatedAt: string
  shop?: Shop
}

export type UserRole = 'owner' | 'manager' | 'staff'

export interface Menu {
  id: string
  shopId: string // 追加
  number: string
  name: string
  description?: string
  price: number
  category: MenuCategory
  imageUrl?: string
  isAvailable: boolean
  isRecommended?: boolean
}

export type MenuCategory = 'drink' | 'food' | 'dessert' | 'other'

export interface CartItem {
  menu: Menu
  quantity: number
}

export interface Order {
  id: string
  shopId: string // 追加
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

export interface Payment {
  id: string
  shopId: string // 追加
  orderId: string
  amount: number
  method: PaymentMethod
  paidAt: Date
}

export type PaymentMethod = 'cash' | 'credit' | 'electronic'

