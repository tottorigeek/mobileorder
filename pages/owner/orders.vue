<template>
  <NuxtLayout name="owner" title="注文一覧">
    <OrdersPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import OrdersPage from '~/pages/multi-shop/orders.vue'

definePageMeta({
  layout: 'owner'
})
</script>


