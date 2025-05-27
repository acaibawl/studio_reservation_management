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
    },
  },
});
