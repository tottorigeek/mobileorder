<template>
  <div class="bg-white p-4 rounded-lg shadow">
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-2">
          <h3 class="text-lg font-semibold">{{ staff.name }}</h3>
          <span
            v-if="showCurrentUserBadge && isCurrentUser"
            class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium"
          >
            自分
          </span>
          <span :class="getRoleBadgeClass(staff.role)">
            {{ getRoleLabel(staff.role) }}
          </span>
          <span
            v-if="showShopName && staff.shopId"
            class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs"
          >
            {{ getShopName(staff.shopId) }}
          </span>
          <span
            v-if="!staff.isActive"
            class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm"
          >
            無効
          </span>
        </div>
        <p class="text-sm text-gray-600 mb-1">ユーザー名: {{ staff.username }}</p>
        <p v-if="staff.email" class="text-sm text-gray-600 mb-1">メール: {{ staff.email }}</p>
        <p v-if="staff.lastLoginAt" class="text-xs text-gray-500">
          最終ログイン: {{ formatDate(staff.lastLoginAt) }}
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <slot name="actions">
          <NuxtLink
            v-if="showEditButton"
            :to="editPath"
            class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors touch-target text-sm"
          >
            編集
          </NuxtLink>
          <NuxtLink
            v-if="showPasswordButton"
            :to="passwordPath"
            class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors touch-target text-sm"
          >
            パスワード変更
          </NuxtLink>
          <button
            v-if="showDeleteButton && canDelete"
            @click="$emit('delete', staff)"
            class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors touch-target text-sm"
          >
            削除
          </button>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { User, Shop } from '~/types'

interface Props {
  staff: User
  shops?: Shop[]
  showShopName?: boolean
  showCurrentUserBadge?: boolean
  showEditButton?: boolean
  showPasswordButton?: boolean
  showDeleteButton?: boolean
  editPath?: string
  passwordPath?: string
  canDelete?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showShopName: false,
  showCurrentUserBadge: false,
  showEditButton: true,
  showPasswordButton: true,
  showDeleteButton: true,
  editPath: '',
  passwordPath: '',
  canDelete: false
})

defineEmits<{
  delete: [staff: User]
}>()

const { getRoleLabel, getRoleBadgeClass, formatDateDetailed } = useAdminUtils()
const authStore = useAuthStore()

const isCurrentUser = computed(() => {
  return authStore.user?.id === props.staff.id
})

const getShopName = (shopId: string): string => {
  if (!props.shops) return '不明'
  const shop = props.shops.find(s => s.id === shopId)
  return shop?.name || '不明'
}

const formatDate = (dateString: string): string => {
  return formatDateDetailed(dateString)
}
</script>



