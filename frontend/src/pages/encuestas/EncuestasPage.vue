<template>
  <div @click="menuAbierto = null; fuenteOpen = false">

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
      <h1 class="text-2xl font-black text-[#0f1f3d]">Encuestas</h1>

      <!-- Filtro fuente -->
      <div class="relative" @click.stop>
        <button
          @click="fuenteOpen = !fuenteOpen"
          class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-200 shadow-sm text-sm text-gray-600 font-medium hover:bg-gray-50 transition-colors"
        >
          <ion-icon name="home-outline" style="font-size:15px;color:#9ca3af" />
          {{ filtros.fuente_id ? (fuentes.find(f => f.id == filtros.fuente_id)?.nombre ?? 'Todas las fuentes') : 'Todas las fuentes' }}
          <ion-icon name="chevron-down-outline" style="font-size:12px;color:#9ca3af" />
        </button>
        <div v-if="fuenteOpen"
          class="absolute right-0 top-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
          style="min-width:200px"
        >
          <button
            @click="filtros.fuente_id = ''; fuenteOpen = false; page = 1; cargar()"
            class="flex items-center w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
            :class="!filtros.fuente_id ? 'font-bold text-[#0f1f3d]' : 'text-gray-600'"
          >Todas las fuentes</button>
          <button
            v-for="f in fuentes" :key="f.id"
            @click="filtros.fuente_id = f.id; fuenteOpen = false; page = 1; cargar()"
            class="flex items-center w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
            :class="filtros.fuente_id == f.id ? 'font-bold text-[#0f1f3d]' : 'text-gray-600'"
          >{{ f.nombre }}</button>
        </div>
      </div>
    </div>

    <!-- Card principal -->
    <div class="bg-white rounded-2xl shadow-sm overflow-visible">

      <!-- Cabecera de la card -->
      <div class="flex flex-wrap items-center gap-4 px-6 py-4 border-b border-gray-100">
        <h2 class="font-black text-[#0f1f3d] text-base shrink-0">Centro de encuestas</h2>

        <!-- Buscador -->
        <div class="relative" style="width:220px">
          <ion-icon name="search-outline" style="font-size:14px;color:#9ca3af;position:absolute;left:11px;top:50%;transform:translateY(-50%)" />
          <input
            v-model="filtros.search"
            @input="debouncedCargar"
            type="text"
            placeholder="Buscar..."
            class="w-full pl-8 pr-4 py-2 text-sm bg-gray-50 border border-gray-100 rounded-xl focus:outline-none focus:border-[#2ecc71]"
          />
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
          <button
            @click="exportarExcel"
            class="flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors"
          >
            Exportar a Excel
            <ion-icon name="document-outline" style="font-size:16px;color:#16a34a" />
          </button>
          <RouterLink
            to="/app/encuestas/crear"
            class="flex items-center gap-2 px-4 py-2 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] transition-colors"
          >
            <ion-icon name="add-outline" style="font-size:17px" />
            Crear encuesta
          </RouterLink>
        </div>
      </div>

      <!-- Spinner -->
      <div v-if="cargando" class="flex items-center justify-center py-16">
        <div class="w-7 h-7 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin" />
      </div>

      <!-- Vacío -->
      <div v-else-if="encuestas.length === 0" class="flex flex-col items-center py-16 text-gray-400">
        <ion-icon name="clipboard-outline" style="font-size:40px" />
        <p class="mt-3 text-sm">No hay encuestas que mostrar</p>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
      <table v-else class="w-full text-sm min-w-[750px]">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 w-16">
              <button @click="sortBy('id')" class="flex items-center gap-1 hover:text-gray-700">
                ID <ion-icon :name="sortIcon('id')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('nombre')" class="flex items-center gap-1 hover:text-gray-700">
                Nombre de encuesta <ion-icon :name="sortIcon('nombre')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 whitespace-nowrap">
              <button @click="sortBy('created_at')" class="flex items-center gap-1 hover:text-gray-700">
                Fecha de creación <ion-icon :name="sortIcon('created_at')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('usuario')" class="flex items-center gap-1 hover:text-gray-700">
                Creador <ion-icon :name="sortIcon('usuario')" style="font-size:11px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('fuente')" class="flex items-center gap-1 hover:text-gray-700">
                Fuente <ion-icon :name="sortIcon('fuente')" style="font-size:11px" />
              </button>
            </th>
            <!-- Total respuestas -->
            <th class="text-center px-5 py-3">
              <button @click="sortBy('total')" class="inline-flex items-center gap-0.5 hover:opacity-70">
                <ion-icon name="sad-outline" style="font-size:18px;color:#6b7280" />
                <ion-icon :name="sortIcon('total')" style="font-size:10px;color:#9ca3af" />
              </button>
            </th>
            <!-- Comentarios -->
            <th class="text-center px-5 py-3">
              <button @click="sortBy('comentarios')" class="inline-flex items-center gap-0.5 hover:opacity-70">
                <ion-icon name="chatbubbles-outline" style="font-size:17px;color:#6b7280" />
                <ion-icon :name="sortIcon('comentarios')" style="font-size:10px;color:#9ca3af" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">
              <button @click="sortBy('estado')" class="flex items-center gap-1 hover:text-gray-700">
                Estado <ion-icon :name="sortIcon('estado')" style="font-size:11px" />
              </button>
            </th>
            <th class="px-3 py-3 w-12" />
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(enc, i) in encuestas" :key="enc.id"
            class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors"
            :class="i % 2 === 1 ? 'bg-gray-50/40' : ''"
          >
            <td class="px-5 py-3.5 text-sm text-gray-500 font-medium">{{ enc.id }}</td>
            <td class="px-5 py-3.5 font-semibold text-[#0f1f3d]">{{ enc.nombre }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(enc.created_at) }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-600">{{ enc.usuario?.name ?? '—' }}</td>
            <td class="px-5 py-3.5 text-sm text-gray-600">{{ enc.fuente?.nombre ?? '—' }}</td>
            <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-700">
              {{ (enc.negativas_count ?? 0) + (enc.neutrales_count ?? 0) + (enc.positivas_count ?? 0) }}
            </td>
            <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-700">{{ enc.comentarios_count ?? 0 }}</td>
            <td class="px-5 py-3.5">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                :class="enc.estado === 'activa' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600'"
              >
                {{ enc.estado === 'activa' ? 'Activo' : 'Pausado' }}
              </span>
            </td>
            <td class="px-3 py-3.5">
              <div class="relative" @click.stop>
                <button
                  @click="menuAbierto = menuAbierto === enc.id ? null : enc.id"
                  class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-400"
                >
                  <ion-icon name="ellipsis-vertical" style="font-size:16px" />
                </button>
                <div
                  v-if="menuAbierto === enc.id"
                  class="absolute right-8 top-0 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                  style="min-width:130px"
                >
                  <button
                    v-if="enc.estado === 'activa'"
                    @click="lanzarEncuesta(enc); menuAbierto = null"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-[#2ecc71] hover:bg-green-50 transition-colors font-semibold"
                  >
                    <ion-icon name="rocket-outline" style="font-size:15px" />
                    Lanzar
                  </button>
                  <RouterLink
                    :to="`/app/encuestas/${enc.id}/editar`"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition-colors"
                  >
                    <ion-icon name="create-outline" style="font-size:15px;color:#6b7280" />
                    Editar
                  </RouterLink>
                  <button
                    @click="togglePause(enc); menuAbierto = null"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition-colors"
                  >
                    <ion-icon :name="enc.estado === 'activa' ? 'pause-outline' : 'play-outline'" style="font-size:15px;color:#6b7280" />
                    {{ enc.estado === 'activa' ? 'Pausar' : 'Activar' }}
                  </button>
                  <button
                    @click="confirmarEliminar(enc); menuAbierto = null"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
                  >
                    <ion-icon name="close-outline" style="font-size:15px" />
                    Eliminar
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Footer paginación -->
      <div class="flex items-center justify-between px-6 py-3.5 border-t border-gray-100">
        <p class="text-xs text-gray-400">
          Mostrando {{ encuestas.length }} de {{ meta.total ?? encuestas.length }} registros
        </p>
        <div v-if="meta.last_page > 1" class="flex items-center gap-1">
          <button
            @click="irPagina(page - 1)" :disabled="page === 1"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors"
          >
            <ion-icon name="chevron-back-outline" style="font-size:14px" />
          </button>
          <button
            v-for="p in paginas" :key="p" @click="irPagina(p)"
            class="w-7 h-7 text-xs rounded-lg transition-colors font-semibold"
            :class="p === page ? 'bg-[#0f1f3d] text-white' : 'text-gray-600 hover:bg-gray-100'"
          >{{ p }}</button>
          <button
            @click="irPagina(page + 1)" :disabled="page === meta.last_page"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition-colors"
          >
            <ion-icon name="chevron-forward-outline" style="font-size:14px" />
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog group="encuestas" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import ConfirmDialog from 'primevue/confirmdialog'
import api from '@/api/axios'

const router  = useRouter()
const confirm = useConfirm()
const toast   = useToast()

const encuestas   = ref([])
const fuentes     = ref([])
const cargando    = ref(false)
const page        = ref(1)
const meta        = ref({ last_page: 1, from: 0, to: 0, total: 0 })
const filtros     = ref({ search: '', estado: '', fuente_id: '' })
const sort        = ref({ col: 'created_at', dir: 'desc' })
const menuAbierto = ref(null)
const fuenteOpen  = ref(false)

const paginas = computed(() => {
  const total = meta.value.last_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  return Array.from({ length: total }, (_, i) => i + 1)
    .slice(Math.max(0, page.value - 3), Math.min(total, page.value + 2))
})

function sortBy(col) {
  if (sort.value.col === col) sort.value.dir = sort.value.dir === 'asc' ? 'desc' : 'asc'
  else { sort.value.col = col; sort.value.dir = 'asc' }
  page.value = 1
  cargar()
}
function sortIcon(col) {
  if (sort.value.col !== col) return 'swap-vertical-outline'
  return sort.value.dir === 'asc' ? 'arrow-up-outline' : 'arrow-down-outline'
}

let debounceTimer = null
function debouncedCargar() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; cargar() }, 400)
}

