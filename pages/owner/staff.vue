<template>
  <NuxtLayout name="owner" title="スタッフ管理">
    <StaffPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import StaffPage from '~/pages/multi-shop/staff.vue'

definePageMeta({
  layout: 'owner'
})
</script>


