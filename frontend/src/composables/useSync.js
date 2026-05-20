import { onMounted, onUnmounted, watch } from 'vue'
import { useOfflineStore } from '@/stores/offline'
import { useAuthStore } from '@/stores/auth'

export function useSync() {
  const offline = useOfflineStore()
  const auth = useAuthStore()

  function onQueuedEvent(e) {
    offline.refreshCount(e.detail.uid)
  }

  async function attemptSync() {
    if (offline.isOnline && auth.userId) {
      await offline.triggerSync(auth.userId, auth.token)
    }
  }

  onMounted(() => {
    offline.initNetworkListeners()
    offline.refreshCount(auth.userId)
    window.addEventListener('offline-queued', onQueuedEvent)

    // Sync al montar si hay conexión
    if (offline.isOnline) attemptSync()

    // Sync periódico cada 2 minutos si hay pendientes
    const interval = setInterval(() => {
      if (offline.isOnline && offline.hasPending) attemptSync()
    }, 120_000)

    onUnmounted(() => {
      window.removeEventListener('offline-queued', onQueuedEvent)
      clearInterval(interval)
    })
  })

  // Sync automático al volver online
  watch(() => offline.isOnline, (online) => {
    if (online) attemptSync()
  })

  return { offline, attemptSync }
}
