/**
 * 認証状態を自動的に復元するプラグイン
 * ページ遷移時にローカルストレージから認証状態を読み込む
 */

export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore()
  
  // クライアント側でのみ実行
  if (process.client) {
    // ストレージから認証状態を復元
    authStore.loadUserFromStorage()
    
    // 定期的にセッションの有効性を確認（5分ごと）
    if (authStore.isAuthenticated) {
      setInterval(async () => {
        try {
          await authStore.checkAuth()
        } catch (error) {
          console.warn('セッション確認に失敗しました:', error)
          // エラーが発生しても認証状態は維持
        }
      }, 5 * 60 * 1000) // 5分
    }
  }
})

