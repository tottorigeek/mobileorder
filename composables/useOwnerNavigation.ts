/**
 * オーナー向け複数店舗管理 ナビゲーション
 */
export const useOwnerNavigation = () => {
  const route = useRoute()

  const navigationItems = computed(() => {
    const items = [
      { to: '/owner/dashboard', label: 'ダッシュボード' },
      { to: '/owner/orders', label: '注文一覧' },
      { to: '/owner/menus', label: 'メニュー管理' },
      { to: '/owner/tables', label: 'テーブル管理' },
      { to: '/owner/staff', label: 'スタッフ管理' }
    ]

    return items.map((item) => ({
      ...item,
      isActive: route.path === item.to || route.path.startsWith(item.to + '/')
    }))
  })

  return {
    navigationItems
  }
}


