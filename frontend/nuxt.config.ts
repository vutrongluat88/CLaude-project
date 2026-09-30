import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // Admin dashboard behind a login: rendering client-side keeps Sanctum's
  // cookie session in one place (the browser) instead of proxying it through SSR.
  ssr: false,

  css: ['~/assets/css/main.css'],
  vite: { plugins: [tailwindcss()] },

  // Bind to all interfaces so the dev server is reachable from outside the container.
  devServer: { host: '0.0.0.0', port: 3000 },

  app: {
    head: {
      title: 'User Management',
      meta: [{ name: 'viewport', content: 'width=device-width, initial-scale=1' }],
    },
  },

  runtimeConfig: {
    public: {
      // Overridden by NUXT_PUBLIC_API_BASE (see compose.yaml / .env).
      apiBase: 'http://localhost:8000/api',
    },
  },
})
