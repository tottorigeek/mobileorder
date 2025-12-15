<template></template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

onMounted(() => {
  navigateTo('/owner/staff', { replace: true })
})
</script>


