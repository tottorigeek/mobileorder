<template>
  <NuxtLayout name="owner" title="店舗選択">
    <ShopSelect />
  </NuxtLayout>
</template>

<script setup lang="ts">
import ShopSelect from '~/pages/shop-select.vue'

definePageMeta({
  layout: 'owner'
})
</script>


