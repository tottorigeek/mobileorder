<template>
  <NuxtLayout name="owner" title="ダッシュボード">
    <DashboardPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import DashboardPage from '~/pages/multi-shop/dashboard.vue'

definePageMeta({
  layout: 'owner'
})
</script>


