<template>
  <div class="flex flex-col items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md">
      <h1 class="text-3xl font-bold text-center mb-8 text-gray-900">
        モバイルオーダーシステム
      </h1>

      <div class="space-y-4">
        <NuxtLink
          to="/customer"
          class="block w-full p-6 bg-blue-600 text-white rounded-lg text-center text-xl font-semibold hover:bg-blue-700 transition-colors touch-target"
        >
          お客様用
        </NuxtLink>

        <NuxtLink
          to="/staff/login"
          class="block w-full p-6 bg-purple-600 text-white rounded-lg text-center text-xl font-semibold hover:bg-purple-700 transition-colors touch-target"
        >
          店舗スタッフ用
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})
</script>

