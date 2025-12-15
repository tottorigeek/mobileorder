/**
 * $fetch に Authorization ヘッダーを自動付与するプラグイン
 * - 既存の $fetch をラップして再代入する
 * - トークンがある場合のみ付与し、既存ヘッダーを保持
 */
import { $fetch as ofetch } from 'ofetch'

export default defineNuxtPlugin((nuxtApp) => {
  if (process.server) return

  const authStore = useAuthStore()
  const router = nuxtApp.$router

  // Nuxtの$fetchが未定義な場合に備え、ofetchをフォールバックに使う
  const baseFetch: typeof ofetch =
    (nuxtApp as any).$fetch ||
    (globalThis as any).$fetch ||
    ofetch

  const authFetch = baseFetch.create({
    onRequest({ options }) {
      // visitor配下（来店客向け画面）では認証ヘッダーを付けない
      // ログイン不要なAPI呼び出しで403になるのを防ぐため
      const currentPath =
        (router?.currentRoute?.value?.path as string | undefined) ||
        (typeof window !== 'undefined' ? window.location.pathname : '')

      if (currentPath && currentPath.startsWith('/visitor')) {
        return
      }

      const token = authStore.getAuthToken()
      if (!token) return

      options.headers = {
        ...(options.headers || {}),
        Authorization: `Bearer ${token}`
      }
    }
  })

  // 既存の $fetch を差し替える（クライアントのみ）
  ;(nuxtApp as any).$fetch = authFetch
  ;(globalThis as any).$fetch = authFetch
})


