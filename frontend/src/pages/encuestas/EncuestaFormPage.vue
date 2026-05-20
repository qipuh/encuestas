<template>
  <div>
    <!-- Breadcrumb + título -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
        <RouterLink to="/app/encuestas" class="hover:text-[#0f1f3d] transition-colors">Encuestas</RouterLink>
        <ion-icon name="chevron-forward-outline" style="font-size:13px" />
        <ion-icon :name="isEdit ? 'pencil-outline' : 'sparkles-outline'" style="font-size:15px;color:#2ecc71" />
        <span class="text-[#0f1f3d] font-semibold">{{ isEdit ? 'Editar encuesta' : 'Crear nueva encuesta' }}</span>
      </div>
      <p class="text-sm text-gray-400">
        {{ isEdit
          ? 'Mejora las preguntas, concéntrate en los detalles y reformula la experiencia.'
          : 'Diseña experiencias que te ayuden a escuchar, entender y mejorar cada día.' }}
      </p>
    </div>

    <div class="flex gap-6 items-start">
      <!-- ═══ Columna principal ═══ -->
      <div class="flex-1 min-w-0 space-y-4">

        <!-- Sección 1: Información general -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex gap-8">
            <!-- Step info -->
            <div class="w-40 shrink-0 text-center pt-1">
              <div class="w-10 h-10 rounded-full bg-[#2ecc71] flex items-center justify-center mx-auto mb-2 shadow-sm">
                <span class="text-white font-black text-base">1</span>
              </div>
              <p class="font-bold text-[#0f1f3d] text-sm">Información general</p>
              <p class="text-xs text-gray-400 mt-1 leading-relaxed">Define los datos básicos de tu encuesta</p>
            </div>
            <!-- Contenido -->
            <div class="flex-1 space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre de encuesta</label>
                <input v-model="form.nombre" type="text"
                  :placeholder="isEdit ? 'Nivel de experiencia' : 'Ej: Experiencia en atención - 2026'"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>

              <div class="flex gap-3 items-end">
                <!-- Fuente dropdown -->
                <div class="flex-1">
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fuente</label>
                  <div class="relative">
                    <button type="button" @click.stop="dropFuente = !dropFuente"
                      class="w-full flex items-center gap-2 px-3 py-2.5 text-sm bg-white border border-gray-200 rounded-xl shadow-sm hover:border-gray-300 transition-colors text-left"
                      :class="dropFuente ? 'border-[#2ecc71]' : ''">
                      <ion-icon name="home-outline" style="font-size:15px;color:#9ba4ab;flex-shrink:0" />
                      <span class="flex-1 truncate" :class="form.fuente_id ? 'text-gray-700' : 'text-gray-400'">
                        {{ fuenteLabel }}
                      </span>
                      <ion-icon name="chevron-down-outline" style="font-size:13px;color:#9ba4ab;flex-shrink:0" />
                    </button>
                    <div v-if="dropFuente"
                      class="absolute left-0 top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg z-20 w-full py-1 max-h-48 overflow-y-auto">
                      <button v-for="f in fuentes" :key="f.id"
                        @click.stop="form.fuente_id = f.id; dropFuente = false"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
                        :class="form.fuente_id === f.id ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
                        {{ f.nombre }}
                      </button>
                      <p v-if="!fuentes.length" class="px-4 py-2 text-xs text-gray-400">Sin fuentes disponibles</p>
                    </div>
                  </div>
                </div>

                <!-- Categoría dropdown -->
                <div class="flex-1">
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Categoría</label>
                  <div class="relative">
                    <button type="button" @click.stop="dropCategoria = !dropCategoria"
                      class="w-full flex items-center gap-2 px-3 py-2.5 text-sm bg-white border border-gray-200 rounded-xl shadow-sm hover:border-gray-300 transition-colors text-left"
                      :class="dropCategoria ? 'border-[#2ecc71]' : ''">
                      <ion-icon name="star-outline" style="font-size:15px;color:#9ba4ab;flex-shrink:0" />
                      <span class="flex-1 truncate" :class="form.categoria_id ? 'text-gray-700' : 'text-gray-400'">
                        {{ categoriaLabel }}
                      </span>
                      <ion-icon name="chevron-down-outline" style="font-size:13px;color:#9ba4ab;flex-shrink:0" />
                    </button>
                    <div v-if="dropCategoria"
                      class="absolute left-0 top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg z-20 w-full py-1 max-h-48 overflow-y-auto">
                      <button @click.stop="form.categoria_id = ''; dropCategoria = false"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
                        :class="!form.categoria_id ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
                        Sin categoría
                      </button>
                      <button v-for="c in categorias" :key="c.id"
                        @click.stop="form.categoria_id = c.id; dropCategoria = false"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
                        :class="form.categoria_id === c.id ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
                        {{ c.nombre }}
                      </button>
                      <p v-if="!categorias.length" class="px-4 py-2 text-xs text-gray-400">Sin categorías</p>
                    </div>
                  </div>
                </div>

                <!-- Botón + nueva categoría -->
                <div class="shrink-0 pb-0.5">
                  <button @click="addCatVisible = !addCatVisible"
                    class="w-9 h-9 rounded-xl bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center shadow-sm transition-colors">
                    <ion-icon name="add-outline" style="font-size:20px" />
                  </button>
                </div>
              </div>

              <!-- Mini form nueva categoría -->
              <div v-if="addCatVisible" class="flex gap-2 items-center">
                <input v-model="nuevaCategoria" type="text" placeholder="Nombre de la nueva categoría..."
                  class="flex-1 px-3 py-2 text-sm bg-white border border-blue-300 rounded-xl focus:outline-none focus:border-blue-500 shadow-sm"
                  @keyup.enter="crearCategoria" />
                <button @click="crearCategoria" :disabled="!nuevaCategoria.trim()"
                  class="px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-xl hover:bg-blue-600 disabled:opacity-50 transition-colors">
                  Agregar
                </button>
                <button @click="addCatVisible = false; nuevaCategoria = ''"
                  class="px-3 py-2 border border-gray-200 text-gray-500 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección 2: Pregunta principal -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex gap-8">
            <!-- Step info -->
            <div class="w-40 shrink-0 text-center pt-1">
              <div class="w-10 h-10 rounded-full bg-orange-400 flex items-center justify-center mx-auto mb-2 shadow-sm">
                <span class="text-white font-black text-base">2</span>
              </div>
              <p class="font-bold text-[#0f1f3d] text-sm">Pregunta principal</p>
              <p class="text-xs text-gray-400 mt-1 leading-relaxed">Es la primera pregunta que verá tu cliente</p>
            </div>
            <!-- Contenido -->
            <div class="flex-1 space-y-3">
              <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300">
                  <ion-icon name="chatbubble-ellipses-outline" style="font-size:18px" />
                </div>
                <input v-model="form.pregunta_principal" type="text" maxlength="120"
                  :placeholder="isEdit ? '¿Tuviste una buena experiencia hoy?' : '¿Cómo fué tu experiencia hoy?'"
                  class="w-full pl-10 pr-20 py-3 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-mono">
                  {{ form.pregunta_principal.length }} / 120
                </span>
              </div>
              <!-- Tip -->
              <div class="flex items-start gap-2">
                <ion-icon name="bulb-outline" style="font-size:16px;color:#2ecc71;flex-shrink:0;margin-top:1px" />
                <p class="text-xs text-gray-400">
                  <span class="text-[#2ecc71] font-bold">Consejo:</span>
                  Las preguntas claras y simples generan mejores respuestas.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección 3: Pregunta según la emoción -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex gap-8">
            <!-- Step info -->
            <div class="w-40 shrink-0 text-center pt-1">
              <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mx-auto mb-2 shadow-sm">
                <span class="text-white font-black text-base">3</span>
              </div>
              <p class="font-bold text-[#0f1f3d] text-sm">Pregunta según la emoción</p>
              <p class="text-xs text-gray-400 mt-1 leading-relaxed">Personaliza las preguntas que verán tus clientes según su emoción.</p>
            </div>
            <!-- Contenido -->
            <div class="flex-1 divide-y divide-gray-50">

              <!-- Positiva -->
              <div class="flex items-center gap-3 py-3 first:pt-0">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="#6b7280"/>
                    <circle cx="15" cy="10" r="1.2" fill="#6b7280"/>
                    <path d="M8 14.5 Q12 18 16 14.5" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                  </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 w-16 shrink-0">Positiva</span>
                <button type="button" @click="form.pregunta_positiva_activa = !form.pregunta_positiva_activa"
                  class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                  :class="form.pregunta_positiva_activa ? 'bg-[#2ecc71]' : 'bg-gray-200'">
                  <ion-icon name="checkmark-outline" :style="`font-size:13px;color:${form.pregunta_positiva_activa ? 'white' : '#9ca3af'}`" />
                </button>
                <input v-model="form.pregunta_positiva" type="text"
                  placeholder="¿Qué es lo que más te gustó?"
                  :disabled="!form.pregunta_positiva_activa"
                  class="flex-1 px-3 py-2 text-sm border rounded-xl focus:outline-none transition-all"
                  :class="form.pregunta_positiva_activa
                    ? 'bg-white border-gray-200 focus:border-[#2ecc71] text-gray-700'
                    : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'" />
              </div>

              <!-- Neutral -->
              <div class="flex items-center gap-3 py-3">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="#6b7280"/>
                    <circle cx="15" cy="10" r="1.2" fill="#6b7280"/>
                    <line x1="8.5" y1="15" x2="15.5" y2="15" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 w-16 shrink-0">Neutral</span>
                <button type="button" @click="form.pregunta_neutral_activa = !form.pregunta_neutral_activa"
                  class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                  :class="form.pregunta_neutral_activa ? 'bg-[#2ecc71]' : 'bg-gray-200'">
                  <ion-icon name="checkmark-outline" :style="`font-size:13px;color:${form.pregunta_neutral_activa ? 'white' : '#9ca3af'}`" />
                </button>
                <input v-model="form.pregunta_neutral" type="text"
                  placeholder="¿En qué podríamos mejorar?"
                  :disabled="!form.pregunta_neutral_activa"
                  class="flex-1 px-3 py-2 text-sm border rounded-xl focus:outline-none transition-all"
                  :class="form.pregunta_neutral_activa
                    ? 'bg-white border-gray-200 focus:border-[#2ecc71] text-gray-700'
                    : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'" />
              </div>

              <!-- Negativa -->
              <div class="flex items-center gap-3 py-3 last:pb-0">
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="#6b7280"/>
                    <circle cx="15" cy="10" r="1.2" fill="#6b7280"/>
                    <path d="M8 16.5 Q12 13 16 16.5" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                  </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 w-16 shrink-0">Negativa</span>
                <button type="button" @click="form.pregunta_negativa_activa = !form.pregunta_negativa_activa"
                  class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-colors"
                  :class="form.pregunta_negativa_activa ? 'bg-[#2ecc71]' : 'bg-gray-200'">
                  <ion-icon name="checkmark-outline" :style="`font-size:13px;color:${form.pregunta_negativa_activa ? 'white' : '#9ca3af'}`" />
                </button>
                <input v-model="form.pregunta_negativa" type="text"
                  placeholder="¿Qué es lo que menos te gustó?"
                  :disabled="!form.pregunta_negativa_activa"
                  class="flex-1 px-3 py-2 text-sm border rounded-xl focus:outline-none transition-all"
                  :class="form.pregunta_negativa_activa
                    ? 'bg-white border-gray-200 focus:border-[#2ecc71] text-gray-700'
                    : 'bg-gray-50 border-gray-100 text-gray-300 cursor-not-allowed'" />
              </div>

            </div>
          </div>
        </div>

      </div>

      <!-- ═══ Sidebar ═══ -->
      <div class="w-56 shrink-0 sticky top-6 space-y-2">
        <!-- Guardar / Crear -->
        <button @click="guardar" :disabled="guardando || !form.nombre || !form.fuente_id || !form.pregunta_principal"
          class="w-full py-3 text-white text-sm font-bold rounded-xl disabled:opacity-50 transition-colors flex items-center justify-center gap-2 shadow-sm"
          :class="isEdit ? 'bg-[#2ecc71] hover:bg-[#27ae60]' : 'bg-[#2ecc71] hover:bg-[#27ae60]'">
          <div v-if="guardando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          <ion-icon v-else :name="isEdit ? 'save-outline' : 'paper-plane-outline'" style="font-size:17px" />
          {{ guardando ? 'Guardando...' : (isEdit ? 'Guardar' : 'Crear encuesta') }}
        </button>

        <!-- Vista previa -->
        <button @click="vistaPrevia"
          class="w-full py-3 bg-white border border-[#2ecc71] text-[#2ecc71] text-sm font-semibold rounded-xl hover:bg-green-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <ion-icon name="eye-outline" style="font-size:17px" />
          Vista previa
        </button>

        <!-- Regresar -->
        <RouterLink to="/app/encuestas"
          class="w-full py-3 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <ion-icon name="arrow-back-outline" style="font-size:16px" />
          Regresar
        </RouterLink>

        <p v-if="error" class="text-red-500 text-xs bg-red-50 border border-red-100 rounded-xl p-3 mt-2">{{ error }}</p>
      </div>
    </div>

    <!-- Backdrop dropdowns -->
    <div v-if="dropFuente || dropCategoria" class="fixed inset-0 z-10" @click="dropFuente = false; dropCategoria = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const fuentes = ref([])
