<template>
  <!-- ── Baner de instalación (no en login: ahí lo muestra LoginPage) ── -->
  <Transition name="pwa-slide">
    <div v-if="showBanner" class="fixed bottom-0 inset-x-0 z-[200] px-3 pb-5 pointer-events-none">
      <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 pointer-events-auto max-w-sm mx-auto overflow-hidden">

        <div class="bg-[#0f1f3d] px-4 py-3 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#2ecc71]/20 flex items-center justify-center shrink-0">
            <ion-icon name="happy-outline" style="font-size:20px;color:#2ecc71" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-white text-sm leading-tight">¡Instala Emotix!</p>
            <p class="text-xs text-gray-400 mt-0.5">Accede más rápido desde tu pantalla de inicio</p>
          </div>
          <button @click="dismiss" class="p-1 text-gray-500 hover:text-gray-300 shrink-0 transition-colors">
            <ion-icon name="close-outline" style="font-size:20px" />
          </button>
        </div>

        <div class="px-4 py-3 flex items-center gap-3">
          <div class="flex-1 text-xs text-gray-500">
            Funciona sin conexión, recibe notificaciones y carga al instante.
          </div>
          <button v-if="canInstall" @click="doInstall"
            class="px-4 py-2 bg-[#2ecc71] text-white text-xs font-bold rounded-xl shrink-0 hover:bg-[#27ae60] transition-colors flex items-center gap-1.5">
            <ion-icon name="download-outline" style="font-size:14px" />
            Instalar
          </button>
          <button v-else-if="isIos" @click="showIosHelp = true"
            class="px-4 py-2 bg-[#0f1f3d] text-white text-xs font-bold rounded-xl shrink-0 flex items-center gap-1.5">
            <ion-icon name="information-circle-outline" style="font-size:14px" />
            Cómo instalar
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- ── Notificación de nueva versión ── -->
  <Transition name="pwa-slide">
    <div v-if="needRefresh" class="fixed bottom-0 inset-x-0 z-[200] px-3 pb-5 pointer-events-none">
      <div class="bg-[#0f1f3d] rounded-2xl shadow-2xl pointer-events-auto max-w-sm mx-auto flex items-center gap-3 px-4 py-3">
        <ion-icon name="refresh-circle-outline" style="font-size:24px;color:#2ecc71;flex-shrink:0" />
        <div class="flex-1 min-w-0">
          <p class="text-white text-sm font-bold leading-tight">Nueva versión disponible</p>
          <p class="text-gray-400 text-xs mt-0.5">Actualiza para obtener las últimas mejoras</p>
        </div>
        <button @click="updateServiceWorker()"
          class="px-3 py-1.5 bg-[#2ecc71] text-white text-xs font-bold rounded-xl shrink-0 hover:bg-[#27ae60] transition-colors">
          Actualizar
        </button>
        <button @click="needRefresh = false"
          class="p-1 text-gray-500 hover:text-gray-300 shrink-0 transition-colors">
          <ion-icon name="close-outline" style="font-size:18px" />
        </button>
      </div>
    </div>
  </Transition>

  <!-- ── Modal iOS (global: lo puede abrir el login o el baner) ── -->
  <Transition name="fade">
    <div v-if="showIosHelp" class="fixed inset-0 z-[300] flex items-end justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showIosHelp = false" />
      <div class="relative bg-white rounded-t-3xl w-full max-w-md px-7 pt-7 pb-10 shadow-2xl">

        <div class="flex items-center justify-between mb-1">
          <h3 class="font-black text-[#0f1f3d] text-xl">Instalar Emotix</h3>
          <button @click="showIosHelp = false"
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
            Solo funciona desde <strong>Safari</strong>. Si usas Chrome u otro navegador, ábrelo primero en Safari.
          </p>
        </div>

        <button @click="showIosHelp = false; dismiss()"
          class="w-full mt-5 py-3.5 bg-[#0f1f3d] text-white font-bold rounded-2xl text-sm">
          Entendido
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useRegisterSW } from 'virtual:pwa-register/vue'
import { usePwaInstall } from '@/composables/usePwaInstall'

const route = useRoute()
const { isIos, installed, canInstall, showIosHelp } = usePwaInstall()

const { needRefresh, updateServiceWorker } = useRegisterSW({
  onRegistered(r) { r && setInterval(() => r.update(), 60 * 60 * 1000) },
})

// ── Baner de instalación ────────────────────────────────
const dismissed = ref(!!localStorage.getItem('pwa_dismissed'))
const ready     = ref(false)

// No mostrar en el login: LoginPage tiene su propio aviso
const onLogin = computed(() => route.path === '/' || route.path === '/login')

const showBanner = computed(() =>
  ready.value &&
  !onLogin.value &&
  !installed.value &&
  !dismissed.value &&
  (canInstall.value || isIos)
)

function dismiss() {
  dismissed.value = true
  localStorage.setItem('pwa_dismissed', '1')
}

async function doInstall() {
  const { promptInstall } = usePwaInstall()
  const ok = await promptInstall()
  if (ok) dismiss()
}

onMounted(() => {
  setTimeout(() => { ready.value = true }, 2500)
})
</script>

<style scoped>
.pwa-slide-enter-active,
.pwa-slide-leave-active {
  transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.3s ease;
}
.pwa-slide-enter-from,
.pwa-slide-leave-to {
  transform: translateY(120%);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
