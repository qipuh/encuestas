import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  queueAdd, queueGetPending, queueMarkDone, queueMarkError,
  queueCountPending, queueClear, cacheSet, cacheGetStale
} from '@/services/db'

export const useOfflineStore = defineStore('offline', () => {
  const isOnline = ref(navigator.onLine)
  const isSyncing = ref(false)
  const pendingCount = ref(0)
  const lastSync = ref(null)
  const syncErrors = ref([])

  const hasPending = computed(() => pendingCount.value > 0)
  const statusLabel = computed(() => {
    if (!isOnline.value) return 'Sin conexión'
    if (isSyncing.value) return 'Sincronizando...'
    if (hasPending.value) return `${pendingCount.value} pendientes`
    return 'Sincronizado'
  })

  // Escuchar eventos de red
  function initNetworkListeners() {
    window.addEventListener('online', () => {
      isOnline.value = true
      triggerSync()
    })
    window.addEventListener('offline', () => {
      isOnline.value = false
    })
  }

  // Encolar una mutación para cuando haya conexión
  async function enqueue(userId, token, method, url, data, meta = {}) {
    await queueAdd(userId, token, method, url, data, meta)
    pendingCount.value = await queueCountPending(userId)
  }

  // Actualizar contador de pendientes
  async function refreshCount(userId) {
    if (!userId) return
    pendingCount.value = await queueCountPending(userId)
  }

  // Sincronizar cola del usuario actual
  async function triggerSync(userId, token) {
    if (!isOnline.value || isSyncing.value || !userId) return
    isSyncing.value = true
    syncErrors.value = []

    try {
      const pending = await queueGetPending(userId)
      if (pending.length === 0) { isSyncing.value = false; return }

      for (const item of pending) {
        try {
          const res = await fetch(item.url, {
            method: item.method,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${item.token}`
            },
            body: JSON.stringify(item.data)
          })

          if (res.ok) {
            await queueMarkDone(item.id)
            pendingCount.value = Math.max(0, pendingCount.value - 1)
          } else if (res.status >= 400 && res.status < 500) {
            // Error del cliente (validación, etc.) — marcar como error permanente
            const err = await res.json().catch(() => ({}))
            await queueMarkError(item.id, err.message || `HTTP ${res.status}`)
            syncErrors.value.push({ url: item.url, error: err.message })
          }
          // 5xx → dejar como pending para reintentar
        } catch {
          // Red caída durante sync → parar
          break
        }
      }

      await queueClear(userId)
      lastSync.value = new Date()
    } finally {
      isSyncing.value = false
      pendingCount.value = await queueCountPending(userId)
    }
  }

  return {
    isOnline, isSyncing, pendingCount, hasPending, lastSync, syncErrors, statusLabel,
    initNetworkListeners, enqueue, refreshCount, triggerSync
  }
})
