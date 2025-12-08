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

  app: {
    head: {
      title: 'モバイルオーダーシステム',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: '番号入力式モバイルオーダーシステム' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      ]
    },
    // GitHub Pages用のベースURL設定
    baseURL: process.env.NUXT_PUBLIC_BASE_URL || '/',
    buildAssetsDir: '/_nuxt/'
  },

  // Vercel用の設定（SSRを有効化）
  // ssr: true がデフォルト（VercelではSSRが推奨）

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE || process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:3000/api'
    }
  }
})

