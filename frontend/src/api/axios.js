import axios from 'axios'
import { cacheSet, cacheGetStale, queueAdd } from '@/services/db'

// Rutas GET que se cachean para modo offline (minutos de TTL)
const CACHEABLE = {
  '/encuestas':              60,
  '/encuestas-activa-para-fuente': 120,
  '/comentarios':            30,
  '/reportes/dashboard':     30,
  '/fuentes':                1440,
  '/categorias':             1440,
  '/roles':                  1440,
  '/usuarios':               60,
  '/reportes/satisfaccion-diaria': 30,
  '/configuracion/general':  120,
  '/configuracion/alertas':  120,
}

// Métodos mutadores que se encolan offline
const QUEUEABLE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE']

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' }
})

function getUserId() { return localStorage.getItem('userId') }
function getToken()  { return localStorage.getItem('token') }

function getCacheKey(url, params) {
  const p = params ? '?' + new URLSearchParams(params).toString() : ''
  return url + p
}

function matchesCacheable(url) {
  return Object.entries(CACHEABLE).find(([pattern]) => url.startsWith(pattern))
}

// ── Request interceptor ──────────────────────────────────────
api.interceptors.request.use(async config => {
  const token = getToken()
  if (token) config.headers.Authorization = `Bearer ${token}`

  // Si estamos offline y es un GET → intentar desde caché
  if (!navigator.onLine && config.method?.toUpperCase() === 'GET') {
    const uid = getUserId()
    const match = matchesCacheable(config.url)
    if (uid && match) {
      const cacheKey = getCacheKey(config.url, config.params)
      const cached = await cacheGetStale(uid, cacheKey)
      if (cached !== null) {
        // Simular respuesta exitosa desde caché
        config._fromCache = true
        config._cachedData = cached
      }
    }
  }

  return config
})

// ── Response interceptor ─────────────────────────────────────
api.interceptors.response.use(
  async res => {
    // Guardar en caché si la ruta lo permite
    const uid = getUserId()
    const match = matchesCacheable(res.config.url)
    if (uid && match && res.config.method?.toUpperCase() === 'GET') {
      const ttl = match[1]
      const cacheKey = getCacheKey(res.config.url, res.config.params)
      await cacheSet(uid, cacheKey, res.data, ttl)
    }
    return res
  },
  async err => {
    const config = err.config
    const status = err.response?.status

    // 401 → logout
    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('userId')
      window.location.href = '/login'
      return Promise.reject(err)
    }

    // Sin conexión o error de red en mutación → encolar
    if (!navigator.onLine && config && QUEUEABLE_METHODS.includes(config.method?.toUpperCase())) {
      const uid = getUserId()
      const token = getToken()
      if (uid && token) {
        const offlineId = `offline_${Date.now()}_${Math.random().toString(36).slice(2)}`
        await queueAdd(
          parseInt(uid), token,
          config.method.toUpperCase(),
          config.baseURL + config.url,
          typeof config.data === 'string' ? JSON.parse(config.data) : config.data,
          { offlineId, url: config.url }
        )

        // Actualizar contador en store (sin circular dependency)
        window.dispatchEvent(new CustomEvent('offline-queued', { detail: { uid } }))

        // Devolver respuesta "optimista" para que el UI no rompa
        return Promise.resolve({
          data: {
            _offline: true,
            offline_id: offlineId,
            mensaje: 'Guardado offline. Se enviará al recuperar conexión.'
          },
          status: 202,
          config
        })
      }
    }

    return Promise.reject(err)
  }
)

// Adaptador para peticiones interceptadas desde caché
const originalRequest = api.request.bind(api)
api.request = async function(config) {
  if (config._fromCache) {
    return { data: config._cachedData, status: 200, config, _fromCache: true }
  }
  return originalRequest(config)
}

export default api
