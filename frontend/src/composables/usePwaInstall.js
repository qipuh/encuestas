import { ref } from 'vue'

const ua = navigator.userAgent.toLowerCase()
const isIos = /iphone|ipad|ipod/.test(ua) && !window.MSStream
const isInStandalone =
  window.matchMedia('(display-mode: standalone)').matches || !!navigator.standalone

// Estado compartido (singleton)
const deferredPrompt = ref(null)
const canInstall     = ref(false)   // prompt nativo Android/Chrome disponible
const installed      = ref(isInStandalone)
const showIosHelp    = ref(false)   // abre el modal de instrucciones iOS

let inited = false
function init() {
  if (inited) return
  inited = true

  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault()
    deferredPrompt.value = e
    canInstall.value = true
  })

  window.addEventListener('appinstalled', () => {
    deferredPrompt.value = null
    canInstall.value = false
    installed.value = true
  })
}

async function promptInstall() {
  if (!deferredPrompt.value) return false
  deferredPrompt.value.prompt()
  const { outcome } = await deferredPrompt.value.userChoice
  deferredPrompt.value = null
  canInstall.value = false
  if (outcome === 'accepted') installed.value = true
  return outcome === 'accepted'
}

export function usePwaInstall() {
  init()
  return { isIos, isInStandalone, installed, canInstall, showIosHelp, promptInstall }
}
