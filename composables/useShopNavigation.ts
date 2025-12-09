/**
 * 店舗管理画面用のナビゲーションタブ定義
 */
export const useShopNavigation = () => {
  const route = useRoute()
  
  const navigationItems = computed(() => {
    const items = [
      { to: '/shop/dashboard', label: 'ダッシュボード' },
      { to: '/shop/seats', label: '着座管理' },
      { to: '/staff/orders', label: '注文管理' },
      { to: '/shop/menus', label: 'メニュー管理' },
      { to: '/shop/categories', label: 'カテゴリ管理' },
      { to: '/shop/sales', label: '売上履歴' },
      { to: '/shop/users', label: 'スタッフ管理' },
      { to: '/shop/tables', label: 'テーブル設定' }
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

