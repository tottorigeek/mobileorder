<template></template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

onMounted(() => {
  navigateTo('/owner/menus', { replace: true })
})
</script>


