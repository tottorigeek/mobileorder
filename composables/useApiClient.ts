import { useRuntimeConfig } from '#imports'

/**
 * API 呼び出し用の共通クライアント
 * - baseURL: `useApiBase` で正規化した URL
 * - Authorization ヘッダー自動付与（auth store / cookie / localStorage）
 * - 401 の場合は共通でログアウト＆画面遷移
 */
export const useApiClient = () => {
  const { buildUrl } = useApiBase()
  const authStore = useAuthStore()

  const apiFetch = $fetch.create({
    baseURL: buildUrl(''),
    credentials: 'include',
    retry: 0,
    async onRequest({ options }) {
      const token = authStore.getAuthToken()

      const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers as Record<string, string> | undefined)
      }

      if (token && token.trim() !== '') {
        headers.Authorization = `Bearer ${token}`
      }

      options.headers = headers
    },
    async onResponseError({ response }) {
      // 認証エラーは共通ハンドリング
      if (response?.status === 401) {
        // 既にログアウト処理中などでエラーしても致命的ではないので try/catch は不要
        await authStore.logout()
      }
    }
  })

  /**
   * buildUrl も一緒に返しておくことで、
   * 相対パスだけでなく完全なURLを組み立てたい場面にも対応
   */
  return {
    apiFetch,
    buildUrl
  }
}


