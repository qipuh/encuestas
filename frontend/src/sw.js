import { cleanupOutdatedCaches, precacheAndRoute } from 'workbox-precaching'
import { registerRoute, NavigationRoute } from 'workbox-routing'
import { NetworkFirst, CacheFirst, StaleWhileRevalidate } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'
import { CacheableResponsePlugin } from 'workbox-cacheable-response'

// Precachear shell de la app
cleanupOutdatedCaches()
precacheAndRoute(self.__WB_MANIFEST)

self.skipWaiting()
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()))

// ── Rutas de caché ──────────────────────────────────────

// Assets estáticos → CacheFirst
registerRoute(
  ({ request }) => ['style', 'script', 'font'].includes(request.destination),
  new CacheFirst({
    cacheName: 'static-assets',
    plugins: [
      new ExpirationPlugin({ maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 * 30 }),
      new CacheableResponsePlugin({ statuses: [0, 200] }),
    ],
  })
)

// Imágenes → CacheFirst
registerRoute(
  ({ request }) => request.destination === 'image',
  new CacheFirst({
    cacheName: 'images',
    plugins: [
      new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 60 * 60 * 24 * 30 }),
      new CacheableResponsePlugin({ statuses: [0, 200] }),
    ],
  })
)

// API GETs → NetworkFirst con fallback a caché
registerRoute(
  ({ url }) =>
    url.pathname.startsWith('/api/') &&
    !url.pathname.includes('/login') &&
    !url.pathname.includes('/logout'),
  new NetworkFirst({
    cacheName: 'api-get-cache',
    networkTimeoutSeconds: 8,
    plugins: [
      new ExpirationPlugin({ maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 }),
      new CacheableResponsePlugin({ statuses: [200] }),
    ],
  }),
  'GET'
)

// Ionicons CDN → StaleWhileRevalidate
registerRoute(
  ({ url }) => url.hostname.includes('unpkg.com') && url.pathname.includes('ionicons'),
  new StaleWhileRevalidate({
    cacheName: 'ionicons-cache',
    plugins: [new ExpirationPlugin({ maxAgeSeconds: 60 * 60 * 24 * 90 })],
  })
)

// ── Web Push ────────────────────────────────────────────

self.addEventListener('push', event => {
  let data = {}
  try { data = event.data?.json() ?? {} } catch { data = { title: 'Emotix', body: event.data?.text() } }

  const title   = data.title || 'Emotix'
  const options = {
    body:    data.body  || 'Tienes un nuevo comentario',
    icon:    '/icons/icon-192x192.png',
    badge:   '/icons/icon-192x192.png',
    vibrate: [200, 100, 200],
    tag:     data.tag   || 'emotix-notification',
    renotify: true,
    data:    { url: data.url || '/app/comentarios' },
    actions: [
      { action: 'view',    title: 'Ver ahora' },
      { action: 'dismiss', title: 'Ignorar'   },
    ],
  }

  event.waitUntil(self.registration.showNotification(title, options))
})

self.addEventListener('notificationclick', event => {
  event.notification.close()
  if (event.action === 'dismiss') return

  const url = event.notification.data?.url || '/app/comentarios'

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
      const existing = clients.find(c => c.url.includes(url) || c.url.includes('/app/'))
      if (existing) return existing.focus()
      return self.clients.openWindow(url)
    })
  )
})
