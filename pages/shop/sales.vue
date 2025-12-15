<template></template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

onMounted(() => {
  navigateTo('/owner/dashboard', { replace: true })
})
</script>


