<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
import Chart from 'primevue/chart'
import api from '@/api/axios'
import MiniChart from '@/components/MiniChart.vue'

// ── Estado ────────────────────────────────────────────────────
const hoy = new Date()
const mesActual = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}`

const filtroMes       = ref(mesActual)
const filtroFuente    = ref('')
const filtroCategoria = ref([])   // multi-select

const fuentes    = ref([])
const categorias = ref([])
const stats      = ref({})
const ultimosComentarios = ref([])

const iconEmocion = { positiva: 'happy-outline', neutral: 'remove-circle-outline', negativa: 'sad-outline' }

// ── Dropdowns custom ──────────────────────────────────────────
const menuAbierto    = ref(null)   // id del comentario con menú abierto
const openDropdown   = ref(null)   // 'mes' | 'fuente' | 'categoria' | null
const pickerYear     = ref(hoy.getFullYear())
const buscarFuente   = ref('')
const buscarCategoria = ref('')

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']

function toggleDropdown(name) {
  openDropdown.value = openDropdown.value === name ? null : name
}
function closeDropdowns() { openDropdown.value = null; menuAbierto.value = null }

function selectMes(idx) {
  filtroMes.value = `${pickerYear.value}-${String(idx + 1).padStart(2, '0')}`
  closeDropdowns()
  cargarDatos()
}
function isSelectedMes(idx) {
  const [y, m] = filtroMes.value.split('-')
  return parseInt(y) === pickerYear.value && parseInt(m) === idx + 1
}

function selectFuente(id) {
  filtroFuente.value = filtroFuente.value == id ? '' : id
  closeDropdowns()
  cargarDatos()
}
function toggleCategoria(id) {
  const idx = filtroCategoria.value.indexOf(id)
  if (idx === -1) filtroCategoria.value.push(id)
  else filtroCategoria.value.splice(idx, 1)
  cargarDatos()
}
function toggleTodasCategorias() {
  filtroCategoria.value = []
  cargarDatos()
}

const fuentesFiltradas   = computed(() =>
  fuentes.value.filter(f => f.nombre.toLowerCase().includes(buscarFuente.value.toLowerCase()))
)
const categoriasFiltradas = computed(() =>
  categorias.value.filter(c => c.nombre.toLowerCase().includes(buscarCategoria.value.toLowerCase()))
)

// ── Computados ────────────────────────────────────────────────
const labelMes = computed(() => {
  const [y, m] = filtroMes.value.split('-')
  const nombre = MESES[parseInt(m) - 1]
  return `${nombre} ${y}`
})

const labelFuente = computed(() =>
  fuentes.value.find(f => f.id == filtroFuente.value)?.nombre ?? 'Todas las sedes'
)

const labelCategoria = computed(() => {
  if (!filtroCategoria.value.length) return 'Todas las categorías'
  if (filtroCategoria.value.length === 1)
    return categorias.value.find(c => c.id == filtroCategoria.value[0])?.nombre ?? 'Todas las categorías'
  return `${filtroCategoria.value.length} categorías`
})

const emocionPredominante = computed(() => {
  const p = stats.value.positivas ?? 0
  const n = stats.value.neutrales ?? 0
  const neg = stats.value.negativas ?? 0
  const max = Math.max(p, n, neg)
  if (max === p) return 'happy-outline'
  if (max === n) return 'remove-circle-outline'
  return 'sad-outline'
})

const emocionesDetalle = computed(() => {
  const total = stats.value.total_emociones || 1
  return [
    { label: 'Positivas', color: '#2ecc71', pct: Math.round((stats.value.positivas ?? 0) / total * 100), total: stats.value.positivas ?? 0 },
    { label: 'Neutrales', color: '#94a3b8', pct: Math.round((stats.value.neutrales ?? 0) / total * 100), total: stats.value.neutrales ?? 0 },
    { label: 'Negativas', color: '#ef4444', pct: Math.round((stats.value.negativas ?? 0) / total * 100), total: stats.value.negativas ?? 0 },
  ]
})

const donutData = computed(() => ({
  labels: ['Positivas', 'Neutrales', 'Negativas'],
  datasets: [{
    data: [stats.value.positivas ?? 1, stats.value.neutrales ?? 0, stats.value.negativas ?? 0],
    backgroundColor: ['#2ecc71', '#94a3b8', '#ef4444'],
    borderWidth: 0,
    hoverOffset: 4,
  }]
}))

const donutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '74%',
  plugins: { legend: { display: false }, tooltip: { enabled: false } },
}

// ── Helpers ───────────────────────────────────────────────────
function formatFecha(f) {
  if (!f) return ''
  const d = new Date(f)
  return d.toLocaleDateString('es', { day: '2-digit', month: 'short' }).replace('.', '')
}

function colorEmocion(e) {
  return { positiva: '#2ecc71', neutral: '#94a3b8', negativa: '#ef4444' }[e] ?? '#94a3b8'
}

function bgEmocion(e) {
  return { positiva: '#dcfce7', neutral: '#f1f5f9', negativa: '#fee2e2' }[e] ?? '#f1f5f9'
}

// ── Carga ─────────────────────────────────────────────────────
function rangoMes() {
  const [y, m] = filtroMes.value.split('-').map(Number)
  const desde = `${y}-${String(m).padStart(2, '0')}-01`
  const hasta = new Date(y, m, 0).toISOString().slice(0, 10)
  return { fecha_desde: desde, fecha_hasta: hasta }
}

async function cargarDatos() {
  const params = {
    ...rangoMes(),
    fuente_id: filtroFuente.value || undefined,
    categoria_id: filtroCategoria.value.length ? filtroCategoria.value.join(',') : undefined,
  }
  try {
    const [dashRes, comsRes] = await Promise.all([
      api.get('/reportes/dashboard', { params }),
      api.get('/comentarios', { params: { per_page: 5, ...params } }),
    ])
    stats.value = dashRes.data
    ultimosComentarios.value = comsRes.data.data ?? []
  } catch {}
}

async function cargarFiltros() {
  try {
    const [fRes, cRes] = await Promise.all([api.get('/fuentes'), api.get('/categorias')])
    fuentes.value = Array.isArray(fRes.data) ? fRes.data : (fRes.data.data ?? [])
    categorias.value = Array.isArray(cRes.data) ? cRes.data : (cRes.data.data ?? [])
  } catch {}
}

onMounted(() => { cargarFiltros(); cargarDatos() })
</script>

<template>
  <div>
    <!-- ── Título ── -->
    <h1 class="text-2xl font-black text-[#0f1f3d] mb-5">Dashboard</h1>

    <!-- ── Filtros ── -->
    <!-- Backdrop para cerrar dropdowns -->
    <div v-if="openDropdown" class="fixed inset-0 z-30" @click="closeDropdowns" />

    <div class="flex flex-wrap gap-3 mb-6">

      <!-- Mes -->
      <div class="relative z-40">
        <button
          @click.stop="toggleDropdown('mes')"
          class="flex items-center gap-2 px-4 py-2 bg-white border rounded-xl shadow-sm hover:border-[#2ecc71] transition-colors min-w-[170px]"
          :class="openDropdown === 'mes' ? 'border-[#2ecc71]' : 'border-gray-200'"
        >
          <ion-icon name="calendar-outline" style="font-size:16px;color:#6b7280;flex-shrink:0" />
          <span class="text-sm text-gray-700 font-medium flex-1 text-left truncate">{{ labelMes }}</span>
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>

        <div v-if="openDropdown === 'mes'" @click.stop class="absolute top-full left-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-5 z-40" style="width:290px">
          <p class="text-sm font-bold text-gray-700 mb-4">Selecciona un periodo</p>
          <div class="flex items-center justify-between mb-4">
            <button @click="pickerYear--" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
              <ion-icon name="chevron-back-outline" style="font-size:16px;color:#6b7280" />
            </button>
            <span class="text-sm font-bold text-gray-800">{{ pickerYear }}</span>
            <button @click="pickerYear++" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
              <ion-icon name="chevron-forward-outline" style="font-size:16px;color:#6b7280" />
            </button>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="(m, i) in MESES" :key="i"
              @click="selectMes(i)"
              class="py-2 rounded-xl text-sm font-medium transition-colors"
              :class="isSelectedMes(i) ? 'bg-[#0f1f3d] text-white' : 'text-gray-600 hover:bg-gray-50'"
            >{{ m }}</button>
          </div>
        </div>
      </div>

      <!-- Fuente -->
      <div class="relative z-40">
        <button
          @click.stop="toggleDropdown('fuente')"
          class="flex items-center gap-2 px-4 py-2 bg-white border rounded-xl shadow-sm hover:border-[#2ecc71] transition-colors min-w-[210px]"
          :class="openDropdown === 'fuente' ? 'border-[#2ecc71]' : 'border-gray-200'"
        >
          <ion-icon name="home-outline" style="font-size:16px;color:#6b7280;flex-shrink:0" />
          <span class="text-sm text-gray-700 font-medium flex-1 text-left truncate">{{ labelFuente }}</span>
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>

        <div v-if="openDropdown === 'fuente'" @click.stop class="absolute top-full left-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-4 z-40" style="width:280px">
          <p class="text-sm font-bold text-gray-700 mb-3">Selecciona una fuente</p>
          <div class="relative mb-3">
            <ion-icon name="search-outline" class="absolute left-3 top-1/2 -translate-y-1/2" style="font-size:14px;color:#9ca3af" />
            <input v-model="buscarFuente" placeholder="Buscar..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
          <div class="overflow-y-auto" style="max-height:220px">
            <button
              @click="selectFuente('')"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-colors text-left"
              :class="filtroFuente === '' ? 'bg-gray-100 font-semibold text-gray-800' : 'text-gray-600 hover:bg-gray-50'"
            >
              <ion-icon name="home-outline" style="font-size:15px;color:#9ca3af" />
              Todos
            </button>
            <button
              v-for="f in fuentesFiltradas" :key="f.id"
              @click="selectFuente(f.id)"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-colors text-left"
              :class="filtroFuente == f.id ? 'bg-gray-100 font-semibold text-gray-800' : 'text-gray-600 hover:bg-gray-50'"
            >
              <ion-icon name="home-outline" style="font-size:15px;color:#9ca3af" />
              {{ f.nombre }}
            </button>
          </div>
        </div>
      </div>

      <!-- Categoría -->
      <div class="relative z-40">
        <button
          @click.stop="toggleDropdown('categoria')"
          class="flex items-center gap-2 px-4 py-2 bg-white border rounded-xl shadow-sm hover:border-[#2ecc71] transition-colors min-w-[200px]"
          :class="openDropdown === 'categoria' ? 'border-[#2ecc71]' : 'border-gray-200'"
        >
          <ion-icon name="pricetag-outline" style="font-size:16px;color:#6b7280;flex-shrink:0" />
          <span class="text-sm text-gray-700 font-medium flex-1 text-left truncate">{{ labelCategoria }}</span>
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>

        <div v-if="openDropdown === 'categoria'" @click.stop class="absolute top-full left-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-4 z-40" style="width:280px">
          <p class="text-sm font-bold text-gray-700 mb-3">Selecciona categorías</p>
          <div class="relative mb-3">
            <ion-icon name="search-outline" class="absolute left-3 top-1/2 -translate-y-1/2" style="font-size:14px;color:#9ca3af" />
            <input v-model="buscarCategoria" placeholder="Buscar..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
          <div class="overflow-y-auto" style="max-height:220px">
            <button
              @click="toggleTodasCategorias()"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-colors text-left"
              :class="!filtroCategoria.length ? 'text-gray-800 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
            >
              <span class="w-5 h-5 rounded flex items-center justify-center shrink-0 border"
                :class="!filtroCategoria.length ? 'bg-[#0f1f3d] border-[#0f1f3d]' : 'border-gray-300'">
                <ion-icon v-if="!filtroCategoria.length" name="checkmark-outline" style="font-size:12px;color:white" />
              </span>
              Todas las categorías
            </button>
            <button
              v-for="c in categoriasFiltradas" :key="c.id"
              @click="toggleCategoria(c.id)"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-colors text-left"
              :class="filtroCategoria.includes(c.id) ? 'text-gray-800 font-semibold' : 'text-gray-600 hover:bg-gray-50'"
            >
              <span class="w-5 h-5 rounded flex items-center justify-center shrink-0 border"
                :class="filtroCategoria.includes(c.id) ? 'bg-[#0f1f3d] border-[#0f1f3d]' : 'border-gray-300'">
                <ion-icon v-if="filtroCategoria.includes(c.id)" name="checkmark-outline" style="font-size:12px;color:white" />
              </span>
              {{ c.nombre }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── KPI Cards (4 columnas) ── -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-4">

      <!-- Encuestas del día -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center justify-between">
        <p class="text-sm font-semibold text-gray-500">Encuestas del día</p>
        <p class="text-3xl font-black text-[#0f1f3d]">{{ stats.encuestas_dia ?? 0 }}</p>
      </div>

      <!-- Comentarios del día -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center justify-between">
        <p class="text-sm font-semibold text-gray-500">Comentarios del día</p>
        <p class="text-3xl font-black text-[#0f1f3d]">{{ stats.comentarios_dia ?? 0 }}</p>
      </div>

      <!-- Emoción predominante -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center justify-between">
        <p class="text-sm font-semibold text-gray-500">Emoción predominante</p>
        <div class="w-11 h-11 rounded-full bg-green-100 flex items-center justify-center">
          <ion-icon :name="emocionPredominante" style="font-size:22px;color:#16a34a" />
        </div>
      </div>

      <!-- Fuentes Inactivas -->
      <div class="bg-white rounded-2xl border shadow-sm px-5 py-4 flex items-center justify-between"
        :class="(stats.fuentes_inactivas ?? 0) > 0 ? 'border-red-200' : 'border-gray-100'">
        <p class="text-sm font-semibold text-gray-500">Fuentes Inactivas</p>
        <p class="text-3xl font-black" :class="(stats.fuentes_inactivas ?? 0) > 0 ? 'text-red-500' : 'text-[#0f1f3d]'">
          {{ stats.fuentes_inactivas ?? 0 }}
        </p>
      </div>
    </div>

    <!-- ── Metric cards (3 columnas) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

      <!-- Satisfacción general -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-600 mb-3">Satisfacción general</p>
            <p class="text-3xl font-black text-[#0f1f3d] leading-none mb-1">
              {{ stats.satisfaccion ?? 0 }}%<span class="text-base font-normal text-gray-400"> / 100%</span>
            </p>
            <div class="flex items-center gap-1">
              <ion-icon
                :name="(stats.satisfaccion_trend ?? 0) >= 0 ? 'arrow-up-outline' : 'arrow-down-outline'"
                :style="`font-size:12px;color:${(stats.satisfaccion_trend ?? 0) >= 0 ? '#22c55e' : '#ef4444'}`"
              />
              <span class="text-xs font-medium" :class="(stats.satisfaccion_trend ?? 0) >= 0 ? 'text-green-500' : 'text-red-500'">
                {{ Math.abs(stats.satisfaccion_trend ?? 0) }} % vs mes anterior
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end justify-between shrink-0 w-32">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
              <ion-icon name="happy-outline" style="font-size:20px;color:#16a34a" />
            </div>
            <MiniChart :data="stats.satisfaccion_chart ?? [40,45,42,50,55,52,60,58,65,68]" color="#2ecc71" />
          </div>
        </div>
      </div>

      <!-- Total emociones -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-600 mb-3">Total emociones</p>
            <p class="text-3xl font-black text-[#0f1f3d] leading-none mb-1">
              {{ (stats.total_emociones ?? 0).toLocaleString('es') }}
            </p>
            <div class="flex items-center gap-1">
              <ion-icon
                :name="(stats.emociones_trend ?? 0) >= 0 ? 'arrow-up-outline' : 'arrow-down-outline'"
                :style="`font-size:12px;color:${(stats.emociones_trend ?? 0) >= 0 ? '#22c55e' : '#ef4444'}`"
              />
              <span class="text-xs font-medium" :class="(stats.emociones_trend ?? 0) >= 0 ? 'text-green-500' : 'text-red-500'">
                {{ Math.abs(stats.emociones_trend ?? 0) }} % vs mes anterior
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end justify-between shrink-0 w-32">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <ion-icon name="analytics-outline" style="font-size:20px;color:#3b82f6" />
            </div>
            <MiniChart :data="stats.emociones_chart ?? [80,75,85,70,65,72,68,74,71,69]" color="#3b82f6" />
          </div>
        </div>
      </div>

      <!-- Total comentarios -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex gap-3">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-600 mb-3">Total comentarios</p>
            <p class="text-3xl font-black text-[#0f1f3d] leading-none mb-1">
              {{ (stats.total_comentarios ?? 0).toLocaleString('es') }}
            </p>
            <div class="flex items-center gap-1">
              <ion-icon
                :name="(stats.comentarios_trend ?? 0) >= 0 ? 'arrow-up-outline' : 'arrow-down-outline'"
                :style="`font-size:12px;color:${(stats.comentarios_trend ?? 0) >= 0 ? '#22c55e' : '#ef4444'}`"
              />
              <span class="text-xs font-medium" :class="(stats.comentarios_trend ?? 0) >= 0 ? 'text-green-500' : 'text-red-500'">
                {{ Math.abs(stats.comentarios_trend ?? 0) }} % vs mes anterior
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end justify-between shrink-0 w-32">
            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
              <ion-icon name="chatbubbles-outline" style="font-size:20px;color:#64748b" />
            </div>
            <MiniChart :data="stats.comentarios_chart ?? [50,48,52,47,45,49,46,44,47,45]" color="#94a3b8" />
          </div>
        </div>
      </div>
    </div>

    <!-- ── Bottom row ── -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

      <!-- Participación de emociones -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Participación de emociones</p>

        <div class="flex items-center gap-6">
          <!-- Donut -->
          <div class="relative shrink-0" style="width:160px;height:160px">
            <Chart type="doughnut" :data="donutData" :options="donutOptions" style="width:160px;height:160px" />
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <p class="text-xl font-black text-[#0f1f3d] leading-none">
                {{ (stats.total_emociones ?? 0).toLocaleString('es') }}
              </p>
              <p class="text-[10px] text-gray-400 mt-0.5">Encuestas</p>
            </div>
          </div>

          <!-- Leyenda -->
          <div class="flex-1 min-w-0">
            <div class="flex justify-end gap-4 mb-2">
              <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Porcentaje</span>
              <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide w-12 text-right">Encuestas</span>
            </div>
            <div class="space-y-3">
              <div v-for="item in emocionesDetalle" :key="item.label" class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: item.color }" />
                <span class="text-sm text-gray-600 flex-1">{{ item.label }}</span>
                <span class="text-sm font-bold text-gray-800 w-10 text-right">{{ item.pct }}%</span>
                <span class="text-sm text-gray-500 w-12 text-right">{{ item.total.toLocaleString('es') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Últimos comentarios -->
      <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <p class="text-sm font-semibold text-gray-700">Ultimos comentarios</p>
          <RouterLink to="/app/comentarios" class="text-xs text-[#2ecc71] font-semibold hover:underline">
            Ver todos
          </RouterLink>
        </div>

        <div v-if="!ultimosComentarios.length" class="py-12 text-center text-gray-400 text-sm">
          Sin comentarios recientes
        </div>

        <div v-else class="divide-y divide-gray-50">
          <div
            v-for="c in ultimosComentarios" :key="c.id"
            class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors"
            @click="$router.push(`/app/comentarios/${c.id}`)"
          >
            <!-- Emoción circle -->
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
              :style="{ background: bgEmocion(c.emocion) }"
            >
              <ion-icon
                :name="iconEmocion[c.emocion] ?? 'help-circle-outline'"
                :style="`font-size:20px;color:${colorEmocion(c.emocion)}`"
              />
            </div>

            <!-- Fuente -->
            <div class="w-36 shrink-0">
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Fuente</p>
              <p class="text-sm font-medium text-gray-700 truncate">{{ c.fuente?.nombre ?? '—' }}</p>
            </div>

            <!-- Comentario -->
            <div class="flex-1 min-w-0">
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Comentario</p>
              <p class="text-sm text-gray-600 truncate">{{ c.comentario ?? '—' }}</p>
            </div>

            <!-- Fecha -->
            <div class="w-16 shrink-0 text-right">
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Fecha</p>
              <p class="text-sm text-gray-500">{{ formatFecha(c.fecha) }}</p>
            </div>

            <!-- Menú -->
            <div class="relative shrink-0" @click.stop>
              <button
                class="p-1 text-gray-300 hover:text-gray-500"
                @click="menuAbierto = menuAbierto === c.id ? null : c.id"
              >
                <ion-icon name="ellipsis-vertical" style="font-size:16px" />
              </button>
              <div v-if="menuAbierto === c.id"
                class="absolute right-0 top-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                style="min-width:130px"
              >
                <button
                  @click="menuAbierto = null; router.push(`/app/comentarios/${c.id}`)"
                  class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  <ion-icon name="eye-outline" style="font-size:15px;color:#6b7280" />
                  Ver más
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
