import { defineStore } from 'pinia'
import type { User } from '~/types'

// 認証トークンを取得するヘルパー関数
function getAuthHeaders(): Record<string, string> {
  // ローカルストレージから直接トークンを取得（循環参照を避けるため）
  const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }
  return headers
}

export interface CreateUserInput {
  username: string
  password: string
  name: string
  email?: string
  role?: 'owner' | 'manager' | 'staff'
  shopId?: string // 複数店舗対応: 店舗IDを指定可能
}

export interface UpdateUserInput {
  name?: string
  email?: string
  role?: 'owner' | 'manager' | 'staff'
  isActive?: boolean
}

export interface ChangePasswordInput {
  currentPassword: string
  newPassword: string
}

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [] as User[],
    currentUser: null as User | null,
    isLoading: false
  }),

  getters: {
    activeUsers: (state) => {
      return state.users.filter(user => user.isActive)
    },
    staffUsers: (state) => {
      return state.users.filter(user => user.role === 'staff')
    },
    managerUsers: (state) => {
      return state.users.filter(user => user.role === 'manager')
    }
  },

  actions: {
    async fetchUsers() {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const data = await $fetch<User[]>(`${apiBase}/users`, {
          headers: getAuthHeaders()
        })
        this.users = data || []
      } catch (error) {
        console.error('ユーザー一覧の取得に失敗しました:', error)
        this.users = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchAllUsers() {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const data = await $fetch<User[]>(`${apiBase}/company-users`, {
          headers: getAuthHeaders()
        })
        this.users = data || []
        return data || []
      } catch (error) {
        console.error('全ユーザー一覧の取得に失敗しました:', error)
        this.users = []
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchUser(userId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const user = await $fetch<User>(`${apiBase}/users/${userId}`, {
          headers: getAuthHeaders()
        })
        this.currentUser = user
        return user
      } catch (error) {
        console.error('ユーザー情報の取得に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async createUser(input: CreateUserInput) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const user = await $fetch<User>(`${apiBase}/users`, {
          method: 'POST',
          body: input,
          headers: getAuthHeaders()
        })
        
        this.users.push(user)
        return user
      } catch (error) {
        console.error('ユーザーの作成に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateUser(userId: string, input: UpdateUserInput) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const user = await $fetch<User>(`${apiBase}/users/${userId}`, {
          method: 'PUT',
          body: input,
          headers: getAuthHeaders()
        })
        
        const index = this.users.findIndex(u => u.id === userId)
        if (index > -1) {
          this.users[index] = user
        }
        
        if (this.currentUser?.id === userId) {
          this.currentUser = user
        }
        
        return user
      } catch (error) {
        console.error('ユーザー情報の更新に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async changePassword(userId: string, input: ChangePasswordInput) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        // サービスワーカーをバイパスするため、直接fetchを使用
        const headers = getAuthHeaders()
        const response = await fetch(`${apiBase}/users/${userId}/password`, {
          method: 'PUT',
          headers: headers,
          body: JSON.stringify(input),
          cache: 'no-store', // サービスワーカーのキャッシュをバイパス
          mode: 'cors' // CORSモードを明示的に指定
        })
        
        if (!response.ok) {
          let errorMessage = `HTTP error! status: ${response.status}`
          try {
            const errorData = await response.json()
            errorMessage = errorData.error || errorMessage
          } catch (e) {
            // JSON解析に失敗した場合は、ステータステキストを使用
            errorMessage = response.statusText || errorMessage
          }
          
          // 401エラーの場合は、より詳細なメッセージを提供
          if (response.status === 401) {
            errorMessage = '認証に失敗しました。再度ログインしてください。'
          }
          
          const error = new Error(errorMessage) as any
          error.status = response.status
          error.statusText = response.statusText
          throw error
        }
        
        const result = await response.json()
        return result
      } catch (error: any) {
        console.error('パスワードの変更に失敗しました:', error)
        // エラーオブジェクトにstatus情報を追加
        if (!error.status && error.message) {
          const statusMatch = error.message.match(/status: (\d+)/)
          if (statusMatch) {
            error.status = parseInt(statusMatch[1])
          }
        }
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateCompanyUser(userId: string, input: UpdateUserInput) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        const user = await $fetch<User>(`${apiBase}/company-users/${userId}`, {
          method: 'PUT',
          body: input,
          headers: getAuthHeaders()
        })
        
        const index = this.users.findIndex(u => u.id === userId)
        if (index > -1) {
          this.users[index] = user
        }
        
        if (this.currentUser?.id === userId) {
          this.currentUser = user
        }
        
        return user
      } catch (error) {
        console.error('ユーザー情報の更新に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteCompanyUser(userId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        await $fetch(`${apiBase}/company-users/${userId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        })
        
        this.users = this.users.filter(u => u.id !== userId)
        if (this.currentUser?.id === userId) {
          this.currentUser = null
        }
      } catch (error) {
        console.error('ユーザーの削除に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async deleteUser(userId: string) {
      this.isLoading = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase
        
        await $fetch(`${apiBase}/users/${userId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        })
        
        this.users = this.users.filter(u => u.id !== userId)
        if (this.currentUser?.id === userId) {
          this.currentUser = null
        }
      } catch (error) {
        console.error('ユーザーの削除に失敗しました:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    }
  }
})

