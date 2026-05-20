<template>
  <div class="min-h-screen relative overflow-hidden select-none"
    :style="estado === 'bienvenida' ? 'background:#eef0f7' : 'background:#ffffff'">

    <!-- ── Patrón de fondo (emocion / comentario / gracias) ── -->
    <div v-if="['emocion','comentario','gracias'].includes(estado)"
      class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
      <ion-icon name="happy-outline"         style="position:absolute;top:4%;left:3%;font-size:44px;color:#0f1f3d;opacity:0.06;transform:rotate(-20deg)" />
      <ion-icon name="flash-outline"         style="position:absolute;top:7%;right:8%;font-size:36px;color:#0f1f3d;opacity:0.06;transform:rotate(15deg)" />
      <ion-icon name="chatbubble-outline"    style="position:absolute;top:15%;right:3%;font-size:48px;color:#0f1f3d;opacity:0.06;transform:rotate(-8deg)" />
      <ion-icon name="star-outline"          style="position:absolute;top:22%;left:7%;font-size:34px;color:#0f1f3d;opacity:0.06;transform:rotate(25deg)" />
      <ion-icon name="heart-outline"         style="position:absolute;top:35%;right:6%;font-size:40px;color:#0f1f3d;opacity:0.06;transform:rotate(-12deg)" />
      <ion-icon name="camera-outline"        style="position:absolute;top:45%;left:2%;font-size:38px;color:#0f1f3d;opacity:0.06;transform:rotate(18deg)" />
      <ion-icon name="pencil-outline"        style="position:absolute;top:55%;right:4%;font-size:34px;color:#0f1f3d;opacity:0.06;transform:rotate(-22deg)" />
      <ion-icon name="sad-outline"           style="position:absolute;top:62%;left:6%;font-size:42px;color:#0f1f3d;opacity:0.06;transform:rotate(10deg)" />
      <ion-icon name="pie-chart-outline"     style="position:absolute;top:72%;right:7%;font-size:38px;color:#0f1f3d;opacity:0.06;transform:rotate(-15deg)" />
      <ion-icon name="chatbubbles-outline"   style="position:absolute;top:82%;left:3%;font-size:46px;color:#0f1f3d;opacity:0.06;transform:rotate(6deg)" />
      <ion-icon name="trending-up-outline"   style="position:absolute;top:88%;right:5%;font-size:36px;color:#0f1f3d;opacity:0.06;transform:rotate(-10deg)" />
      <ion-icon name="thumbs-up-outline"     style="position:absolute;top:18%;left:40%;font-size:34px;color:#0f1f3d;opacity:0.05;transform:rotate(20deg)" />
      <ion-icon name="ribbon-outline"        style="position:absolute;top:50%;right:20%;font-size:40px;color:#0f1f3d;opacity:0.05;transform:rotate(-18deg)" />
      <ion-icon name="megaphone-outline"     style="position:absolute;top:70%;left:25%;font-size:44px;color:#0f1f3d;opacity:0.05;transform:rotate(8deg)" />
      <ion-icon name="bar-chart-outline"     style="position:absolute;top:30%;left:15%;font-size:36px;color:#0f1f3d;opacity:0.05;transform:rotate(-25deg)" />
      <ion-icon name="videocam-outline"      style="position:absolute;top:40%;right:15%;font-size:38px;color:#0f1f3d;opacity:0.05;transform:rotate(14deg)" />
      <ion-icon name="bulb-outline"          style="position:absolute;top:92%;left:50%;font-size:34px;color:#0f1f3d;opacity:0.05;transform:rotate(-8deg)" />
    </div>

    <!-- Offline badge -->
    <div v-if="!online" class="fixed top-4 right-4 z-50 bg-yellow-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-lg">
      <ion-icon name="cloud-offline-outline" style="font-size:14px" />
      Sin conexión
    </div>

    <!-- ── Cargando ── -->
    <div v-if="estado === 'cargando'" class="min-h-screen flex flex-col items-center justify-center gap-4">
      <div class="w-12 h-12 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin" />
      <p class="text-gray-400 text-sm font-medium">Cargando encuesta...</p>
    </div>

    <!-- ── Sin encuesta ── -->
    <div v-else-if="estado === 'sin-encuesta'" class="min-h-screen flex flex-col items-center justify-center gap-4 p-8 text-center">
      <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
        <ion-icon name="clipboard-outline" style="font-size:36px;color:#94a3b8" />
      </div>
      <h2 class="text-xl font-black text-[#0f1f3d]">Sin encuesta activa</h2>
      <p class="text-gray-400 text-sm max-w-xs">No hay una encuesta activa configurada para esta fuente.</p>
    </div>

    <!-- ── Bienvenida (kiosk welcome screen) ── -->
    <div v-else-if="estado === 'bienvenida'"
      class="min-h-screen flex flex-col items-center justify-between py-12 px-6">

      <!-- Fuente logo -->
      <div class="flex flex-col items-center">
        <img v-if="encuesta?.fuente?.logo_url"
          :src="encuesta.fuente.logo_url"
          class="h-16 object-contain mb-2"
          :alt="encuesta.fuente.nombre"
        />
        <p v-else class="text-2xl font-black text-[#0f1f3d] tracking-wide uppercase mb-2">
          {{ encuesta?.fuente?.nombre }}
        </p>
      </div>

      <!-- Personaje + saludo -->
      <div class="flex flex-col items-center text-center">
        <!-- Ilustración (coloca /images/character-waving.png en /public/images/) -->
        <div class="w-56 h-56 rounded-full bg-white/70 flex items-center justify-center mb-6 shadow-sm">
          <img v-if="imgWaving" :src="'/images/character-waving.png'"
            class="w-full h-full object-cover rounded-full" alt="Bienvenida"
            @error="imgWaving = false"
          />
          <ion-icon v-else name="hand-left-outline" style="font-size:80px;color:#0f1f3d;opacity:0.3" />
        </div>

        <h1 class="text-3xl font-black text-[#0f1f3d] mb-2">
          ¡Hola, {{ encuesta?.fuente?.nombre ?? 'Bienvenido' }}!
        </h1>
        <p class="text-gray-500 text-base">Bienvenido de vuelta a Emotix</p>
      </div>

      <!-- Card resumen encuestas -->
      <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
        <!-- Icono emotix chat -->
        <div class="shrink-0">
          <svg width="60" height="60" viewBox="0 0 60 60" fill="none">
            <path d="M8 10 Q8 4 14 4 H46 Q52 4 52 10 V34 Q52 40 46 40 H34 L22 52 V40 H14 Q8 40 8 34 Z" stroke="#0f1f3d" stroke-width="2.5" fill="none"/>
            <circle cx="22" cy="22" r="7" fill="#e5e7eb"/>
            <circle cx="38" cy="22" r="7" fill="#e5e7eb"/>
            <path d="M17 22 Q18 26 22 26 Q26 26 27 22" stroke="#9ca3af" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M33 22 Q34 19 38 19 Q42 19 43 22 Q42 26 38 26 Q34 26 33 22" stroke="#2ecc71" stroke-width="1.5" fill="none" stroke-linecap="round"/>
          </svg>
        </div>
        <div>
          <p class="text-xl font-black text-[#0f1f3d]">{{ encuestasActivas }} Encuesta{{ encuestasActivas !== 1 ? 's' : '' }}</p>
          <p class="text-sm text-gray-500 mt-0.5">{{ encuestasActivas }} Encuesta{{ encuestasActivas !== 1 ? 's' : '' }} habilitada{{ encuestasActivas !== 1 ? 's' : '' }}</p>
          <p class="text-sm text-gray-400">{{ encuestasPausadas }} Encuesta{{ encuestasPausadas !== 1 ? 's' : '' }} deshabilitada{{ encuestasPausadas !== 1 ? 's' : '' }}</p>
        </div>
      </div>

      <!-- Botón lanzar -->
      <div class="w-full max-w-sm space-y-4">
        <button
          @click="estado = 'emocion'"
          class="w-full py-4 bg-[#2ecc71] text-white text-lg font-bold rounded-2xl hover:bg-[#27ae60] active:scale-95 transition-all shadow-md"
        >
          Lanzar Encuesta
        </button>

        <!-- Footer emotix -->
        <p class="text-center text-gray-400 text-sm font-semibold tracking-widest">emotix</p>
      </div>
    </div>

    <!-- ── Selección de emoción ── -->
    <div v-else-if="estado === 'emocion'"
      class="relative z-10 min-h-screen flex flex-col items-center justify-between py-16 px-6">

      <!-- Pregunta -->
      <div class="text-center max-w-2xl">
        <h1 class="text-4xl sm:text-5xl font-black text-[#0f1f3d] leading-tight">
          {{ encuesta?.pregunta_principal || '¿Cómo fue tu experiencia hoy?' }}
        </h1>
      </div>

      <!-- Emociones -->
      <div class="flex items-center justify-center gap-10 sm:gap-16">
        <button v-for="em in EMOCIONES" :key="em.valor"
          @click="seleccionarEmocion(em)"
          class="flex flex-col items-center gap-3 group active:scale-90 transition-transform"
        >
          <div class="w-32 h-32 sm:w-36 sm:h-36 rounded-full flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform"
            :style="{ background: em.color }">
            <!-- Carita SVG -->
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" v-if="em.valor === 'positiva'">
              <circle cx="22" cy="25" r="4" fill="white"/>
              <circle cx="42" cy="25" r="4" fill="white"/>
              <path d="M18 40 Q32 52 46 40" stroke="white" stroke-width="4" stroke-linecap="round" fill="none"/>
            </svg>
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" v-else-if="em.valor === 'neutral'">
              <circle cx="22" cy="25" r="4" fill="white"/>
              <circle cx="42" cy="25" r="4" fill="white"/>
              <line x1="18" y1="44" x2="46" y2="44" stroke="white" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" v-else>
              <circle cx="22" cy="25" r="4" fill="white"/>
              <circle cx="42" cy="25" r="4" fill="white"/>
              <path d="M18 50 Q32 38 46 50" stroke="white" stroke-width="4" stroke-linecap="round" fill="none"/>
            </svg>
          </div>
        </button>
      </div>

      <!-- Footer logos -->
      <div class="flex items-center gap-4">
        <span class="text-[#0f1f3d] font-black text-lg tracking-wide">emotix</span>
        <span class="text-gray-300 text-xl">|</span>
        <img v-if="encuesta?.fuente?.logo_url"
          :src="encuesta.fuente.logo_url" class="h-8 object-contain" :alt="encuesta?.fuente?.nombre" />
        <span v-else class="text-gray-500 font-semibold text-sm">{{ encuesta?.fuente?.nombre }}</span>
      </div>
    </div>

    <!-- ── Comentario ── -->
    <div v-else-if="estado === 'comentario'"
      class="relative z-10 min-h-screen flex flex-col items-center justify-between py-16 px-6">

      <!-- Título -->
      <div class="text-center max-w-2xl">
        <h1 class="text-4xl sm:text-5xl font-black text-[#0f1f3d] leading-tight">
          ¿Cuéntanos, que pasó?
        </h1>
      </div>

      <!-- Textarea -->
      <div class="w-full max-w-2xl">
        <textarea
          v-model="comentarioTexto"
          rows="7"
          placeholder="Escribe aquí tus comentarios..."
          class="w-full px-6 py-5 text-base text-gray-700 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:border-[#2ecc71] resize-none shadow-sm placeholder-gray-300"
          maxlength="1000"
          style="font-size:1.05rem"
        />
        <p v-if="errorEnvio" class="mt-2 text-center text-red-500 text-sm">{{ errorEnvio }}</p>
      </div>

      <!-- Botones -->
      <div class="flex items-center gap-5">
        <button
          @click="enviarRespuesta(true)"
          :disabled="enviando"
          class="px-12 py-3.5 bg-[#2ecc71] text-white font-bold text-base rounded-xl hover:bg-[#27ae60] active:scale-95 transition-all disabled:opacity-60 flex items-center gap-2"
        >
          <div v-if="enviando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Enviar
        </button>
        <button
          @click="enviarRespuesta(false)"
          :disabled="enviando"
          class="px-12 py-3.5 bg-gray-400 text-white font-bold text-base rounded-xl hover:bg-gray-500 active:scale-95 transition-all disabled:opacity-60"
        >
          No Gracias
        </button>
      </div>

      <!-- Footer logos -->
      <div class="flex items-center gap-4">
        <span class="text-[#0f1f3d] font-black text-lg tracking-wide">emotix</span>
        <span class="text-gray-300 text-xl">|</span>
        <img v-if="encuesta?.fuente?.logo_url"
          :src="encuesta.fuente.logo_url" class="h-8 object-contain" :alt="encuesta?.fuente?.nombre" />
        <span v-else class="text-gray-500 font-semibold text-sm">{{ encuesta?.fuente?.nombre }}</span>
      </div>
    </div>

    <!-- ── Gracias ── -->
    <div v-else-if="estado === 'gracias'"
      class="relative z-10 min-h-screen flex flex-col items-center justify-between py-16 px-6">

      <div />

      <div class="text-center max-w-lg">
        <!-- Icono emotix chat -->
        <div class="flex justify-center mb-8">
          <svg width="100" height="90" viewBox="0 0 100 90" fill="none">
            <path d="M10 14 Q10 4 20 4 H80 Q90 4 90 14 V56 Q90 66 80 66 H58 L38 82 V66 H20 Q10 66 10 56 Z"
              stroke="#c5cbd3" stroke-width="3" fill="none" stroke-linejoin="round"/>
            <circle cx="36" cy="35" r="8" fill="#d1d5db"/>
            <circle cx="64" cy="35" r="8" fill="#d1d5db"/>
            <path d="M28 35 Q30 40 36 40 Q42 40 44 35" stroke="#9ca3af" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M56 32 Q58 28 64 28 Q70 28 72 32 Q70 38 64 38 Q58 38 56 32" stroke="#9ca3af" stroke-width="2" fill="none" stroke-linecap="round"/>
          </svg>
        </div>

        <h1 class="text-4xl sm:text-5xl font-black text-[#0f1f3d] leading-tight mb-4">
          ¡Gracias por<br/>tus comentarios!
        </h1>
        <p class="text-lg font-bold text-[#2ecc71]">
          Tus sugerencias nos ayudan<br/>a seguir mejorando.
        </p>

        <!-- Countdown sutil -->
        <div class="flex items-center justify-center gap-2 text-gray-300 text-sm mt-8">
          <div class="w-5 h-5 relative">
            <svg class="w-5 h-5 -rotate-90" viewBox="0 0 20 20">
              <circle cx="10" cy="10" r="8" fill="none" stroke="#e5e7eb" stroke-width="2" />
              <circle cx="10" cy="10" r="8" fill="none" stroke="#2ecc71" stroke-width="2"
                :stroke-dasharray="`${countdownProgress * 50.27} 50.27`" stroke-linecap="round"/>
            </svg>
          </div>
          Volviendo en {{ countdown }}s
        </div>
      </div>

      <!-- Footer logos -->
      <div class="flex items-center gap-4">
        <span class="text-[#0f1f3d] font-black text-lg tracking-wide">emotix</span>
        <span class="text-gray-300 text-xl">|</span>
        <img v-if="encuesta?.fuente?.logo_url"
          :src="encuesta.fuente.logo_url" class="h-8 object-contain" :alt="encuesta?.fuente?.nombre" />
        <span v-else class="text-gray-500 font-semibold text-sm">{{ encuesta?.fuente?.nombre }}</span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const online = ref(navigator.onLine)

