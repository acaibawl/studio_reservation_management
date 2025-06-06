// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  css: ['modern-css-reset'],
  modules: [
    '@nuxt/eslint',
    'vuetify-nuxt-module',
    '@pinia/nuxt',
  ],
  vite: {
    server: {
      allowedHosts: ['front.local'],
    },
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: '',
      baseUrl: '',
    },
  },
  app: {
    head: {
      htmlAttrs: {
        lang: 'ja',
      },
      link: [
        {
          rel: 'preconnect',
          href: 'https://fonts.googleapis.com',
        },
        {
          rel: 'preconnect',
          href: 'https://fonts.gstatic.com',
        },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Mochiy+Pop+One&family=Noto+Sans+JP:wght@100..900&family=Philosopher&display=swap',
        },
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
        { rel: 'icon', type: 'image/png', sizes: '192x192', href: '/android-touch-icon.png' },
      ],
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
      meta: [
        { name: 'description', content: 'スタジオの予約を管理するためのシステムです。' },
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'スタジオ予約管理システム' },
        { property: 'og:locale', content: 'ja_JP' },
        { property: 'og:title', content: 'スタジオ予約管理システム' },
        { property: 'og:description', content: 'スタジオの予約を管理するためのシステムです。' },
        { property: 'og:image', content: '/ogp.jpg' },
        { property: 'og:url', content: process.env.NUXT_PUBLIC_BASE_URL },
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:title', content: 'スタジオ予約管理システム' },
        { name: 'twitter:description', content: 'スタジオの予約を管理するためのシステムです。' },
        { name: 'twitter:image', content: '/ogp.jpg' },
      ],
    },
  },
});
