<template>
  <div>
    <!-- Header -->
    <div class="mb-5">
      <h1 class="text-2xl font-black text-[#0f1f3d]">Usuarios</h1>
    </div>

    <!-- Card principal -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <!-- Card header -->
      <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-bold text-[#0f1f3d]">Centro de usuarios</h2>
        <div class="flex items-center gap-2 flex-wrap">
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <ion-icon name="search-outline" style="font-size:14px" />
            </span>
            <input v-model="filtros.search" type="text" placeholder="Buscar..."
              class="pl-8 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71] w-48"
              @input="debouncedCargar" />
          </div>
          <button class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
            <ion-icon name="options-outline" style="font-size:18px;color:#6b7280" />
          </button>
          <RouterLink to="/app/usuarios/crear"
            class="flex items-center gap-1.5 px-4 py-1.5 bg-[#2ecc71] text-white text-sm font-bold rounded-lg hover:bg-[#27ae60] transition-colors">
            <ion-icon name="add-outline" style="font-size:16px" />
            Crear usuario
          </RouterLink>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="cargando" class="flex items-center justify-center py-16">
        <div class="w-7 h-7 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="usuarios.length === 0" class="flex flex-col items-center py-16 text-gray-400">
        <ion-icon name="people-outline" style="font-size:40px" />
        <p class="mt-3 text-sm">No hay usuarios que mostrar</p>
      </div>

      <!-- Tabla -->
      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm min-w-[700px]">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Usuario <ion-icon :name="sortIcon('name')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('rol')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Rol <ion-icon :name="sortIcon('rol')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('created_at')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Fecha creación <ion-icon :name="sortIcon('created_at')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('ultima_actividad')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Última actividad <ion-icon :name="sortIcon('ultima_actividad')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              <button @click="sortBy('conexion')" class="inline-flex items-center gap-1 hover:text-gray-600 transition-colors">
                Conexión <ion-icon :name="sortIcon('conexion')" style="font-size:12px" />
              </button>
            </th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="(u, i) in usuarios" :key="u.id"
            class="hover:bg-blue-50/30 transition-colors group"
            :class="i % 2 === 1 ? 'bg-gray-50/40' : ''">
            <!-- Usuario -->
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#0f1f3d]/10 flex items-center justify-center overflow-hidden shrink-0">
                  <img v-if="u.foto_url" :src="u.foto_url" class="w-full h-full object-cover" />
                  <ion-icon v-else name="person" style="font-size:16px;color:#94a3b8" />
                </div>
                <div>
                  <p class="font-semibold text-[#0f1f3d] text-sm leading-tight">{{ u.name }}{{ u.apellidos ? ' ' + u.apellidos : '' }}</p>
                  <p class="text-xs text-gray-400">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <!-- Rol -->
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="rolClass(u.rol?.nombre)">
                {{ u.rol?.nombre ?? '—' }}
              </span>
            </td>
            <!-- Fecha creación -->
            <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ formatFecha(u.created_at) }}</td>
            <!-- Última actividad -->
            <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ tiempoRelativo(u.ultima_actividad) }}</td>
            <!-- Conexión -->
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                :class="u.en_linea ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500'">
                {{ u.en_linea ? 'En línea' : 'Desconectado' }}
              </span>
            </td>
            <!-- Acciones -->
            <td class="px-5 py-3">
              <div class="flex items-center gap-1 relative">
                <button @click="toggleHabilitado(u)"
                  class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                  :title="u.habilitado ? 'Deshabilitar' : 'Habilitar'">
                  <ion-icon :name="u.habilitado ? 'lock-closed-outline' : 'lock-open-outline'" style="font-size:16px;color:#6b7280" />
                </button>
                <button @click="confirmarEliminar(u)" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                  <ion-icon name="close-outline" style="font-size:16px;color:#6b7280" />
                </button>
                <RouterLink :to="`/app/usuarios/${u.id}/editar`" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                  <ion-icon name="create-outline" style="font-size:16px;color:#6b7280" />
                </RouterLink>
                <div class="relative">
                  <button @click.stop="toggleMenu(u.id)" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <ion-icon name="ellipsis-vertical-outline" style="font-size:16px;color:#6b7280" />
                  </button>
                  <div v-if="menuAbierto === u.id"
                    class="absolute right-0 top-8 bg-white border border-gray-100 rounded-xl shadow-lg z-20 min-w-[140px] py-1">
                    <RouterLink :to="`/app/usuarios/${u.id}`"
                      class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition-colors">
                      <ion-icon name="eye-outline" style="font-size:14px;color:#6b7280" />
                      Ver más
                    </RouterLink>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>

      <!-- Footer paginación -->
      <div v-if="!cargando && usuarios.length > 0" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
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

    <!-- Backdrop -->
    <div v-if="menuAbierto !== null" class="fixed inset-0 z-10" @click="menuAbierto = null" />

    <ConfirmDialog group="usuarios" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import ConfirmDialog from 'primevue/confirmdialog'
