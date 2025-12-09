<template>
  <div class="w-full overflow-x-auto pb-2 scrollbar-hide -mx-4 sm:mx-0 px-4 sm:px-0">
    <div class="flex gap-2 min-w-max">
      <NuxtLink
        v-for="item in navigationItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 rounded-xl text-sm sm:text-base font-semibold whitespace-nowrap transition-all duration-300 flex-shrink-0 touch-target',
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
        class="px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 rounded-xl text-sm sm:text-base font-semibold whitespace-nowrap transition-all duration-300 bg-white text-gray-700 hover:bg-gray-50 hover:shadow-md border border-gray-200 flex-shrink-0 touch-target"
      >
        パスワード変更
      </NuxtLink>
      <button
        v-if="showLogout"
        @click="handleLogout"
        class="px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl text-sm sm:text-base font-semibold whitespace-nowrap hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-lg shadow-red-500/30 hover:shadow-xl flex-shrink-0 touch-target"
      >
        ログアウト
      </button>
    </div>
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