const EMOCIONES = [
  { valor: 'positiva', label: 'Bien',    color: '#44b86b' },
  { valor: 'neutral',  label: 'Regular', color: '#9ba4ab' },
  { valor: 'negativa', label: 'Mal',     color: '#e8705e' },
]

const estado            = ref('cargando')
const encuesta          = ref(null)
const encuestasActivas  = ref(1)
const encuestasPausadas = ref(0)
const emocionSeleccionada = ref(null)
const comentarioTexto   = ref('')
const enviando          = ref(false)
const errorEnvio        = ref('')
const imgWaving         = ref(true)

const countdown         = ref(5)
const countdownProgress = ref(1)
let countdownTimer      = null

function seleccionarEmocion(em) {
  emocionSeleccionada.value = em
  comentarioTexto.value = ''
  errorEnvio.value = ''
  estado.value = 'comentario'
}

async function enviarRespuesta(conComentario = true) {
  if (!emocionSeleccionada.value || !encuesta.value) return
  enviando.value  = true
  errorEnvio.value = ''
  try {
    await api.post('/respuestas', {
      encuesta_id: encuesta.value.id,
      emocion:     emocionSeleccionada.value.valor,
      comentario:  conComentario && comentarioTexto.value.trim() ? comentarioTexto.value.trim() : null,
      fecha_hora:  new Date().toISOString(),
    })
    estado.value = 'gracias'
    iniciarCountdown()
  } catch (err) {
    errorEnvio.value = err?.response?.data?.message || 'No se pudo enviar. Inténtalo de nuevo.'
  } finally {
    enviando.value = false
  }
}

