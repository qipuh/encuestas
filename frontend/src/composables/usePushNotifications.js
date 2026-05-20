import { ref } from 'vue'
import api from '@/api/axios'

const isSupported = 'serviceWorker' in navigator && 'PushManager' in window
const isSubscribed = ref(false)
const isLoading = ref(false)

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
  const raw = atob(base64)
  return Uint8Array.from([...raw].map(c => c.charCodeAt(0)))
}

async function getVapidKey() {
  const { data } = await api.get('/push/vapid-key')
  return data.public_key
}

async function getCurrentSubscription() {
  if (!isSupported) return null
  const reg = await navigator.serviceWorker.ready
  return reg.pushManager.getSubscription()
}

async function subscribe() {
  if (!isSupported || isLoading.value) return
  isLoading.value = true
  try {
    const permission = await Notification.requestPermission()
    if (permission !== 'granted') return

    const vapidKey = await getVapidKey()
    const reg = await navigator.serviceWorker.ready
    const sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(vapidKey),
    })

    const json = sub.toJSON()
    await api.post('/push-subscription', {
      endpoint: json.endpoint,
      keys: { auth: json.keys.auth, p256dh: json.keys.p256dh },
    })

    isSubscribed.value = true
  } catch (err) {
    console.error('Push subscribe error:', err)
  } finally {
    isLoading.value = false
  }
}

async function unsubscribe() {
  if (!isSupported || isLoading.value) return
  isLoading.value = true
  try {
    const sub = await getCurrentSubscription()
    if (!sub) return
    await api.delete('/push-subscription', { data: { endpoint: sub.endpoint } })
    await sub.unsubscribe()
    isSubscribed.value = false
  } catch (err) {
    console.error('Push unsubscribe error:', err)
  } finally {
    isLoading.value = false
  }
}

async function syncSubscriptionState() {
  const sub = await getCurrentSubscription()
  isSubscribed.value = !!sub
}

export function usePushNotifications() {
  return { isSupported, isSubscribed, isLoading, subscribe, unsubscribe, syncSubscriptionState }
}
