// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-01-01',
  devtools: { enabled: true },
  
  modules: [
    '@nuxt/ui',
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],

  css: ['~/assets/css/main.css'],

  // カラーモードを強制的にlightに固定
  colorMode: {
    preference: 'light',
    fallback: 'light',
    classSuffix: '',
    storageKey: 'nuxt-color-mode'
  },
  
  // Nuxt UIのカラーモード設定
  ui: {
    global: true,
    safelistColors: ['primary', 'red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose']
  },

  app: {
    head: {
      title: 'Radish - ラディッシュ',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Radish（ラディッシュ）- 番号入力式モバイルオーダーシステム' },
        { name: 'color-scheme', content: 'light' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    },
    // GitHub Pages用のベースURL設定
    baseURL: process.env.NUXT_PUBLIC_BASE_URL || '/',
    buildAssetsDir: '/_nuxt/'
  },

  // SSRを有効化（/visitor配下のみクライアントレンダリングに限定）
  ssr: true,

  routeRules: {
    '/visitor/**': { ssr: false }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE
    }
  }
})

