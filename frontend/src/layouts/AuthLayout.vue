<template>
  <div class="min-h-screen flex flex-col">
    <slot />

    <!-- PWA Install Banner -->
    <Transition name="pwa-slide">
      <div v-if="showBanner" class="fixed bottom-0 inset-x-0 z-50 px-4 pb-6 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-2xl p-4 flex items-center gap-3 pointer-events-auto max-w-md mx-auto border border-gray-100">
          <div class="w-11 h-11 rounded-xl bg-[#0f1f3d] flex items-center justify-center shrink-0">
            <ion-icon name="happy-outline" style="font-size:22px;color:#2ecc71" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-[#0f1f3d] text-sm leading-tight">Instala Emotix</p>
            <p class="text-xs text-gray-400 mt-0.5">Accede más rápido desde tu pantalla</p>
          </div>
          <button v-if="canInstallAndroid" @click="installAndroid"
            class="px-4 py-2 bg-[#2ecc71] text-white text-xs font-bold rounded-xl shrink-0 hover:bg-[#27ae60] transition-colors">
            Instalar
          </button>
          <button v-else-if="isIos" @click="showIosModal = true"
            class="px-4 py-2 bg-[#0f1f3d] text-white text-xs font-bold rounded-xl shrink-0">
            Cómo instalar
          </button>
          <button @click="dismiss" class="p-1 text-gray-300 shrink-0">
            <ion-icon name="close-outline" style="font-size:20px" />
          </button>
        </div>
      </div>
    </Transition>

    <!-- iOS Step-by-step Modal -->
    <Transition name="fade">
      <div v-if="showIosModal" class="fixed inset-0 z-[60] flex items-end justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showIosModal = false" />
        <div class="relative bg-white rounded-t-3xl w-full max-w-md px-7 pt-7 pb-10 shadow-2xl">

          <div class="flex items-center justify-between mb-1">
            <h3 class="font-black text-[#0f1f3d] text-xl">Instalar Emotix</h3>
            <button @click="showIosModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100">
              <ion-icon name="close-outline" style="font-size:16px;color:#6b7280" />
            </button>
          </div>
          <p class="text-gray-400 text-sm mb-6">Sigue estos pasos en Safari para añadir la app a tu pantalla de inicio.</p>

          <div class="space-y-5">
            <div class="flex items-start gap-4">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                <span class="text-blue-600 font-bold text-sm">1</span>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed pt-1">
                Toca el ícono
                <span class="inline-flex items-center gap-0.5 mx-1 px-2 py-0.5 bg-gray-100 rounded-lg">
                  <ion-icon name="share-outline" style="font-size:14px;color:#007AFF" />
                  <span class="text-xs text-[#007AFF] font-medium">Compartir</span>
                </span>
                en la barra inferior de Safari
              </p>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                <span class="text-blue-600 font-bold text-sm">2</span>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed pt-1">
                Desplázate y toca
                <span class="inline-flex items-center gap-0.5 mx-1 px-2 py-0.5 bg-gray-100 rounded-lg">
                  <ion-icon name="add-square-outline" style="font-size:14px;color:#6b7280" />
                  <span class="text-xs font-medium text-gray-700">Añadir a inicio</span>
                </span>
              </p>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                <span class="text-blue-600 font-bold text-sm">3</span>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed pt-1">
                Toca <strong class="text-[#0f1f3d]">"Añadir"</strong> en la esquina superior derecha para confirmar
              </p>
            </div>
          </div>

          <div class="mt-5 p-3 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-2">
            <ion-icon name="information-circle-outline" style="font-size:16px;color:#d97706;flex-shrink:0;margin-top:1px" />
            <p class="text-xs text-amber-700 leading-relaxed">
              Este proceso solo funciona en <strong>Safari</strong>. Si usas Chrome u otro navegador, ábrelo primero en Safari.
            </p>
          </div>

          <button @click="showIosModal = false; dismiss()"
            class="w-full mt-5 py-3.5 bg-[#0f1f3d] text-white font-bold rounded-2xl text-sm">
            Entendido
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const showBanner    = ref(false)
const showIosModal  = ref(false)
const canInstallAndroid = ref(false)
let deferredPrompt  = null

const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent.toLowerCase())
const isInStandalone = window.matchMedia('(display-mode: standalone)').matches
  || !!navigator.standalone

function dismiss() {
  showBanner.value = false
  localStorage.setItem('pwa_dismissed', '1')
}

async function installAndroid() {
  if (!deferredPrompt) return
  deferredPrompt.prompt()
  const { outcome } = await deferredPrompt.userChoice
  if (outcome === 'accepted') dismiss()
  deferredPrompt = null
  canInstallAndroid.value = false
}

function onBeforeInstallPrompt(e) {
  e.preventDefault()
  deferredPrompt = e
  canInstallAndroid.value = true
  if (!localStorage.getItem('pwa_dismissed')) {
    setTimeout(() => { showBanner.value = true }, 3000)
  }
}

onMounted(() => {
  if (isInStandalone || localStorage.getItem('pwa_dismissed')) return
  window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt)
  if (isIos) setTimeout(() => { showBanner.value = true }, 3000)
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt)
})
</script>

<style scoped>
.pwa-slide-enter-active,
.pwa-slide-leave-active {
  transition: transform 0.35s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.35s ease;
}
.pwa-slide-enter-from,
.pwa-slide-leave-to {
  transform: translateY(110%);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
