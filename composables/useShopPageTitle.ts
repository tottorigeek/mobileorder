/**
 * 店舗管理画面用のページタイトル生成
 */
export const useShopPageTitle = (baseTitle: string) => {
  const shopStore = useShopStore()
  
  const pageTitle = computed(() => {
    if (shopStore.currentShop) {
      return `${shopStore.currentShop.name} - ${baseTitle}`
    }
    return baseTitle
  })
  
  return {
    pageTitle
  }
}

