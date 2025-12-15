<template></template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

onMounted(() => {
  navigateTo('/owner/tables', { replace: true })
})
</script>


