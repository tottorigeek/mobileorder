import { defineStore } from 'pinia'
import type { User } from '~/types'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
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
    async login(username: string, password: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const response = await $fetch<{ success: boolean; user: User }>(`${apiBase}/auth/login`, {
          method: 'POST',
          body: { username, password },
          credentials: 'include' // クッキーを含める（セッション管理のため）
        })
        
        if (response.success && response.user) {
          this.user = response.user
          this.isAuthenticated = true
          // ローカルストレージに保存
          localStorage.setItem('auth_user', JSON.stringify(response.user))
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
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        await $fetch(`${apiBase}/auth/logout`, {
          method: 'POST',
          credentials: 'include' // クッキーを含める
        })
      } catch (error) {
        console.error('ログアウトに失敗しました:', error)
      } finally {
        this.user = null
        this.isAuthenticated = false
        localStorage.removeItem('auth_user')
        // ログアウト後はトップページにリダイレクト
        await navigateTo('/')
      }
    },

    async checkAuth() {
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const user = await $fetch<User>(`${apiBase}/auth/me`, {
          credentials: 'include' // クッキーを含める
        })
        this.user = user
        this.isAuthenticated = true
        // ローカルストレージも更新
        localStorage.setItem('auth_user', JSON.stringify(user))
        return true
      } catch (error) {
        // エラーが発生しても、ローカルストレージにユーザー情報がある場合は認証状態を維持
        // これにより、一時的なネットワークエラーでログアウトされないようにする
        const stored = localStorage.getItem('auth_user')
        if (stored) {
          try {
            this.user = JSON.parse(stored)
            this.isAuthenticated = true
            console.warn('セッション確認に失敗しましたが、ローカルストレージから認証状態を復元しました')
            return false // セッションは無効だが、認証状態は維持
          } catch (e) {
            // ローカルストレージのデータも無効な場合は完全にログアウト
            this.user = null
            this.isAuthenticated = false
            localStorage.removeItem('auth_user')
            return false
          }
        } else {
          // ローカルストレージにもデータがない場合は完全にログアウト
          this.user = null
          this.isAuthenticated = false
          return false
        }
      }
    },

    loadUserFromStorage() {
      const stored = localStorage.getItem('auth_user')
      if (stored) {
        try {
          this.user = JSON.parse(stored)
          this.isAuthenticated = true
          // セッションの有効性をバックグラウンドで確認（非同期）
          // 失敗しても認証状態は維持する（ユーザー体験を優先）
          this.checkAuth().catch((error) => {
            console.warn('セッション確認に失敗しましたが、認証状態は維持します:', error)
            // セッションが無効な場合でも、ユーザーが明示的にログアウトするまで認証状態を維持
          })
        } catch (e) {
          console.error('ユーザー情報の読み込みに失敗しました:', e)
          localStorage.removeItem('auth_user')
          this.user = null
          this.isAuthenticated = false
        }
      }
    }
  }
})

