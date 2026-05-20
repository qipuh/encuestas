<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import { useConfigStore } from '@/stores/config'

const configStore = useConfigStore()

const TABS = [
  { key: 'general',  label: 'Generales',              icon: 'settings-outline' },
  { key: 'roles',    label: 'Roles',                   icon: 'people-circle-outline' },
  // { key: 'fuentes',  label: 'Fuentes y Categorías',    icon: 'layers-outline' },
  { key: 'alertas',  label: 'Alertas',                 icon: 'notifications-outline' },
]

const PERMISOS = [
  { key: 'dashboard',        label: 'Dashboard' },
  { key: 'encuestas',        label: 'Encuestas' },
  { key: 'comentarios',      label: 'Comentarios' },
  { key: 'reportes',         label: 'Reportes' },
  { key: 'usuarios',         label: 'Usuarios' },
  { key: 'configuracion',    label: 'Configuración' },
  { key: 'exportar',         label: 'Exportar Información' },
  { key: 'lanzar_encuestas', label: 'Lanzar encuestas' },
]

const PERMISOS_DEFAULT = {
  Administrador: { dashboard:true, encuestas:true, comentarios:true, reportes:true, usuarios:true, configuracion:true, exportar:true, lanzar_encuestas:true },
  Supervisor:    { dashboard:true, encuestas:true, comentarios:true, reportes:true, usuarios:false, configuracion:false, exportar:true, lanzar_encuestas:false },
  Calidad:       { dashboard:true, encuestas:false, comentarios:true, reportes:true, usuarios:false, configuracion:false, exportar:true, lanzar_encuestas:false },
  Fuente:        { dashboard:true, encuestas:false, comentarios:true, reportes:false, usuarios:false, configuracion:false, exportar:false, lanzar_encuestas:true },
}

const HORAS_OPCIONES = Array.from({ length: 24 }, (_, i) => {
  const h = i % 12 === 0 ? 12 : i % 12
  const ampm = i < 12 ? 'AM' : 'PM'
  const label = `${String(h).padStart(2, '0')}:00 ${ampm}`
  const value = `${String(i).padStart(2, '0')}:00`
  return { value, label }
})

const PORCENTAJES = ['10%','20%','30%','40%','50%','60%','70%','80%','90%']

// ── Estado ──────────────────────────────────────────
const tabActivo = ref('general')

// General
const general = ref({
  organizacion: '', pais: '', distrito: '', telefono: '', email_responsable: '',
  cierre_sesion: '22:00', hora_limite_login: '08:00',
  umbral_timeon_bajo: 5, umbral_timeon_medio: 8, umbral_timeon_alto: 8,
  umbral_satisfaccion: '60%',
})
const guardandoGeneral = ref(false)
const logoPreview = ref(null)
const logoFile = ref(null)

// Roles
const roles = ref([])
const matrizPermisos = ref({})
const guardandoRoles = ref(false)
const MAX_ROLES = 6

// Fuentes y Categorías
const fuentes = ref([])
const categorias = ref([])
const nuevaFuente = ref('')
const nuevaCategoria = ref('')
const editandoFuente = ref(null)
const editandoCategoria = ref(null)

// Alertas
const alertas = ref([
  { emocion: 'positiva', activa: false },
  { emocion: 'neutral',  activa: true },
  { emocion: 'negativa', activa: true },
])
const guardandoAlertas = ref(false)

// ── Helpers ─────────────────────────────────────────
function emocionColor(e) {
  return { positiva: '#44b86b', neutral: '#9ba4ab', negativa: '#e8705e' }[e] ?? '#9ba4ab'
}