function iniciarCountdown() {
  countdown.value = 5
  countdownProgress.value = 1
  const total = 5000
  const start = Date.now()
  countdownTimer = setInterval(() => {
    const elapsed   = Date.now() - start
    const remaining = Math.max(0, total - elapsed)
    countdown.value         = Math.ceil(remaining / 1000)
    countdownProgress.value = remaining / total
    if (remaining <= 0) {
      clearInterval(countdownTimer)
      reiniciar()
    }
  }, 100)
}

function reiniciar() {
  emocionSeleccionada.value = null
  comentarioTexto.value     = ''
  errorEnvio.value          = ''
  estado.value              = 'emocion'
}

async function cargar() {
  try {
    const id = route.query.id
    const url = id ? `/encuestas/${id}` : '/encuesta-activa-para-fuente'
    const { data } = await api.get(url)
    encuesta.value          = data
    encuestasActivas.value  = data.fuente?.encuestas_activas  ?? 1
    encuestasPausadas.value = data.fuente?.encuestas_pausadas ?? 0
    estado.value            = 'bienvenida'
  } catch {
    estado.value = 'sin-encuesta'
  }
}

const onlineHandler  = () => { online.value = true  }
const offlineHandler = () => { online.value = false }

onMounted(() => {
  cargar()
  window.addEventListener('online',  onlineHandler)
  window.addEventListener('offline', offlineHandler)
})
onUnmounted(() => {
  clearInterval(countdownTimer)
  window.removeEventListener('online',  onlineHandler)
  window.removeEventListener('offline', offlineHandler)
})
</script>
