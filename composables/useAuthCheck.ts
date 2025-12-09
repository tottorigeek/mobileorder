import { useAuthStore } from '~/stores/auth'
import { useShopStore } from '~/stores/shop'

/**
 * 認証チェックとログアウト処理の共通ロジック
 */
export const useAuthCheck = () => {
  const authStore = useAuthStore()
  const shopStore = useShopStore()

  /**
   * 認証チェック（単一店舗用）
   * @param redirectToShopSelect 店舗が選択されていない場合に店舗選択画面にリダイレクトするか
   */
  const checkAuth = async (redirectToShopSelect = true): Promise<boolean> => {
    authStore.loadUserFromStorage()
    
    if (!authStore.isAuthenticated) {
      await navigateTo('/staff/login')
      return false
    }

    // 店舗情報の読み込み
    shopStore.loadShopFromStorage()
    
    // 店舗が選択されていない場合の処理
    if (!shopStore.currentShop) {
      if (authStore.user?.shop) {
        shopStore.setCurrentShop(authStore.user.shop)
      } else if (redirectToShopSelect) {
        await navigateTo('/staff/shop-select')
        return false
      }
    }

    return true
  }

  /**
   * 複数店舗用の認証チェック
   */
  const checkAuthMultiShop = async (): Promise<boolean> => {
    authStore.loadUserFromStorage()
    
    if (!authStore.isAuthenticated) {
      await navigateTo('/staff/login')
      return false
    }

    return true
  }

  /**
   * ログアウト処理
   */
  const handleLogout = async (): Promise<void> => {
    if (confirm('ログアウトしますか？')) {
      await authStore.logout()
    }
  }

  return {
    checkAuth,
    checkAuthMultiShop,
    handleLogout
  }
}


