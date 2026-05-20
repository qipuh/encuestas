import { ref, onMounted, onUnmounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { createEcho, destroyEcho } from '@/plugins/echo'
import { useAuthStore } from '@/stores/auth'

const pendingCount = ref(0)

export function useNotificaciones() {
  const toast  = useToast()
  const auth   = useAuthStore()
  let channel  = null

  function incrementar() {
    pendingCount.value++
  }

  function resetear() {
    pendingCount.value = 0
  }

  async function pedirPermisoNotificacion() {
    if (!('Notification' in window)) return false
    if (Notification.permission === 'granted') return true
    if (Notification.permission === 'denied') return false
    const perm = await Notification.requestPermission()
    return perm === 'granted'
  }

  function mostrarNotificacionNativa(comentario) {
    if (Notification.permission !== 'granted') return

    const emoji = { positiva: '😊', neutral: '😐', negativa: '😞' }[comentario.emocion] ?? '💬'
    const notif = new Notification(`${emoji} Nuevo comentario — ${comentario.fuente}`, {
      body: comentario.comentario || 'Sin texto',
      icon: '/icons/icon-192x192.png',
      badge: '/icons/icon-192x192.png',
      tag: `comentario-${comentario.id}`,
      renotify: true,
    })

    notif.onclick = () => {
      window.focus()
      window.location.hash = `/app/comentarios/${comentario.id}`
      notif.close()
    }
  }

  function mostrarToast(comentario) {
    const emoji   = { positiva: '😊', neutral: '😐', negativa: '😞' }[comentario.emocion] ?? '💬'
    const severity = { positiva: 'success', neutral: 'warn', negativa: 'error' }[comentario.emocion] ?? 'info'

    toast.add({
      severity,
      summary: `${emoji} Nuevo comentario`,
      detail: `${comentario.fuente} — ${comentario.comentario || 'Sin texto'}`,
      life: 6000,
    })
  }

  function suscribir() {
    if (!auth.token || !auth.userId) return

    const echo = createEcho(auth.token)

    channel = echo.private(`notificaciones.${auth.userId}`)
      .listen('.nuevo-comentario', (data) => {
        incrementar()
        mostrarToast(data)
        mostrarNotificacionNativa(data)
      })
  }

  function desuscribir() {
    destroyEcho()
    channel = null
  }

  onMounted(async () => {
    await pedirPermisoNotificacion()
    suscribir()
  })

  onUnmounted(() => {
    desuscribir()
  })

  return { pendingCount, resetear, pedirPermisoNotificacion }
}
