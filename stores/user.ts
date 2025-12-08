import { defineStore } from 'pinia'
import type { User } from '~/types'

export interface CreateUserInput {
  username: string
  password: string
  name: string
  email?: string
  role?: 'owner' | 'manager' | 'staff'
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
        
        const data = await $fetch<User[]>(`${apiBase}/users`)
        this.users = data || []
      } catch (error) {
        console.error('ユーザー一覧の取得に失敗しました:', error)
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
        
        const user = await $fetch<User>(`${apiBase}/users/${userId}`)
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
          body: input
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
          body: input
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
        
        await $fetch(`${apiBase}/users/${userId}/password`, {
          method: 'PUT',
          body: input
        })
      } catch (error) {
        console.error('パスワードの変更に失敗しました:', error)
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
          method: 'DELETE'
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

