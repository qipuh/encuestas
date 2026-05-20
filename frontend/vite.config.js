import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    vue({
      template: {
        compilerOptions: {
          isCustomElement: tag => tag.startsWith('ion-')
        }
      }
    }),
    VitePWA({
      registerType: 'autoUpdate',
      devOptions: { enabled: true },          // SW activo en desarrollo
      includeAssets: ['favicon.ico', 'apple-touch-icon.png'],
      manifest: {
        name: 'Emotix',
        short_name: 'Emotix',
        description: 'Experiencias que se sienten',
        theme_color: '#0f1f3d',
        background_color: '#f4f5f9',
        display: 'standalone',
        start_url: '/',
        orientation: 'portrait',
        icons: [
          { src: '/icons/icon-192x192.png', sizes: '192x192', type: 'image/png' },
          { src: '/icons/icon-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'any maskable' }
        ]
      },
      workbox: {
        navigateFallback: '/index.html',
        navigateFallbackDenylist: [/^\/api/],
        cleanupOutdatedCaches: true,

        // Precachear shell de la app
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],

        runtimeCaching: [
          // Assets estáticos → Cache First
          {
            urlPattern: /\.(js|css|woff2|png|svg|ico)$/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'static-assets',
              expiration: { maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 * 30 }
            }
          },
          // API GETs → NetworkFirst con fallback a caché (10s timeout)
          {
            urlPattern: ({ url }) => url.pathname.startsWith('/api/') && !url.pathname.includes('/login') && !url.pathname.includes('/logout'),
            handler: 'NetworkFirst',
            method: 'GET',
            options: {
              cacheName: 'api-get-cache',
              networkTimeoutSeconds: 8,
              expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 },
              cacheableResponse: { statuses: [200] }
            }
          },
          // Ionicons CDN → StaleWhileRevalidate
          {
            urlPattern: /unpkg\.com\/ionicons/i,
            handler: 'StaleWhileRevalidate',
            options: { cacheName: 'ionicons-cache', expiration: { maxAgeSeconds: 60 * 60 * 24 * 90 } }
          },
          // Google Fonts → CacheFirst
          {
            urlPattern: /fonts\.googleapis\.com|fonts\.gstatic\.com/i,
            handler: 'CacheFirst',
            options: { cacheName: 'google-fonts', expiration: { maxAgeSeconds: 60 * 60 * 24 * 365 } }
          }
        ],


      }
    })
  ],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) }
  },
  server: {
    port: 5173,
    proxy: {
      '/api':     { target: 'http://emotix.test', changeOrigin: true },
      '/storage': { target: 'http://emotix.test', changeOrigin: true },
      '/img':     { target: 'http://emotix.test', changeOrigin: true }
    }
  }
})
