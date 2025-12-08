<template>
  <div class="flex gap-3 overflow-x-auto pb-2">
      <NuxtLink
        v-for="item in navigationItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors',
          item.isActive
            ? activeColor === 'green'
              ? 'bg-green-600 text-white'
              : 'bg-blue-600 text-white'
            : 'bg-white text-gray-700 hover:bg-gray-100'
        ]"
      >
        {{ item.label }}
      </NuxtLink>
    <NuxtLink
      v-if="showPasswordChange"
      :to="passwordChangePath"
      :class="[
        'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors',
        'bg-white text-gray-700 hover:bg-gray-100'
      ]"
    >
      パスワード変更
    </NuxtLink>
    <button
      v-if="showLogout"
      @click="handleLogout"
      class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium whitespace-nowrap hover:bg-red-200 ml-auto"
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