const categorias = ref([])
const guardando = ref(false)
const error = ref('')
const dropFuente = ref(false)
const dropCategoria = ref(false)
const addCatVisible = ref(false)
const nuevaCategoria = ref('')

const form = ref({
  nombre: '',
  fuente_id: '',
  categoria_id: '',
  estado: 'activa',
  pregunta_principal: '',
  pregunta_positiva: '',
  pregunta_positiva_activa: false,
  pregunta_neutral: '',
  pregunta_neutral_activa: true,
  pregunta_negativa: '',
  pregunta_negativa_activa: true,
})

const fuenteLabel = computed(() =>
  fuentes.value.find(f => f.id === form.value.fuente_id)?.nombre ?? 'Seleccionar fuente'
)
const categoriaLabel = computed(() =>
  categorias.value.find(c => c.id === form.value.categoria_id)?.nombre ?? 'Seleccionar categoría'
)

async function cargarDatos() {
  const [f, c] = await Promise.all([
    api.get('/fuentes').catch(() => ({ data: [] })),
    api.get('/categorias').catch(() => ({ data: [] })),
  ])
  fuentes.value = f.data?.data ?? f.data
  categorias.value = c.data?.data ?? c.data

  if (isEdit.value) {
    const { data } = await api.get(`/encuestas/${route.params.id}`)
    form.value = {
      nombre: data.nombre ?? '',
      fuente_id: data.fuente_id ?? '',
      categoria_id: data.categoria_id ?? '',
      estado: data.estado ?? 'activa',
      pregunta_principal: data.pregunta_principal ?? '',
      pregunta_positiva: data.pregunta_positiva ?? '',
      pregunta_positiva_activa: !!data.pregunta_positiva,
      pregunta_neutral: data.pregunta_neutral ?? '',
      pregunta_neutral_activa: !!data.pregunta_neutral,
      pregunta_negativa: data.pregunta_negativa ?? '',
      pregunta_negativa_activa: !!data.pregunta_negativa,
    }
  }
}

