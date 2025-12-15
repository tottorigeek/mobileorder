<template>
  <NuxtLayout name="owner" title="テーブル管理">
    <TablesPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import TablesPage from '~/pages/multi-shop/tables.vue'

definePageMeta({
  layout: 'owner'
})
</script>


