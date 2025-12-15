/**
 * $fetch に Authorization ヘッダーを自動付与するプラグイン
 * - 既存の $fetch をラップして再代入する
 * - トークンがある場合のみ付与し、既存ヘッダーを保持
 */
import { $fetch as ofetch } from 'ofetch'

export default defineNuxtPlugin((nuxtApp) => {
  if (process.server) return

  const authStore = useAuthStore()

  // Nuxtの$fetchが未定義な場合に備え、ofetchをフォールバックに使う
  const baseFetch: typeof ofetch =
    (nuxtApp as any).$fetch ||
    (globalThis as any).$fetch ||
    ofetch

  const authFetch = baseFetch.create({
    onRequest({ options }) {
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


