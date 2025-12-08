/**
 * 店舗管理画面用のナビゲーションタブ定義
 */
export const useShopNavigation = () => {
  const route = useRoute()
  
  const navigationItems = computed(() => {
    const items = [
      { to: '/shop/dashboard', label: 'ダッシュボード' },
      { to: '/shop/users', label: 'スタッフ管理' },
      { to: '/staff/tables', label: 'テーブル設定' },
      { to: '/staff/orders', label: '注文管理' }
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

