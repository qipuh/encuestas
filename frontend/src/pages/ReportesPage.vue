<template>
  <div @click="fechaOpen = false; fuenteOpen = false">

    <!-- Encabezado -->
    <div class="mb-5">
      <h1 class="text-2xl font-black text-[#0f1f3d]">Reportes</h1>
    </div>

    <!-- Filtros -->
    <div class="flex gap-3 mb-5">

      <!-- Período -->
      <div class="relative" @click.stop>
        <button
          @click="fechaOpen = !fechaOpen; fuenteOpen = false"
          class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-200 shadow-sm text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors"
        >
          <ion-icon name="calendar-outline" style="font-size:15px;color:#9ca3af" />
          {{ labelFecha }}
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>
        <div v-if="fechaOpen"
          class="absolute left-0 top-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 p-4 z-50"
          style="min-width:280px"
        >
          <p class="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-wide">Rango de fechas</p>
          <div class="flex flex-col gap-2">
            <div>
              <p class="text-xs text-gray-500 mb-1">Desde</p>
              <input v-model="filtros.fecha_desde" type="date"
                class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
            </div>
            <div>
              <p class="text-xs text-gray-500 mb-1">Hasta</p>
              <input v-model="filtros.fecha_hasta" type="date"
                class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
            </div>
            <button @click="aplicarFecha"
              class="mt-1 w-full py-2 bg-[#0f1f3d] text-white text-xs font-bold rounded-lg hover:bg-[#152b52] transition-colors">
              Aplicar
            </button>
          </div>
        </div>
      </div>

      <!-- Fuente -->
      <div class="relative" @click.stop>
        <button
          @click="fuenteOpen = !fuenteOpen; fechaOpen = false"
          class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-200 shadow-sm text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors"
        >
          <ion-icon name="home-outline" style="font-size:15px;color:#9ca3af" />
          {{ filtros.fuente_id ? (fuentes.find(f => f.id == filtros.fuente_id)?.nombre ?? 'Todas las fuentes') : 'Todas las fuentes' }}
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>
        <div v-if="fuenteOpen"
          class="absolute left-0 top-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
          style="min-width:200px"
        >
          <button @click="filtros.fuente_id = ''; fuenteOpen = false; page = 1; cargar()"
            class="flex items-center w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
            :class="!filtros.fuente_id ? 'font-bold text-[#0f1f3d]' : 'text-gray-600'"
          >Todas las fuentes</button>
          <button v-for="f in fuentes" :key="f.id"
            @click="filtros.fuente_id = f.id; fuenteOpen = false; page = 1; cargar()"
            class="flex items-center w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
            :class="filtros.fuente_id == f.id ? 'font-bold text-[#0f1f3d]' : 'text-gray-600'"
          >{{ f.nombre }}</button>
        </div>
      </div>
    </div>

    <!-- Card tabla -->
    <div class="bg-white rounded-2xl shadow-sm overflow-visible">

      <!-- Cabecera card -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
          <h2 class="font-black text-[#0f1f3d] text-base">Centro de reportes</h2>
          <ion-icon name="options-outline" style="font-size:17px;color:#9ca3af" />
        </div>
        <button @click="exportar"
          class="flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
          Exportar a Excel
          <ion-icon name="document-outline" style="font-size:16px;color:#16a34a" />
        </button>
      </div>

      <!-- Spinner -->
      <div v-if="cargando" class="flex items-center justify-center py-16">
        <div class="w-7 h-7 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin" />
      </div>

      <!-- Vacío -->
      <div v-else-if="tablaDiaria.length === 0" class="flex flex-col items-center py-16 text-gray-400">
        <ion-icon name="bar-chart-outline" style="font-size:40px" />
        <p class="mt-3 text-sm">Sin datos para el período seleccionado</p>
      </div>

      <!-- Tabla -->
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('fuente')" class="flex items-center gap-1 hover:text-gray-700">
                Fuente <ion-icon :name="sortIcon('fuente')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('fecha')" class="flex items-center gap-1 hover:text-gray-700">
                Fecha <ion-icon :name="sortIcon('fecha')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('login')" class="flex items-center gap-1 hover:text-gray-700">
                LogIn <ion-icon :name="sortIcon('login')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('tiempo')" class="flex items-center gap-1 hover:text-gray-700">
                TimeOn <ion-icon :name="sortIcon('tiempo')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('positivas')" class="inline-flex items-center gap-0.5 hover:text-gray-700">
                Positiva <ion-icon :name="sortIcon('positivas')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('neutrales')" class="inline-flex items-center gap-0.5 hover:text-gray-700">
                Neutral <ion-icon :name="sortIcon('neutrales')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('negativas')" class="inline-flex items-center gap-0.5 hover:text-gray-700">
                Negativa <ion-icon :name="sortIcon('negativas')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('comentarios')" class="inline-flex items-center gap-0.5 hover:text-gray-700">
                Coment. <ion-icon :name="sortIcon('comentarios')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500" style="min-width:160px">
              <button @click="sortBy('satisfaccion')" class="flex items-center gap-1 hover:text-gray-700">
                Satisfacción <ion-icon :name="sortIcon('satisfaccion')" style="font-size:11px" />
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in tablaDiaria" :key="`${row.fuente}-${row.fecha}-${i}`"
            class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors"
            :class="i % 2 === 1 ? 'bg-gray-50/40' : ''"
          >
            <td class="px-5 py-3.5 text-sm font-semibold text-[#0f1f3d]">{{ row.fuente }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ formatFecha(row.fecha) }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500">{{ row.login ?? row.encargado ?? '—' }}</td>
            <td class="px-5 py-3.5 text-sm font-semibold" :style="`color:${timeOnColor(row.tiempo_minutos)}`">
              {{ formatTimeOn(row.tiempo_minutos) }}
            </td>
            <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ row.positivas ?? 0 }}</td>
            <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ row.neutrales ?? 0 }}</td>
            <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ row.negativas ?? 0 }}</td>
            <td class="px-4 py-3.5 text-center text-sm text-gray-700">{{ row.comentarios ?? 0 }}</td>
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-2">
                <div class="bg-gray-200 rounded-full h-2.5 flex-1" style="min-width:80px;max-width:110px">
                  <div class="h-2.5 rounded-full bg-[#2ecc71] transition-all"
                    :style="`width:${Math.min(row.satisfaccion ?? 0, 100)}%`" />
                </div>
                <span class="text-sm font-bold text-gray-700 w-10 text-right">{{ row.satisfaccion ?? 0 }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Footer paginación -->
      <div class="flex items-center justify-between px-6 py-3.5 border-t border-gray-100">
        <p class="text-xs text-gray-400">
          Mostrando {{ tablaDiaria.length }} de {{ meta.total ?? tablaDiaria.length }} registros
        </p>
        <div v-if="meta.last_page > 1" class="flex items-center gap-1">
          <button @click="irPagina(page - 1)" :disabled="page === 1"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <ion-icon name="chevron-back-outline" style="font-size:14px" />
          </button>

          <template v-for="p in paginasVisibles" :key="p">
            <span v-if="p === '...'" class="w-7 h-7 flex items-center justify-center text-xs text-gray-400">…</span>
            <button v-else @click="irPagina(p)"
              class="w-7 h-7 text-xs rounded-lg font-semibold transition-colors"
              :class="p === page ? 'bg-[#0f1f3d] text-white' : 'text-gray-600 hover:bg-gray-100'">
              {{ p }}
            </button>
          </template>

          <button @click="irPagina(page + 1)" :disabled="page === meta.last_page"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors">
            <ion-icon name="chevron-forward-outline" style="font-size:14px" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const cargando   = ref(false)
const fuentes    = ref([])
const tablaDiaria = ref([])
const page       = ref(1)
const meta       = ref({ last_page: 1, total: 0 })
const sort       = ref({ col: 'fecha', dir: 'desc' })
const fechaOpen  = ref(false)
const fuenteOpen = ref(false)

const now   = new Date()
const yy    = now.getFullYear()
const mm    = String(now.getMonth() + 1).padStart(2, '0')
const desde = `${yy}-${mm}-01`
const hasta = `${yy}-${mm}-${String(new Date(yy, now.getMonth() + 1, 0).getDate()).padStart(2,'0')}`

const filtros = ref({ fecha_desde: desde, fecha_hasta: hasta, fuente_id: '' })

// ── Labels ──────────────────────────────────────────────
const labelFecha = computed(() => {
  const fmt = d => { const [y, m, dy] = d.split('-'); return `${dy}/${m}/${y.slice(2)}` }
  return `${fmt(filtros.value.fecha_desde)} al ${fmt(filtros.value.fecha_hasta)}`
})

// ── Paginación ──────────────────────────────────────────
const paginasVisibles = computed(() => {
  const total   = meta.value.last_page
  const current = page.value
  if (total <= 9) return Array.from({ length: total }, (_, i) => i + 1)

  const pages = []
  const window = 3
  for (let i = 1; i <= Math.min(window + 4, total); i++) pages.push(i)
  if (total > window + 5) { pages.push('...'); pages.push(total) }
  else { for (let i = window + 5; i <= total; i++) pages.push(i) }
  return pages
})

// ── Sort ────────────────────────────────────────────────
function sortBy(col) {
  if (sort.value.col === col) sort.value.dir = sort.value.dir === 'asc' ? 'desc' : 'asc'
  else { sort.value.col = col; sort.value.dir = 'asc' }
  page.value = 1; cargar()
}
function sortIcon(col) {
  if (sort.value.col !== col) return 'swap-vertical-outline'
  return sort.value.dir === 'asc' ? 'arrow-up-outline' : 'arrow-down-outline'
}

function irPagina(p) {
  if (p < 1 || p > meta.value.last_page) return
  page.value = p; cargar()
}

function aplicarFecha() { fechaOpen.value = false; page.value = 1; cargar() }

// ── API ─────────────────────────────────────────────────
async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/reportes/dashboard', {
      params: {
        ...filtros.value,
        page:     page.value,
        per_page: 20,
        sort:     sort.value.col,
        dir:      sort.value.dir,
      }
    })
    tablaDiaria.value = data.data ?? data.por_fuente ?? []
    meta.value = data.meta ?? { last_page: 1, total: tablaDiaria.value.length }
  } finally {
    cargando.value = false
  }
}

// ── Formatters ──────────────────────────────────────────
function formatFecha(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y.slice(2)}`
}

function formatTimeOn(min) {
  if (!min && min !== 0) return '—'
  const h = Math.floor(min / 60)
  const m = min % 60
  return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')} hrs.`
}

function timeOnColor(min) {
  if (!min) return '#94a3b8'
  if (min >= 480) return '#2ecc71'   // ≥ 8h verde
  if (min >= 240) return '#f59e0b'   // 4-8h naranja
  return '#ef4444'                    // < 4h rojo
}

async function exportar() {
  try {
    const params = Object.fromEntries(Object.entries(filtros.value).filter(([, v]) => v !== ''))
    const { data } = await api.get('/reportes/exportar', { params, responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'reportes.xlsx'
    a.click()
    URL.revokeObjectURL(url)
  } catch {}
}

onMounted(async () => {
  const f = await api.get('/fuentes')
  fuentes.value = f.data
  await cargar()
})
</script>