function irPagina(p) {
  if (p < 1 || p > meta.value.last_page) return
  page.value = p
  cargar()
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/encuestas', {
      params: { page: page.value, per_page: 9, sort: sort.value.col, dir: sort.value.dir, ...filtros.value }
    })
    encuestas.value = data.data
    meta.value = data.meta ?? data
  } finally {
    cargando.value = false
  }
}

async function togglePause(enc) {
  await api.patch(`/encuestas/${enc.id}/toggle-pause`)
  enc.estado = enc.estado === 'activa' ? 'pausada' : 'activa'
}

function confirmarEliminar(enc) {
  confirm.require({
    group: 'encuestas',
    message: `¿Eliminar "${enc.nombre}"? Esta acción no se puede deshacer.`,
    header: 'Confirmar eliminación',
    acceptLabel: 'Eliminar',
    rejectLabel: 'Cancelar',
    accept: async () => {
      await api.delete(`/encuestas/${enc.id}`)
      encuestas.value = encuestas.value.filter(e => e.id !== enc.id)
      toast.add({ severity: 'success', summary: 'Encuesta eliminada', life: 3000 })
    }
  })
}

function lanzarEncuesta(enc) {
  router.push({ path: '/encuesta-activa', query: { id: enc.id } })
}

async function exportarExcel() {
  try {
    const params = Object.fromEntries(Object.entries(filtros.value).filter(([, v]) => v !== ''))
    const { data } = await api.get('/encuestas/export', { params, responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'encuestas.xlsx'
    a.click()
    URL.revokeObjectURL(url)
  } catch {}
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' })
    .replace(/\./g, '').replace(/(\d+) (\w+) (\d+)/, '$1 - $2 - $3')
}

onMounted(async () => {
  const [, f] = await Promise.all([cargar(), api.get('/fuentes')])
  fuentes.value = f.data
})
</script>
