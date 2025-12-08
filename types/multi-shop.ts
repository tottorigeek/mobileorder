// 複数店舗対応 型定義

export interface ShopOwner {
  id: string
  name: string
  username: string
  email?: string | null
}

export interface BusinessHours {
  open: string // HH:mm形式（例: "10:00"）
  close: string // HH:mm形式（例: "22:00"）
  isClosed?: boolean // 休業日の場合true
}

// 定休日の設定タイプ
export interface RegularHoliday {
  type: 'weekly' | 'monthly' // 'weekly': 毎週, 'monthly': 毎月第〇曜日
  day: 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday'
  week?: number // 毎月の場合: 1-4 (第1-4週), -1 (最終週)
}

export interface ShopSettings {
  regularHolidays?: (string | RegularHoliday)[] // 定休日の設定（後方互換性のため文字列も許可）
  temporaryHolidays?: string[] // 臨時休業日の日付配列（例: ["2024-12-25", "2024-12-31"]）
  businessHours?: {
    monday?: BusinessHours
    tuesday?: BusinessHours
    wednesday?: BusinessHours
    thursday?: BusinessHours
    friday?: BusinessHours
    saturday?: BusinessHours
    sunday?: BusinessHours
  }
}

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
  shopRole?: 'owner' | 'manager' | 'staff' // この店舗での役割
  isPrimary?: boolean // 主店舗フラグ
  owner?: ShopOwner | null // オーナー情報（後方互換性のため）
  owners?: ShopOwner[] // 複数のオーナー情報
  settings?: ShopSettings // 店舗設定（営業時間・定休日など）
  createdAt?: string
  updatedAt?: string
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

