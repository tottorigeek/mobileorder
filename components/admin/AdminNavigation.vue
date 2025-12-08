<template>
  <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
      <NuxtLink
        v-for="item in navigationItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'px-5 py-2.5 rounded-xl font-semibold whitespace-nowrap transition-all duration-300',
          item.isActive
            ? activeColor === 'green'
              ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-lg shadow-green-500/30'
              : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30'
            : 'bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200'
        ]"
      >
        {{ item.label }}
      </NuxtLink>
    <NuxtLink
      v-if="showPasswordChange"
      :to="passwordChangePath"
      class="px-5 py-2.5 rounded-xl font-semibold whitespace-nowrap transition-all duration-300 bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200"
    >
      パスワード変更
    </NuxtLink>
    <button
      v-if="showLogout"
      @click="handleLogout"
      class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl font-semibold whitespace-nowrap hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-lg shadow-red-500/30 hover:shadow-xl ml-auto"
    >
      ログアウト
    </button>
  </div>
</template>

<script setup lang="ts">
export interface NavigationItem {
  to: string
  label: string
  isActive?: boolean
}

interface Props {
  navigationItems: NavigationItem[]
  showPasswordChange?: boolean
  passwordChangePath?: string
  activeColor?: 'blue' | 'green'
  showLogout?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showPasswordChange: true,
  passwordChangePath: '/shop/users/password',
  activeColor: 'blue',
  showLogout: true
})

const { handleLogout } = useAuthCheck()
</script>

