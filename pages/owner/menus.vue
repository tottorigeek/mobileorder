<template>
  <NuxtLayout name="owner" title="メニュー管理">
    <MenusPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import MenusPage from '~/pages/multi-shop/menus.vue'

definePageMeta({
  layout: 'owner'
})
</script>


