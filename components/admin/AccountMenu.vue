<template>
  <div class="relative account-menu-container">
    <!-- アカウント情報ボタン -->
    <button
      ref="menuButton"
      @click="toggleMenu"
      class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors touch-target"
      :class="{ 'bg-gray-100': isMenuOpen }"
    >
      <div class="user-avatar w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
        <span class="user-initials text-white text-sm font-semibold">{{ userInitials }}</span>
      </div>
      <div class="user-info hidden sm:block text-left">
        <p class="user-name text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
        <p class="user-role text-xs text-gray-500">{{ roleLabel }}</p>
      </div>
      <svg 
        class="w-4 h-4 text-gray-500 transition-transform"
        :class="{ 'rotate-180': isMenuOpen }"
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- ドロップダウンメニュー -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="isMenuOpen"
          ref="menuDropdown"
          class="fixed bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-[9999]"
          :style="menuStyle"
        >
        <!-- ユーザー情報セクション -->
        <div class="px-4 py-3 border-b border-gray-200">
          <p class="text-sm font-semibold text-gray-900">{{ authStore.user?.name }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ authStore.user?.email || authStore.user?.username }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ roleLabel }}</p>
        </div>

        <!-- メニュー項目 -->
        <div class="py-1">
          <NuxtLink
            :to="passwordChangePath"
            @click="closeMenu"
            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            パスワード変更
          </NuxtLink>
          <button
            @click="handleLogoutClick"
            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
          >
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            ログアウト
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useAuthCheck } from '~/composables/useAuthCheck'

interface Props {
  passwordChangePath?: string
}

const props = withDefaults(defineProps<Props>(), {
  passwordChangePath: '/staff/password'
})

const authStore = useAuthStore()
const { handleLogout } = useAuthCheck()

const isMenuOpen = ref(false)
const menuButton = ref<HTMLElement | null>(null)
const menuDropdown = ref<HTMLElement | null>(null)

const menuStyle = computed(() => {
  if (!menuButton.value || !isMenuOpen.value) {
    return {}
  }
  
  const rect = menuButton.value.getBoundingClientRect()
  const menuWidth = 224 // w-56 = 14rem = 224px
  const menuHeight = 200 // おおよその高さ
  const spacing = 8 // mt-2 = 0.5rem = 8px
  
  // 画面の右端からの距離を計算
  const right = window.innerWidth - rect.right
  const top = rect.bottom + spacing
  
  // スマホでは右端に寄せるが、画面外に出ないように調整
  const maxRight = Math.max(16, right) // 最小16pxのマージン
  const adjustedRight = Math.min(maxRight, window.innerWidth - menuWidth - 16)
  
  return {
    top: `${top}px`,
    right: `${adjustedRight}px`,
    width: `${Math.min(menuWidth, window.innerWidth - 32)}px`,
    maxWidth: 'calc(100vw - 2rem)'
  }
})

const userInitials = computed(() => {
  if (!authStore.user?.name) return '?'
  const name = authStore.user.name
  const firstChar = name.charAt(0)
  if (firstChar.match(/[a-zA-Z]/)) {
    return name.substring(0, 2).toUpperCase()
  }
  return firstChar
})

const roleLabel = computed(() => {
  const labels: Record<string, string> = {
    owner: 'オーナー',
    manager: '管理者',
    staff: 'スタッフ',
    company: '会社管理者'
  }
  return labels[authStore.user?.role || ''] || authStore.user?.role || ''
})

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const handleLogoutClick = async () => {
  closeMenu()
  await handleLogout()
}

// クリックアウトサイド処理
const handleClickOutside = (event: MouseEvent | TouchEvent) => {
  const target = event.target as HTMLElement
  if (
    menuButton.value && 
    menuDropdown.value &&
    !menuButton.value.contains(target) &&
    !menuDropdown.value.contains(target)
  ) {
    closeMenu()
  }
}

watch(isMenuOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      // タッチイベントとクリックイベントの両方を監視
      document.addEventListener('click', handleClickOutside, true)
      document.addEventListener('touchstart', handleClickOutside, true)
    })
  } else {
    document.removeEventListener('click', handleClickOutside, true)
    document.removeEventListener('touchstart', handleClickOutside, true)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
  document.removeEventListener('touchstart', handleClickOutside, true)
})
</script>

