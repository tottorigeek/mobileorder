import type { OrderStatus, UserRole } from '~/types'

/**
 * 管理画面で使用する共通ユーティリティ関数
 */
export const useAdminUtils = () => {
  /**
   * 役割のラベルを取得
   */
  const getRoleLabel = (role: string): string => {
    const labels: Record<string, string> = {
      owner: 'オーナー',
      manager: '管理者',
      staff: 'スタッフ'
    }
    return labels[role] || role
  }

  /**
   * 役割のバッジクラスを取得
   */
  const getRoleBadgeClass = (role: string): string => {
    const classes: Record<string, string> = {
      owner: 'px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm',
      manager: 'px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm',
      staff: 'px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm'
    }
    return classes[role] || 'px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm'
  }

  /**
   * 注文ステータスのラベルを取得
   */
  const getStatusLabel = (status: OrderStatus): string => {
    const labels: Record<OrderStatus, string> = {
      pending: '受付待ち',
      accepted: '受付済み',
      cooking: '調理中',
      completed: '完成',
      cancelled: 'キャンセル'
    }
    return labels[status]
  }

  /**
   * 注文ステータスのバッジクラスを取得
   */
  const getStatusClass = (status: OrderStatus): string => {
    const classes: Record<OrderStatus, string> = {
      pending: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm',
      accepted: 'px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm',
      cooking: 'px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm',
      completed: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm',
      cancelled: 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm'
    }
    return classes[status]
  }

  /**
   * 日付をフォーマット
   */
  const formatDate = (date: Date | string, options?: Intl.DateTimeFormatOptions): string => {
    const d = typeof date === 'string' ? new Date(date) : date
    const defaultOptions: Intl.DateTimeFormatOptions = {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }
    return d.toLocaleString('ja-JP', options || defaultOptions)
  }

  /**
   * 詳細な日付フォーマット（年、月、日、時、分を含む）
   */
  const formatDateDetailed = (date: Date | string): string => {
    const d = typeof date === 'string' ? new Date(date) : date
    return d.toLocaleString('ja-JP', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return {
    getRoleLabel,
    getRoleBadgeClass,
    getStatusLabel,
    getStatusClass,
    formatDate,
    formatDateDetailed
  }
}


