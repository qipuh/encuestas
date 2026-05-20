import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null

export function createEcho(token) {
  if (echoInstance) {
    echoInstance.disconnect()
  }

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key:         import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:      import.meta.env.VITE_REVERB_HOST ?? 'localhost',
    wsPort:      import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:     import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS:    false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/broadcasting/auth',
    auth: {
      headers: { Authorization: `Bearer ${token}` }
    },
  })

  return echoInstance
}

export function getEcho() {
  return echoInstance
}

export function destroyEcho() {
  if (echoInstance) {
    echoInstance.disconnect()
    echoInstance = null
  }
}
