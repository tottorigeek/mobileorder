import { defineStore } from 'pinia'
import type { User } from '~/types/multi-shop'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
    isAuthenticated: false,
    isLoading: false
  }),

  getters: {
    isOwner: (state) => {
      return state.user?.role === 'owner'
    },
    isManager: (state) => {
      return state.user?.role === 'manager' || state.user?.role === 'owner'
    },
    isStaff: (state) => {
      return state.user !== null
    }
  },

  actions: {
    async login(username: string, password: string, companyLogin: boolean = false) {
      this.isLoading = true
      try {
        const { apiFetch, buildUrl } = useApiClient()

        const response = await apiFetch<{ success: boolean; token: string; user: User }>('auth/login', {
          method: 'POST',
          body: { username, password, company_login: companyLogin },
          credentials: 'include' // クッキーを含める（後方互換性のため）
        })
        
        if (response.success && response.user && response.token) {
          this.user = response.user
          this.token = response.token
          this.isAuthenticated = true
          // クッキーにも保存（SSR用）
          const authCookie = useCookie('auth_token', {
            sameSite: 'none',
            secure: true,
            path: '/',
            maxAge: 60 * 60 * 24 * 7 // 7日
          })
          authCookie.value = response.token
          // ローカルストレージに保存
          localStorage.setItem('auth_user', JSON.stringify(response.user))
          localStorage.setItem('auth_token', response.token)
          return true
        }
        return false
      } catch (error) {
        console.error('ログインに失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        const { apiFetch } = useApiClient()

        await apiFetch('auth/logout', {
          method: 'POST',
          credentials: 'include' // クッキーを含める
        })
      } catch (error) {
        console.error('ログアウトに失敗しました:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        localStorage.removeItem('auth_user')
        localStorage.removeItem('auth_token')
        const authCookie = useCookie('auth_token', { path: '/' })
        authCookie.value = null
        // ログアウト後はトップページにリダイレクト
        await navigateTo('/')
      }
    },

    async checkAuth() {
      try {
        const { apiFetch, buildUrl } = useApiClient()

        // トークンを取得（メモリ → ローカルストレージ → クッキー）
        const cookieToken = useCookie('auth_token').value
        const token = this.token || localStorage.getItem('auth_token') || cookieToken
        if (!token) {
          console.error('checkAuth: Token not found')
          throw new Error('Token not found')
        }
        
        // トークンが空文字列でないか確認
        if (token.trim() === '') {
          console.error('checkAuth: Token is empty string')
          throw new Error('Token is empty')
        }
        
        const url = buildUrl('auth/me')
        console.log('checkAuth: URL:', url)
        console.log('checkAuth: Token exists:', !!token, 'Token length:', token.length)
        
        const response = await apiFetch(url, {
          method: 'GET',
          cache: 'no-store'
        }) as unknown as User
        
        const user = response
        this.user = user
        this.token = token
        this.isAuthenticated = true
        // ローカルストレージも更新
        localStorage.setItem('auth_user', JSON.stringify(user))
        localStorage.setItem('auth_token', token)
        const authCookie = useCookie('auth_token', {
          sameSite: 'none',
          secure: true,
          path: '/',
          maxAge: 60 * 60 * 24 * 7
        })
        authCookie.value = token
        return true
      } catch (error: any) {
        // 開発環境（localhost）から本番環境へのリクエストでは、セッションクッキーが送信されないため
        // 401エラーが発生するのは正常な動作です
        const isDevelopment = typeof window !== 'undefined' && 
          (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')
        
        // エラーが発生しても、ローカルストレージにユーザー情報がある場合は認証状態を維持
        // これにより、一時的なネットワークエラーでログアウトされないようにする
        const stored = localStorage.getItem('auth_user')
        const storedToken = localStorage.getItem('auth_token')
        if (stored && storedToken) {
          try {
            this.user = JSON.parse(stored)
            this.token = storedToken
            this.isAuthenticated = true
            // 開発環境では警告を出さない（正常な動作のため）
            if (!isDevelopment) {
              console.warn('認証確認に失敗しましたが、ローカルストレージから認証状態を復元しました')
            }
            return false // 認証確認は失敗したが、認証状態は維持
          } catch (e) {
            // ローカルストレージのデータも無効な場合は完全にログアウト
            this.user = null
            this.token = null
            this.isAuthenticated = false
            localStorage.removeItem('auth_user')
            localStorage.removeItem('auth_token')
            return false
          }
        } else {
          // ローカルストレージにもデータがない場合は完全にログアウト
          this.user = null
          this.token = null
          this.isAuthenticated = false
          return false
        }
      }
    },

    loadUserFromStorage() {
      const stored = localStorage.getItem('auth_user')
      const storedToken = localStorage.getItem('auth_token')
      const cookieToken = useCookie('auth_token').value
      const token = storedToken || cookieToken
      if (stored && token) {
        try {
          this.user = JSON.parse(stored)
          this.token = token
          this.isAuthenticated = true
          
          // トークンの有効性をバックグラウンドで確認（非同期）
          // 失敗しても認証状態は維持する（ユーザー体験を優先）
          this.checkAuth().catch((error) => {
            // エラーは無視（認証状態は維持）
          })
        } catch (e) {
          console.error('ユーザー情報の読み込みに失敗しました:', e)
          localStorage.removeItem('auth_user')
          localStorage.removeItem('auth_token')
          this.user = null
          this.token = null
          this.isAuthenticated = false
        }
      }
    },
    
    // トークンを取得するヘルパー関数
    getAuthToken(): string | null {
      const cookieToken = useCookie('auth_token').value || null
      return this.token || localStorage.getItem('auth_token') || cookieToken
    }
  }
})