function seleccionarLogo(e) {
  const file = e.target.files[0]
  if (!file) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

function tienePermiso(rolId, key) {
  return matrizPermisos.value[rolId]?.[key] ?? false
}

function togglePermiso(rolId, key) {
  if (!matrizPermisos.value[rolId]) matrizPermisos.value[rolId] = {}
  matrizPermisos.value[rolId][key] = !tienePermiso(rolId, key)
}

function agregarRol() {
  if (roles.value.length >= MAX_ROLES) return
  const id = `nuevo_${Date.now()}`
  roles.value.push({ id, nombre: 'Nuevo Rol', editando: true, esNuevo: true })
  matrizPermisos.value[id] = {}
  PERMISOS.forEach(p => { matrizPermisos.value[id][p.key] = false })
}

function iniciarEdicionRol(rol) {
  roles.value.forEach(r => { r.editando = false })
  rol.editando = true
  rol._nombreTmp = rol.nombre
}

function cancelarEdicionRol(rol) {
  if (rol.esNuevo) {
    roles.value = roles.value.filter(r => r.id !== rol.id)
    delete matrizPermisos.value[rol.id]
  } else {
    rol.nombre = rol._nombreTmp ?? rol.nombre
    rol.editando = false
  }
}

function confirmarEdicionRol(rol) {
  if (!rol.nombre.trim()) return
  rol.editando = false
  rol.esNuevo = false
}

function cambiarUmbral(campo, delta) {
  const val = (general.value[campo] ?? 0) + delta
  if (val >= 0) general.value[campo] = val
}

// ── Guardar actual ───────────────────────────────────
async function guardarActual() {
  if (tabActivo.value === 'general') return guardarGeneral()
  if (tabActivo.value === 'roles') return guardarRoles()
  if (tabActivo.value === 'alertas') return guardarAlertas()
}

// ── Carga ────────────────────────────────────────────
async function cargar() {
  try {
    const [g, a, f, c, r] = await Promise.all([
      api.get('/configuracion/general').catch(() => ({ data: {} })),
      api.get('/configuracion/alertas').catch(() => ({ data: {} })),
      api.get('/configuracion/fuentes').catch(() => ({ data: [] })),
      api.get('/configuracion/categorias').catch(() => ({ data: [] })),
      api.get('/roles').catch(() => ({ data: [] })),
    ])

    if (g.data && Object.keys(g.data).length) {
      general.value = { ...general.value, ...g.data }
    }

    if (Object.keys(a.data).length) {
      alertas.value = ['positiva','neutral','negativa'].map(e => a.data[e] ?? { emocion: e, activa: false })
    }

    fuentes.value = f.data?.data ?? f.data
    categorias.value = c.data?.data ?? c.data

    const rolesApi = Array.isArray(r.data) ? r.data : (r.data.data ?? [])
    roles.value = rolesApi.map(rol => ({ ...rol, editando: false }))
    rolesApi.forEach(rol => {
      const fromApi = rol.permisos ?? {}
      const defaults = Object.keys(fromApi).length ? fromApi : (PERMISOS_DEFAULT[rol.nombre] ?? {})
      matrizPermisos.value[rol.id] = {}
      PERMISOS.forEach(p => { matrizPermisos.value[rol.id][p.key] = defaults[p.key] ?? false })
    })

    if (!roles.value.length) {
      ['Administrador','Supervisor','Calidad','Fuente'].forEach((nombre, i) => {
        const id = i + 1
        roles.value.push({ id, nombre, editando: false })
        matrizPermisos.value[id] = { ...PERMISOS_DEFAULT[nombre] }
      })
    }
  } catch {}
}

async function guardarGeneral() {
  guardandoGeneral.value = true
  try {
    const fd = new FormData()
    const campos = ['organizacion','pais','distrito','telefono','email_responsable',
      'cierre_sesion','hora_limite_login',
      'umbral_timeon_bajo','umbral_timeon_medio','umbral_timeon_alto','umbral_satisfaccion']
    campos.forEach(k => { if (general.value[k] != null) fd.append(k, general.value[k]) })
    if (logoFile.value) fd.append('logo_path', logoFile.value)
    fd.append('_method', 'PUT')
    const { data } = await api.post('/configuracion/general', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    general.value.logo_url = data.logo_url ?? general.value.logo_url
    configStore.setLogo(data.logo_url)
    logoFile.value = null
  } finally {
    guardandoGeneral.value = false
  }
}

async function guardarRoles() {
  guardandoRoles.value = true
  try {
    const payload = roles.value.map(rol => ({
      id: rol.id,
      nombre: rol.nombre,
      permisos: matrizPermisos.value[rol.id] ?? {},
    }))
    await api.put('/configuracion/roles', { roles: payload })
  } finally {
    guardandoRoles.value = false
  }
}

async function guardarAlertas() {
  guardandoAlertas.value = true
  try { await api.put('/configuracion/alertas', { alertas: alertas.value }) } finally { guardandoAlertas.value = false }
}

async function agregarFuente() {
  if (!nuevaFuente.value.trim()) return
  const { data } = await api.post('/configuracion/fuentes', { nombre: nuevaFuente.value })
  fuentes.value.push(data)
  nuevaFuente.value = ''
}
async function guardarFuente() {
  await api.put(`/configuracion/fuentes/${editandoFuente.value.id}`, { nombre: editandoFuente.value.nombre })
  const idx = fuentes.value.findIndex(f => f.id === editandoFuente.value.id)
  if (idx !== -1) fuentes.value[idx].nombre = editandoFuente.value.nombre
  editandoFuente.value = null
}
async function eliminarFuente(f) {
  await api.delete(`/configuracion/fuentes/${f.id}`)
  fuentes.value = fuentes.value.filter(x => x.id !== f.id)
}

async function agregarCategoria() {
  if (!nuevaCategoria.value.trim()) return
  const { data } = await api.post('/configuracion/categorias', { nombre: nuevaCategoria.value })
  categorias.value.push(data)
  nuevaCategoria.value = ''
}
async function guardarCategoria() {
  await api.put(`/configuracion/categorias/${editandoCategoria.value.id}`, { nombre: editandoCategoria.value.nombre })
  const idx = categorias.value.findIndex(c => c.id === editandoCategoria.value.id)
  if (idx !== -1) categorias.value[idx].nombre = editandoCategoria.value.nombre
  editandoCategoria.value = null
}
async function eliminarCategoria(c) {
  await api.delete(`/configuracion/categorias/${c.id}`)
  categorias.value = categorias.value.filter(x => x.id !== c.id)
}

onMounted(cargar)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-5">
      <h1 class="text-2xl font-black text-[#0f1f3d]">Configuración</h1>
      <p class="text-sm text-gray-400 mt-0.5">Administra las configuraciones generales del sistema</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-5">
      <button v-for="tab in TABS" :key="tab.key" @click="tabActivo = tab.key"
        class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold transition-all shadow-sm"
        :class="tabActivo === tab.key
          ? 'border-b-[3px] border-b-[#0f1f3d] text-[#0f1f3d]'
          : 'text-gray-400 hover:text-gray-600 hover:border-gray-300'">
        <ion-icon :name="tab.icon"
          :style="`font-size:16px;color:${tabActivo === tab.key ? '#2ecc71' : '#9ba4ab'}`" />
        {{ tab.label }}
      </button>
    </div>

    <!-- Layout principal: contenido + sidebar -->
    <div class="flex gap-6 items-start">

      <!-- ═══ Contenido del tab ═══ -->
      <div class="flex-1 min-w-0 space-y-4">

        <!-- ──── Tab: Generales ──── -->
        <template v-if="tabActivo === 'general'">

          <!-- Información de la organización -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="mb-5">
              <h2 class="font-bold text-[#0f1f3d] text-base">Información de la organización</h2>
              <p class="text-xs text-gray-400 mt-0.5">Información básica de la empresa</p>
            </div>

            <div class="flex gap-6">
              <!-- Campos -->
              <div class="flex-1 grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombres de la organización</label>
                  <input v-model="general.organizacion" type="text" placeholder="Procabell"
                    class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">País</label>
                  <input v-model="general.pais" type="text" placeholder="Perú"
                    class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Distrito</label>
                  <input v-model="general.distrito" type="text" placeholder="San Isidro"
                    class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono <span class="text-red-500">*</span></label>
                  <input v-model="general.telefono" type="text" placeholder="923549786"
                    class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                </div>
                <div class="col-span-2">
                  <label class="block text-sm font-semibold text-gray-700 mb-1.5">Usuario responsable <span class="text-red-500">*</span></label>
                  <input v-model="general.email_responsable" type="email" placeholder="jperez@empresa.com"
                    class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                </div>
              </div>

              <!-- Logo -->
              <div class="shrink-0 flex flex-col items-center">
                <label class="cursor-pointer block">
                  <div class="w-40 h-36 rounded-xl border border-gray-200 overflow-hidden hover:border-[#2ecc71] transition-colors relative"
                    style="background-image:repeating-linear-gradient(-45deg,#e5e7eb,#e5e7eb 1px,#f3f4f6 1px,#f3f4f6 10px)">
                    <img v-if="logoPreview || general.logo_url" :src="logoPreview || general.logo_url" class="w-full h-full object-contain p-2 relative z-10 bg-white" />
                    <template v-else>
                      <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-black text-gray-300 tracking-widest">LOGO</span>
                      </div>
                    </template>
                  </div>
                  <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="seleccionarLogo" />
                </label>
                <span class="text-xs font-semibold text-gray-700 mt-2">Subir logo</span>
                <span class="text-xs text-gray-400">JPG, PNG, max 2MB</span>
              </div>
            </div>
          </div>

          <!-- Parámetros generales -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="mb-5">
              <h2 class="font-bold text-[#0f1f3d] text-base">Parámetros generales</h2>
              <p class="text-xs text-gray-400 mt-0.5">Configuraciones generales</p>
            </div>

            <div class="space-y-4">
              <!-- Cierra sesión -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Cierra sesión automáticamente</span>
                <div class="relative">
                  <select v-model="general.cierre_sesion"
                    class="pl-4 pr-9 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm appearance-none cursor-pointer min-w-[140px]">
                    <option v-for="h in HORAS_OPCIONES" :key="h.value" :value="h.value">{{ h.label }}</option>
                  </select>
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <ion-icon name="chevron-down-outline" style="font-size:14px" />
                  </span>
                </div>
              </div>

              <!-- Hora límite LogIn -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Hora límite LogIn</span>
                <div class="relative">
                  <select v-model="general.hora_limite_login"
                    class="pl-4 pr-9 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm appearance-none cursor-pointer min-w-[140px]">
                    <option v-for="h in HORAS_OPCIONES" :key="h.value" :value="h.value">{{ h.label }}</option>
                  </select>
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <ion-icon name="chevron-down-outline" style="font-size:14px" />
                  </span>
                </div>
              </div>

              <!-- Umbral TimeOn -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Umbral TimeOn</span>
                <div class="flex items-center gap-3">
                  <!-- Bajo (< N horas) -->
                  <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                      <ion-icon name="chevron-back-outline" style="font-size:13px;color:#6b7280" />
                      <input v-model.number="general.umbral_timeon_bajo" type="number" min="0"
                        class="w-8 text-center text-sm font-semibold text-gray-700 focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-auto [&::-webkit-outer-spin-button]:appearance-auto" />
                      <span class="text-xs text-gray-400">horas</span>
                    </div>
                    <span class="text-sm font-bold text-red-500">Bajo</span>
                  </div>
                  <!-- Medio (< N horas) -->
                  <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                      <ion-icon name="chevron-back-outline" style="font-size:13px;color:#6b7280" />
                      <input v-model.number="general.umbral_timeon_medio" type="number" min="0"
                        class="w-8 text-center text-sm font-semibold text-gray-700 focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-auto [&::-webkit-outer-spin-button]:appearance-auto" />
                      <span class="text-xs text-gray-400">horas</span>
                    </div>
                    <span class="text-sm font-bold text-orange-400">Medio</span>
                  </div>
                  <!-- Alto (> N horas) -->
                  <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                      <ion-icon name="chevron-forward-outline" style="font-size:13px;color:#6b7280" />
                      <input v-model.number="general.umbral_timeon_alto" type="number" min="0"
                        class="w-8 text-center text-sm font-semibold text-gray-700 focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-auto [&::-webkit-outer-spin-button]:appearance-auto" />
                      <span class="text-xs text-gray-400">horas</span>
                    </div>
                    <span class="text-sm font-bold text-[#2ecc71]">Alto</span>
                  </div>
                </div>
              </div>

              <!-- Umbral satisfacción -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Umbral de bajo de satisfacción</span>
                <div class="relative">
                  <select v-model="general.umbral_satisfaccion"
                    class="pl-4 pr-9 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm appearance-none cursor-pointer min-w-[120px]">
                    <option v-for="p in PORCENTAJES" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                    <ion-icon name="chevron-down-outline" style="font-size:14px" />
                  </span>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- ──── Tab: Roles ──── -->
        <template v-if="tabActivo === 'roles'">
          <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
              <h2 class="font-bold text-[#0f1f3d] text-base">Configuración de roles</h2>
              <p class="text-xs text-gray-400 mt-0.5">Define los permisos para cada rol del sistema (máx {{ MAX_ROLES }} roles)</p>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="w-48 px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Permiso</th>
                    <th v-for="rol in roles" :key="rol.id" class="px-4 py-4 text-center min-w-[110px]">
                      <div class="flex flex-col items-center gap-1">
                        <div v-if="!rol.editando" class="flex items-center gap-1 group">
                          <span class="text-xs font-bold text-[#0f1f3d]">{{ rol.nombre }}</span>
                          <button @click="iniciarEdicionRol(rol)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-gray-600">
                            <ion-icon name="create-outline" style="font-size:11px" />
                          </button>
                        </div>
                        <div v-else class="flex items-center gap-1">
                          <input v-model="rol.nombre" type="text"
                            class="w-24 px-2 py-0.5 text-xs border border-[#2ecc71] rounded-lg focus:outline-none text-center"
                            @keyup.enter="confirmarEdicionRol(rol)"
                            @keyup.escape="cancelarEdicionRol(rol)" />
                          <button @click="confirmarEdicionRol(rol)" class="text-[#2ecc71]">
                            <ion-icon name="checkmark-outline" style="font-size:13px" />
                          </button>
                          <button @click="cancelarEdicionRol(rol)" class="text-gray-400">
                            <ion-icon name="close-outline" style="font-size:13px" />
                          </button>
                        </div>
                      </div>
                    </th>
                    <th v-for="i in Math.max(0, MAX_ROLES - roles.length)" :key="`nuevo-${i}`" class="px-4 py-4 text-center min-w-[110px]">
                      <button v-if="i === 1" @click="agregarRol"
                        class="text-xs font-bold text-[#2ecc71] hover:text-[#27ae60] flex items-center gap-0.5 mx-auto transition-colors">
                        <ion-icon name="add-outline" style="font-size:14px" />
                        Nuevo Rol
                      </button>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="(permiso, i) in PERMISOS" :key="permiso.key"
                    class="hover:bg-blue-50/20 transition-colors"
                    :class="i % 2 === 1 ? 'bg-gray-50/40' : ''">
                    <td class="px-6 py-3.5 text-sm font-medium text-gray-700">{{ permiso.label }}</td>
                    <td v-for="rol in roles" :key="rol.id" class="px-4 py-3.5 text-center">
                      <button @click="togglePermiso(rol.id, permiso.key)"
                        class="w-7 h-7 rounded-lg inline-flex items-center justify-center transition-all"
                        :class="tienePermiso(rol.id, permiso.key)
                          ? 'bg-[#2ecc71] hover:bg-[#27ae60] text-white'
                          : 'bg-gray-100 hover:bg-gray-200 text-gray-300'">
                        <ion-icon name="checkmark-outline" style="font-size:14px" />
                      </button>
                    </td>
                    <td v-for="i in Math.max(0, MAX_ROLES - roles.length)" :key="`v-${i}`" class="px-4 py-3.5">
                      <div class="w-7 h-7 rounded-lg bg-gray-100 mx-auto" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <!-- Tab: Fuentes y Categorías (desactivado) -->
        <template v-if="false">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow-sm p-6">
              <div class="mb-4">
                <h2 class="font-bold text-[#0f1f3d] text-base">Fuentes</h2>
                <p class="text-xs text-gray-400 mt-0.5">Puntos de atención o sucursales</p>
              </div>
              <div class="flex gap-2 mb-4">
                <input v-model="nuevaFuente" type="text" placeholder="Nombre de la fuente..."
                  class="flex-1 px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm"
                  @keyup.enter="agregarFuente" />
                <button @click="agregarFuente" :disabled="!nuevaFuente.trim()"
                  class="px-4 py-2 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-50 transition-colors flex items-center gap-1">
                  <ion-icon name="add-outline" style="font-size:16px" />
                  Agregar
                </button>
              </div>
              <div class="divide-y divide-gray-50">
                <div v-for="f in fuentes" :key="f.id" class="flex items-center gap-2 py-2.5 group">
                  <ion-icon name="home-outline" style="font-size:14px;color:#94a3b8" />
                  <span v-if="editandoFuente?.id !== f.id" class="flex-1 text-sm text-gray-700">{{ f.nombre }}</span>
                  <input v-else v-model="editandoFuente.nombre" type="text"
                    class="flex-1 px-2 py-1 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
                  <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button v-if="editandoFuente?.id !== f.id" @click="editandoFuente = { ...f }" class="p-1 hover:bg-gray-100 rounded-lg">
                      <ion-icon name="create-outline" style="font-size:13px;color:#6b7280" />
                    </button>
                    <button v-else @click="guardarFuente" class="p-1 hover:bg-green-50 rounded-lg">
                      <ion-icon name="checkmark-outline" style="font-size:13px;color:#2ecc71" />
                    </button>
                    <button @click="eliminarFuente(f)" class="p-1 hover:bg-red-50 rounded-lg">
                      <ion-icon name="trash-outline" style="font-size:13px;color:#ef4444" />
                    </button>
                  </div>
                </div>
                <div v-if="!fuentes.length" class="py-8 text-center text-gray-400 text-sm">
                  <ion-icon name="home-outline" style="font-size:28px;display:block;margin:0 auto 8px" />
                  Sin fuentes registradas
                </div>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-6">
              <div class="mb-4">
                <h2 class="font-bold text-[#0f1f3d] text-base">Categorías</h2>
                <p class="text-xs text-gray-400 mt-0.5">Categorías para clasificar comentarios</p>
              </div>
              <div class="flex gap-2 mb-4">
                <input v-model="nuevaCategoria" type="text" placeholder="Nombre de la categoría..."
                  class="flex-1 px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm"
                  @keyup.enter="agregarCategoria" />
                <button @click="agregarCategoria" :disabled="!nuevaCategoria.trim()"
                  class="px-4 py-2 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-50 transition-colors flex items-center gap-1">
                  <ion-icon name="add-outline" style="font-size:16px" />
                  Agregar
                </button>
              </div>
              <div class="divide-y divide-gray-50">
                <div v-for="c in categorias" :key="c.id" class="flex items-center gap-2 py-2.5 group">
                  <ion-icon name="tag-outline" style="font-size:14px;color:#94a3b8" />
                  <span v-if="editandoCategoria?.id !== c.id" class="flex-1 text-sm text-gray-700">{{ c.nombre }}</span>
                  <input v-else v-model="editandoCategoria.nombre" type="text"
                    class="flex-1 px-2 py-1 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
                  <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button v-if="editandoCategoria?.id !== c.id" @click="editandoCategoria = { ...c }" class="p-1 hover:bg-gray-100 rounded-lg">
                      <ion-icon name="create-outline" style="font-size:13px;color:#6b7280" />
                    </button>
                    <button v-else @click="guardarCategoria" class="p-1 hover:bg-green-50 rounded-lg">
                      <ion-icon name="checkmark-outline" style="font-size:13px;color:#2ecc71" />
                    </button>
                    <button @click="eliminarCategoria(c)" class="p-1 hover:bg-red-50 rounded-lg">
                      <ion-icon name="trash-outline" style="font-size:13px;color:#ef4444" />
                    </button>
                  </div>
                </div>
                <div v-if="!categorias.length" class="py-8 text-center text-gray-400 text-sm">
                  <ion-icon name="tag-outline" style="font-size:28px;display:block;margin:0 auto 8px" />
                  Sin categorías registradas
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- ──── Tab: Alertas ──── -->
        <template v-if="tabActivo === 'alertas'">
          <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
              <h2 class="font-bold text-[#0f1f3d] text-base">Alertas de emoción</h2>
              <p class="text-xs text-gray-400 mt-0.5">Configura qué emociones disparan alertas por email</p>
            </div>
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Emoción</th>
                  <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Canal</th>
                  <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Destinatarios</th>
                  <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Estado</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="a in alertas" :key="a.emocion" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0"
                        :style="{ backgroundColor: emocionColor(a.emocion) }">
                        <svg v-if="a.emocion === 'positiva'" viewBox="0 0 24 24" width="16" height="16" fill="none">
                          <circle cx="9" cy="10" r="1.2" fill="white"/>
                          <circle cx="15" cy="10" r="1.2" fill="white"/>
                          <path d="M8 14.5 Q12 18 16 14.5" stroke="white" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        </svg>
                        <svg v-else-if="a.emocion === 'neutral'" viewBox="0 0 24 24" width="16" height="16" fill="none">
                          <circle cx="9" cy="10" r="1.2" fill="white"/>
                          <circle cx="15" cy="10" r="1.2" fill="white"/>
                          <line x1="8.5" y1="15" x2="15.5" y2="15" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none">
                          <circle cx="9" cy="10" r="1.2" fill="white"/>
                          <circle cx="15" cy="10" r="1.2" fill="white"/>
                          <path d="M8 16.5 Q12 13 16 16.5" stroke="white" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        </svg>
                      </div>
                      <span class="text-sm font-semibold text-gray-700 capitalize">{{ a.emocion }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                      <ion-icon name="mail-outline" style="font-size:14px" />
                      Email
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-xs text-gray-500">Todos los administradores</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button @click="a.activa = !a.activa"
                      class="w-11 h-6 rounded-full transition-colors relative shrink-0"
                      :class="a.activa ? 'bg-[#2ecc71]' : 'bg-gray-300'">
                      <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-transform shadow-sm"
                        :class="a.activa ? 'translate-x-5' : 'translate-x-1'" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

      </div>

      <!-- ═══ Sidebar ═══ -->
      <div class="w-56 shrink-0 sticky top-6 space-y-2">
        <button @click="guardarActual"
          :disabled="guardandoGeneral || guardandoRoles || guardandoAlertas"
          class="w-full py-3 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-60 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <div v-if="guardandoGeneral || guardandoRoles || guardandoAlertas"
            class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          <ion-icon v-else name="save-outline" style="font-size:17px" />
          {{ (guardandoGeneral || guardandoRoles || guardandoAlertas) ? 'Guardando...' : 'Guardar' }}
        </button>
        <button @click="$router.back()"
          class="w-full py-3 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <ion-icon name="arrow-back-outline" style="font-size:16px" />
          Regresar
        </button>
      </div>

    </div>
  </div>
</template>