async function crearCategoria() {
  if (!nuevaCategoria.value.trim()) return
  try {
    const { data } = await api.post('/categorias', { nombre: nuevaCategoria.value })
    categorias.value.push(data)
    form.value.categoria_id = data.id
    nuevaCategoria.value = ''
    addCatVisible.value = false
  } catch {}
}

function vistaPrevia() {
  if (isEdit.value) {
    router.push({ path: '/encuesta-activa', query: { id: route.params.id } })
  }
}

async function guardar() {
  guardando.value = true
  error.value = ''
  try {
    const payload = {
      nombre: form.value.nombre,
      fuente_id: form.value.fuente_id,
      categoria_id: form.value.categoria_id || null,
      estado: form.value.estado,
      pregunta_principal: form.value.pregunta_principal,
      pregunta_positiva: form.value.pregunta_positiva_activa ? (form.value.pregunta_positiva || null) : null,
      pregunta_neutral: form.value.pregunta_neutral_activa ? (form.value.pregunta_neutral || null) : null,
      pregunta_negativa: form.value.pregunta_negativa_activa ? (form.value.pregunta_negativa || null) : null,
    }
    if (isEdit.value) {
      await api.put(`/encuestas/${route.params.id}`, payload)
    } else {
      await api.post('/encuestas', payload)
    }
    router.push('/app/encuestas')
  } catch (err) {
    error.value = err?.response?.data?.message
      || Object.values(err?.response?.data?.errors ?? {})[0]?.[0]
      || 'Error al guardar la encuesta.'
  } finally {
    guardando.value = false
  }
}

onMounted(cargarDatos)
</script>
