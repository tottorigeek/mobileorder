/**
 * 複数店舗管理画面用のナビゲーションタブ定義
 */
export const useMultiShopNavigation = () => {
  const route = useRoute()
  
  const navigationItems = computed(() => {
    const items = [
      { to: '/multi-shop/dashboard', label: 'ダッシュボード' },
      { to: '/multi-shop/orders', label: '注文一覧' },
      { to: '/multi-shop/menus', label: 'メニュー管理' },
      { to: '/multi-shop/tables', label: 'テーブル管理' },
      { to: '/multi-shop/staff', label: 'スタッフ管理' }
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

