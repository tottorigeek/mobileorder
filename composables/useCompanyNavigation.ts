/**
 * 会社管理画面用のナビゲーションタブ定義
 */
export const useCompanyNavigation = () => {
  const route = useRoute()
  
  const navigationItems = computed(() => {
    const items = [
      { to: '/company/dashboard', label: 'ダッシュボード' },
      { to: '/company/shops', label: '店舗管理' },
      { to: '/company/users', label: 'ユーザー管理' },
      { to: '/company/orders', label: '注文管理' },
      { to: '/company/tables', label: 'テーブル管理' },
      { to: '/company/menus', label: 'メニュー管理' },
      { to: '/company/error-logs', label: 'エラーログ' }
    ]
    
    // 現在のパスに基づいてアクティブ状態を設定
    return items.map(item => ({
      ...item,
      isActive: route.path === item.to || route.path.startsWith(item.to + '/')
    }))
  })
  
  return {
    navigationItems
  }
}