import api from '@/api/axios'

const confirm = useConfirm()
const toast = useToast()

const usuarios = ref([])
const cargando = ref(false)
const page = ref(1)
const meta = ref({ last_page: 1, from: 0, to: 0, total: 0 })
const filtros = ref({ search: '' })
const menuAbierto = ref(null)
const sortCol = ref('created_at')
const sortDir = ref('desc')

const paginas = computed(() => {
  const total = meta.value.last_page
  if (!total || total <= 1) return []
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const cur = page.value
  const pages = [1]
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

function formatFecha(d) {
  if (!d) return '—'
  const fecha = new Date(d)
  const dia = String(fecha.getDate()).padStart(2, '0')
  const mes = fecha.toLocaleDateString('es-PE', { month: 'short' })
  const anio = fecha.getFullYear()
  return `${dia} - ${mes} - ${anio}`
}

function tiempoRelativo(d) {
  if (!d) return 'Nunca'
  const ahora = new Date()
  const fecha = new Date(d)
  const diff = Math.floor((ahora - fecha) / 1000)
  if (diff < 60) return 'Ahora mismo'
  if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`
  const horas = Math.floor(diff / 3600)
  if (horas < 24) {
    const hora = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
    return `Hoy, ${hora}`
  }
  const dias = Math.floor(diff / 86400)
  if (dias === 1) {
    const hora = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
    return `Ayer, ${hora}`
  }
  if (dias < 7) return `Hace ${dias} días`
  return formatFecha(d)
}

function rolClass(nombre) {
  const n = (nombre ?? '').toLowerCase()
  if (n.includes('admin')) return 'bg-blue-100 text-blue-700'
  if (n.includes('supervisor')) return 'bg-gray-100 text-gray-600'
  if (n.includes('calidad')) return 'bg-green-100 text-green-700'
  if (n.includes('fuente')) return 'bg-orange-100 text-orange-600'
  return 'bg-gray-100 text-gray-600'
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/usuarios', {
      params: {
        page: page.value,
        per_page: 9,
        sort: sortCol.value,
        dir: sortDir.value,
        ...Object.fromEntries(Object.entries(filtros.value).filter(([, v]) => v !== ''))
      }
    })
    usuarios.value = data.data
    meta.value = data.meta ?? data
  } finally {
    cargando.value = false
  }
}

async function toggleHabilitado(u) {
  await api.patch(`/usuarios/${u.id}/toggle-habilitado`)
  u.habilitado = !u.habilitado
}

function confirmarEliminar(u) {
  confirm.require({
    group: 'usuarios',
    message: `¿Eliminar al usuario "${u.name}"? Esta acción no se puede deshacer.`,
    header: 'Confirmar eliminación',
    acceptLabel: 'Eliminar',
    rejectLabel: 'Cancelar',
    accept: async () => {
      await api.delete(`/usuarios/${u.id}`)
      usuarios.value = usuarios.value.filter(x => x.id !== u.id)
      toast.add({ severity: 'success', summary: 'Usuario eliminado', life: 3000 })
    }
  })
}

onMounted(cargar)
</script>
