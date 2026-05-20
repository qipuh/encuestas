<template>
  <div>
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
      <h1 class="text-2xl font-black text-[#0f1f3d]">Comentarios</h1>
      <div class="flex flex-wrap items-center gap-2">
        <!-- Filtro emoción -->
        <div class="relative">
          <button @click.stop="dropEmocion = !dropEmocion"
            class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-[#0f1f3d] shadow-sm hover:bg-gray-50 transition-colors">
            <ion-icon name="happy-outline" style="font-size:16px;color:#6b7280" />
            {{ emocionLabel }}
            <ion-icon name="chevron-down-outline" style="font-size:13px;color:#6b7280" />
          </button>
          <div v-if="dropEmocion" class="absolute right-0 mt-1 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-[160px] py-1">
            <button v-for="op in opcionesEmocion" :key="op.value" @click.stop="filtros.emocion = op.value; dropEmocion = false; cargar()"
              class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
              :class="filtros.emocion === op.value ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
              {{ op.label }}
            </button>
          </div>
        </div>
        <!-- Filtro fuente -->
        <div class="relative">
          <button @click.stop="dropFuente = !dropFuente"
            class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-[#0f1f3d] shadow-sm hover:bg-gray-50 transition-colors">
            <ion-icon name="home-outline" style="font-size:16px;color:#6b7280" />
            {{ fuenteLabel }}
            <ion-icon name="chevron-down-outline" style="font-size:13px;color:#6b7280" />
          </button>
          <div v-if="dropFuente" class="absolute right-0 mt-1 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-[180px] py-1">
            <button @click.stop="filtros.fuente_id = ''; dropFuente = false; cargar()"
              class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
              :class="filtros.fuente_id === '' ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
              Todas las fuentes
            </button>
            <button v-for="f in fuentes" :key="f.id" @click.stop="filtros.fuente_id = f.id; dropFuente = false; cargar()"
              class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
              :class="filtros.fuente_id === f.id ? 'font-semibold text-[#2ecc71]' : 'text-gray-700'">
              {{ f.nombre }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Card principal -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <!-- Card header -->
      <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-bold text-[#0f1f3d]">Centro de comentarios</h2>
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <ion-icon name="search-outline" style="font-size:14px" />
            </span>
            <input v-model="filtros.search" type="text" placeholder="Buscar..."
              class="pl-8 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71] w-48"
              @input="debouncedCargar" />
          </div>
          <!-- Filtros fecha -->
          <input v-model="filtros.fecha_desde" type="date" @change="cargar"
            class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
          <input v-model="filtros.fecha_hasta" type="date" @change="cargar"
            class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
          <button class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
            <ion-icon name="options-outline" style="font-size:18px;color:#6b7280" />
          </button>
          <button @click="exportar"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-[#0f1f3d] text-white text-xs font-semibold rounded-lg hover:bg-[#162d52] transition-colors">
            <ion-icon name="download-outline" style="font-size:13px" />
            Exportar a Excel
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="cargando" class="flex items-center justify-center py-16">
        <div class="w-7 h-7 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="comentarios.length === 0" class="flex flex-col items-center py-16 text-gray-400">
        <ion-icon name="chatbubbles-outline" style="font-size:40px" />
        <p class="mt-3 text-sm">No hay comentarios que mostrar</p>
      </div>

      <!-- Tabla -->
      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('id')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                ID <ion-icon :name="sortIcon('id')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('fuente')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Fuente <ion-icon :name="sortIcon('fuente')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('fecha')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Fecha <ion-icon :name="sortIcon('fecha')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('hora')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Hora <ion-icon :name="sortIcon('hora')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('comentario')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Comentario <ion-icon :name="sortIcon('comentario')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('emocion')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Emoción <ion-icon :name="sortIcon('emocion')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('estado')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Estado <ion-icon :name="sortIcon('estado')" style="font-size:12px" />
              </button>
            </th>
            <th class="px-5 py-3 w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="(c, i) in comentarios" :key="c.id"
            class="hover:bg-blue-50/30 transition-colors cursor-pointer group"
            :class="i % 2 === 1 ? 'bg-gray-50/40' : ''"
            @click="$router.push(`/app/comentarios/${c.id}`)">
            <td class="px-5 py-3 text-xs text-gray-400 font-mono">#{{ c.id }}</td>
            <td class="px-5 py-3 text-sm font-semibold text-[#0f1f3d]">{{ c.fuente?.nombre ?? '—' }}</td>
            <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ formatFecha(c.fecha) }}</td>
            <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ c.hora?.slice(0, 5) ?? '—' }}</td>
            <td class="px-5 py-3 max-w-xs">
              <p class="text-sm text-gray-700 line-clamp-1">{{ c.comentario }}</p>
              <p v-if="c.cliente_nombre" class="text-xs text-gray-400 mt-0.5">{{ c.cliente_nombre }}</p>
            </td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2">
                <!-- Círculo emoción con cara SVG -->
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                  :style="{ backgroundColor: emocionColor(c.emocion) }">
                  <svg v-if="c.emocion === 'positiva'" viewBox="0 0 24 24" width="16" height="16" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="white"/>
                    <circle cx="15" cy="10" r="1.2" fill="white"/>
                    <path d="M8 14.5 Q12 18 16 14.5" stroke="white" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                  </svg>
                  <svg v-else-if="c.emocion === 'neutral'" viewBox="0 0 24 24" width="16" height="16" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="white"/>
                    <circle cx="15" cy="10" r="1.2" fill="white"/>
                    <line x1="8.5" y1="15" x2="15.5" y2="15" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                  <svg v-else-if="c.emocion === 'negativa'" viewBox="0 0 24 24" width="16" height="16" fill="none">
                    <circle cx="9" cy="10" r="1.2" fill="white"/>
                    <circle cx="15" cy="10" r="1.2" fill="white"/>
                    <path d="M8 16.5 Q12 13 16 16.5" stroke="white" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none">
                    <circle cx="12" cy="12" r="5" stroke="white" stroke-width="1.5"/>
                  </svg>
                </div>
                <span class="text-xs font-medium capitalize" :style="{ color: emocionColor(c.emocion) }">
                  {{ c.emocion ?? '—' }}
                </span>
              </div>
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="estadoClass(c.estado)">{{ estadoLabel(c.estado) }}</span>
            </td>
            <td class="px-5 py-3" @click.stop>
              <div class="relative flex justify-center">
                <button @click.stop="toggleMenu(c.id)"
                  class="opacity-0 group-hover:opacity-100 p-1 rounded-lg hover:bg-gray-100 transition-all">
                  <ion-icon name="ellipsis-vertical-outline" style="font-size:16px;color:#6b7280" />
                </button>
                <div v-if="menuAbierto === c.id"
                  class="absolute right-0 top-6 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-[140px] py-1">
                  <button @click.stop="$router.push(`/app/comentarios/${c.id}`); menuAbierto = null"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition-colors">
                    <ion-icon name="eye-outline" style="font-size:14px;color:#6b7280" />
                    Ver más
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Footer paginación -->
      <div v-if="!cargando && comentarios.length > 0" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400">Mostrando {{ meta.from ?? 0 }} de {{ meta.total ?? 0 }} registros</p>
        <div class="flex items-center gap-1">
          <button @click="irPagina(page - 1)" :disabled="page === 1"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
            <ion-icon name="chevron-back-outline" style="font-size:13px" />
          </button>
          <template v-for="p in paginas" :key="p">
            <span v-if="p === '...'" class="w-7 h-7 flex items-center justify-center text-xs text-gray-400">…</span>
            <button v-else @click="irPagina(p)"
              class="w-7 h-7 text-xs rounded-lg transition-colors font-semibold"
              :class="p === page ? 'bg-[#0f1f3d] text-white' : 'text-gray-600 hover:bg-gray-100'">
              {{ p }}
            </button>
          </template>
          <button @click="irPagina(page + 1)" :disabled="page === meta.last_page"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
            <ion-icon name="chevron-forward-outline" style="font-size:13px" />
          </button>
        </div>
      </div>
    </div>

    <!-- Backdrop para cerrar dropdowns/menús -->
    <div v-if="dropEmocion || dropFuente || menuAbierto !== null"
      class="fixed inset-0 z-10" @click="cerrarTodo" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const comentarios = ref([])
const cargando = ref(false)
const page = ref(1)
const meta = ref({ last_page: 1, from: 0, to: 0, total: 0 })
const filtros = ref({ search: '', emocion: '', fuente_id: '', estado: '', fecha_desde: '', fecha_hasta: '' })
const fuentes = ref([])
const menuAbierto = ref(null)
const dropEmocion = ref(false)
const dropFuente = ref(false)
const sortCol = ref('id')
const sortDir = ref('desc')

const opcionesEmocion = [
  { value: '', label: 'Todo' },
  { value: 'positiva', label: 'Positiva' },
  { value: 'neutral', label: 'Neutral' },
  { value: 'negativa', label: 'Negativa' },
]

const emocionLabel = computed(() =>
  opcionesEmocion.find(o => o.value === filtros.value.emocion)?.label ?? 'Todo'
)
const fuenteLabel = computed(() =>
  fuentes.value.find(f => f.id === filtros.value.fuente_id)?.nombre ?? 'Todas las fuentes'
)

const paginas = computed(() => {
  const total = meta.value.last_page
  if (!total || total <= 1) return []
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const cur = page.value
  const pages = []
  pages.push(1)
  if (cur > 3) pages.push('...')
  for (let p = Math.max(2, cur - 1); p <= Math.min(total - 1, cur + 1); p++) pages.push(p)
  if (cur < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

function sortBy(col) {
  if (sortCol.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortCol.value = col
    sortDir.value = 'asc'
  }
  cargar()
}

function sortIcon(col) {
  if (sortCol.value !== col) return 'swap-vertical-outline'
  return sortDir.value === 'asc' ? 'arrow-up-outline' : 'arrow-down-outline'
}

function toggleMenu(id) {
  menuAbierto.value = menuAbierto.value === id ? null : id
}

function cerrarTodo() {
  dropEmocion.value = false
  dropFuente.value = false
  menuAbierto.value = null
}

function irPagina(p) {
  if (p < 1 || p > meta.value.last_page) return
  page.value = p
  cargar()
}

let debounceTimer = null
function debouncedCargar() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; cargar() }, 400)
}

function formatFecha(f) {
  if (!f) return '—'
  const [y, m, d] = f.split('-')
  return `${d}/${m}/${String(y).slice(2)}`
}

function emocionColor(e) {
  return { positiva: '#44b86b', neutral: '#9ba4ab', negativa: '#e8705e' }[e] ?? '#9ba4ab'
}

function estadoClass(e) {
  return {
    pendiente: 'bg-orange-100 text-orange-600',
    en_proceso: 'bg-blue-100 text-blue-700',
    resuelto: 'bg-green-100 text-green-700',
  }[e] ?? 'bg-gray-100 text-gray-600'
}

function estadoLabel(e) {
  return { pendiente: 'Pendiente', en_proceso: 'En proceso', resuelto: 'Resuelto' }[e] ?? e
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/comentarios', {
      params: {
        page: page.value,
        per_page: 9,
        sort: sortCol.value,
        dir: sortDir.value,
        ...Object.fromEntries(Object.entries(filtros.value).filter(([, v]) => v !== ''))
      }
    })
    comentarios.value = data.data
    meta.value = data.meta ?? data
  } finally {
    cargando.value = false
  }
}

async function cargarFuentes() {
  try {
    const { data } = await api.get('/fuentes', { params: { per_page: 100 } })
    fuentes.value = data.data ?? data
  } catch {}
}

async function exportar() {
  try {
    const params = Object.fromEntries(Object.entries(filtros.value).filter(([, v]) => v !== ''))
    const { data } = await api.get('/comentarios/exportar', { params, responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'comentarios.xlsx'
    a.click()
    URL.revokeObjectURL(url)
  } catch {}
}

onMounted(() => { cargar(); cargarFuentes() })
</script>
