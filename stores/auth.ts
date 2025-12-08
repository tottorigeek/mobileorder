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
          body: { username, password }
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
          method: 'POST'
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
        
        const user = await $fetch<User>(`${apiBase}/auth/me`)
        this.user = user
        this.isAuthenticated = true
        return true
      } catch (error) {
        this.user = null
        this.isAuthenticated = false
        localStorage.removeItem('auth_user')
        return false
      }
    },

    loadUserFromStorage() {
      const stored = localStorage.getItem('auth_user')
      if (stored) {
        try {
          this.user = JSON.parse(stored)
          this.isAuthenticated = true
          // セッションの有効性を確認
          this.checkAuth()
        } catch (e) {
          console.error('ユーザー情報の読み込みに失敗しました:', e)
          localStorage.removeItem('auth_user')
        }
      }
    }
  }
})

