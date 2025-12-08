<template>
  <div>
    <NuxtPage />
  </div>
</template>

<script setup>
// アプリケーションのルートコンポーネント
// 強制的にlightテーマを適用

let observer: MutationObserver | null = null

onMounted(() => {
  // HTML要素にlightクラスを強制
  const html = document.documentElement
  html.classList.remove('dark')
  html.classList.add('light')
  html.setAttribute('data-color-mode-forced', 'light')
  
  // カラーモードのストレージを上書き
  if (typeof window !== 'undefined') {
    localStorage.setItem('nuxt-color-mode', 'light')
  }
  
  // カラーモードの変更を監視して強制的にlightに戻す
  observer = new MutationObserver(() => {
    const htmlElement = document.documentElement
    if (!htmlElement.classList.contains('light') || htmlElement.classList.contains('dark')) {
      htmlElement.classList.remove('dark')
      htmlElement.classList.add('light')
      htmlElement.setAttribute('data-color-mode-forced', 'light')
    }
  })
  
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class', 'data-color-mode-forced']
  })
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})
</script>

