/**
 * 認証状態を自動的に復元するプラグイン
 * ページ遷移時にローカルストレージから認証状態を読み込む
 */

export default defineNuxtPlugin((nuxtApp) => {
  if (!process.client) return

  const authStore = useAuthStore()
  const router = nuxtApp.$router

  const isVisitorRoute = (path: string) => path.startsWith('/visitor')

  // visitor配下では認証処理を走らせないようガード
  let authInitialized = false
  let sessionCheckTimer: number | null = null

  const initAuth = () => {
    if (authInitialized) return

    authStore.loadUserFromStorage()

    if (authStore.isAuthenticated && sessionCheckTimer === null) {
      sessionCheckTimer = window.setInterval(async () => {
        try {
          await authStore.checkAuth()
        } catch (error) {
          console.warn('セッション確認に失敗しました:', error)
          // エラーが発生しても認証状態は維持
        }
      }, 5 * 60 * 1000) // 5分
    }

    authInitialized = true
  }

  const tryInitAuth = (path: string) => {
    if (!isVisitorRoute(path)) {
      initAuth()
    }
  }

  // 初回アクセス時のパスで判定
  tryInitAuth(router?.currentRoute?.value?.path || window.location.pathname)

  // ルート遷移時にvisitor以外へ移動したら初期化
  router?.afterEach((to) => {
    tryInitAuth(to.path)
  })
})

