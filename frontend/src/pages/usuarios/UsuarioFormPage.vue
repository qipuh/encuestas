<template>
  <div>
    <!-- Breadcrumb + título -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
        <RouterLink to="/app/usuarios" class="hover:text-[#0f1f3d] transition-colors">Usuarios</RouterLink>
        <ion-icon name="chevron-forward-outline" style="font-size:13px" />
        <span class="text-[#0f1f3d] font-semibold">Crear / Editar usuario</span>
      </div>
      <p class="text-sm text-gray-400">Completa la información de registro en el sistema</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6 items-start">
      <!-- Columna principal -->
      <div class="flex-1 min-w-0 space-y-4">

        <!-- Sección 1: Perfil del usuario -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="mb-5">
            <h2 class="font-bold text-[#0f1f3d] text-base">Perfil del usuario!</h2>
            <p class="text-xs text-gray-400 mt-0.5">Información básica del usuario que accederá al sistema</p>
          </div>

          <div class="flex items-start gap-6">
            <!-- Avatar -->
            <div class="flex flex-col items-center gap-1.5 shrink-0">
              <div class="relative">
                <label class="cursor-pointer block">
                  <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center border-2 border-gray-200 hover:border-[#2ecc71] transition-colors">
                    <img v-if="preview || form.foto_url" :src="preview || form.foto_url" class="w-full h-full object-cover" />
                    <ion-icon v-else name="person" style="font-size:40px;color:#94a3b8" />
                  </div>
                  <div class="absolute bottom-1.5 right-1.5 w-7 h-7 bg-[#2ecc71] rounded-full flex items-center justify-center shadow-sm">
                    <ion-icon name="camera-outline" style="font-size:14px;color:white" />
                  </div>
                  <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="seleccionarFoto" />
                </label>
              </div>
              <span class="text-xs font-semibold text-gray-600">Cambiar foto</span>
              <span class="text-xs text-gray-400">JPG, PNG, max 2MB</span>
            </div>

            <!-- Campos -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombres <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" placeholder="Juan Carlos"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Apellidos <span class="text-red-500">*</span></label>
                <input v-model="form.apellidos" type="text" placeholder="Pérez García"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Correo electrónico <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email" placeholder="jperez@empresa.com"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono <span class="text-red-500">*</span></label>
                <input v-model="form.telefono" type="text" placeholder="923549786"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">DNI <span class="text-red-500">*</span></label>
                <input v-model="form.dni" type="text" maxlength="20" placeholder="46987561"
                  class="w-full px-4 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sección 2: Acceso y permisos -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="mb-5">
            <h2 class="font-bold text-[#0f1f3d] text-base">Acceso y permisos</h2>
            <p class="text-xs text-gray-400 mt-0.5">Define los parámetros de acceso y permisos</p>
          </div>

          <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 mb-4">
            <!-- Rol dropdown -->
            <div class="flex-1 w-full">
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Rol <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                  <ion-icon name="person-outline" style="font-size:16px" />
                </span>
                <select v-model="form.role_id"
                  class="w-full pl-9 pr-10 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm appearance-none">
                  <option value="">Seleccionar rol</option>
                  <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.nombre }}</option>
                </select>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                  <ion-icon name="chevron-down-outline" style="font-size:14px" />
                </span>
              </div>
            </div>

            <!-- Fuentes dropdown -->
            <div class="flex-1 w-full">
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fuentes <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                  <ion-icon name="home-outline" style="font-size:16px" />
                </span>
                <button type="button" @click.stop="dropFuentes = !dropFuentes"
                  class="w-full pl-9 pr-10 py-2.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm text-left"
                  :class="dropFuentes ? 'border-[#2ecc71]' : ''">
                  <span :class="form.fuentes.length ? 'text-gray-700' : 'text-gray-400'">
                    {{ form.fuentes.length ? `${form.fuentes.length} fuente(s) seleccionada(s)` : 'Seleccionar fuentes' }}
                  </span>
                </button>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                  <ion-icon name="chevron-down-outline" style="font-size:14px" />
                </span>
                <div v-if="dropFuentes"
                  class="absolute left-0 top-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg z-20 w-full py-1 max-h-48 overflow-y-auto">
                  <label v-for="f in fuentes" :key="f.id"
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" :value="f.id" v-model="form.fuentes" class="rounded accent-[#2ecc71]" />
                    <ion-icon name="home-outline" style="font-size:13px;color:#9ba4ab" />
                    {{ f.nombre }}
                  </label>
                  <p v-if="fuentes.length === 0" class="px-4 py-2 text-xs text-gray-400">Sin fuentes disponibles</p>
                </div>
              </div>
            </div>

            <!-- Estado toggle -->
            <div class="shrink-0 pb-0.5">
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estado</label>
              <div class="flex items-center gap-2.5">
                <button type="button" @click="form.habilitado = !form.habilitado"
                  class="w-12 h-6 rounded-full transition-colors relative shrink-0"
                  :class="form.habilitado ? 'bg-[#2ecc71]' : 'bg-gray-300'">
                  <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-transform shadow-sm"
                    :class="form.habilitado ? 'translate-x-6' : 'translate-x-1'"></div>
                </button>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="form.habilitado ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                  {{ form.habilitado ? 'Habilitado' : 'Deshabilitado' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Info box -->
          <div class="flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700">
            <ion-icon name="information-circle-outline" style="font-size:18px;color:#3b82f6;flex-shrink:0" />
            Los permisos de cada Rol se pueden modificar en la sección de configuración.
          </div>
        </div>

        <!-- Sección 3: Contraseña -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="mb-5">
            <h2 class="font-bold text-[#0f1f3d] text-base">Contraseña</h2>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ isEdit ? 'Dejar vacío para no cambiar la contraseña' : 'Define la contraseña de acceso' }}
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                {{ isEdit ? 'Nueva contraseña' : 'Contraseña' }} <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                  class="w-full px-4 py-2.5 pr-11 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71] shadow-sm" />
                <button type="button" @click="showPass = !showPass"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <ion-icon :name="showPass ? 'eye-off-outline' : 'eye-outline'" style="font-size:18px" />
                </button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Confirmar contraseña <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input v-model="form.password_confirmation" :type="showPassConf ? 'text' : 'password'"
                  class="w-full px-4 py-2.5 pr-11 text-sm bg-white border rounded-xl focus:outline-none shadow-sm transition-colors"
                  :class="passNoCoincide ? 'border-red-300 focus:border-red-400' : 'border-gray-200 focus:border-[#2ecc71]'" />
                <button type="button" @click="showPassConf = !showPassConf"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <ion-icon :name="showPassConf ? 'eye-off-outline' : 'eye-outline'" style="font-size:18px" />
                </button>
              </div>
              <div class="mt-1.5 flex items-center gap-1.5 h-4">
                <template v-if="passCoincide">
                  <ion-icon name="checkmark-circle-outline" style="font-size:14px;color:#2ecc71" />
                  <span class="text-xs text-[#2ecc71] font-semibold">Las contraseñas coinciden</span>
                </template>
                <template v-else-if="passNoCoincide">
                  <ion-icon name="close-circle-outline" style="font-size:14px;color:#ef4444" />
                  <span class="text-xs text-red-500">Las contraseñas no coinciden</span>
                </template>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Sidebar derecho -->
      <div class="w-full md:w-56 shrink-0 space-y-2 md:sticky top-6">
        <button type="button" @click="guardar" :disabled="guardando || passNoCoincide"
          class="w-full py-3 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <div v-if="guardando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <ion-icon v-else name="save-outline" style="font-size:17px" />
          {{ guardando ? 'Guardando...' : 'Guardar' }}
        </button>
        <RouterLink to="/app/usuarios"
          class="w-full py-3 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
          <ion-icon name="arrow-back-outline" style="font-size:16px" />
          Regresar
        </RouterLink>
        <p v-if="error" class="text-red-500 text-xs bg-red-50 border border-red-100 rounded-xl p-3 mt-2">{{ error }}</p>
      </div>
    </div>

    <!-- Backdrop fuentes dropdown -->
    <div v-if="dropFuentes" class="fixed inset-0 z-10" @click="dropFuentes = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const roles = ref([])
const fuentes = ref([])
const guardando = ref(false)
const error = ref('')
const preview = ref(null)
const fotoFile = ref(null)
const showPass = ref(false)
const showPassConf = ref(false)
const dropFuentes = ref(false)

const form = ref({
  name: '', apellidos: '', email: '', telefono: '', dni: '',
  password: '', password_confirmation: '', role_id: '', fuentes: [], habilitado: true, foto_url: null,
})

const passNoCoincide = computed(() =>
  !!(form.value.password_confirmation && form.value.password !== form.value.password_confirmation)
)

const passCoincide = computed(() =>
  !!(form.value.password && form.value.password_confirmation && form.value.password === form.value.password_confirmation)
)

function seleccionarFoto(e) {
  const file = e.target.files[0]
  if (!file) return
  fotoFile.value = file
  preview.value = URL.createObjectURL(file)
}

async function cargarDatos() {
  const [r, f] = await Promise.all([api.get('/roles'), api.get('/fuentes')])
  roles.value = r.data?.data ?? r.data
  fuentes.value = f.data?.data ?? f.data

  if (isEdit.value) {
    const { data } = await api.get(`/usuarios/${route.params.id}`)
    form.value = {
      name: data.name,
      apellidos: data.apellidos ?? '',
      email: data.email,
      telefono: data.telefono ?? '',
      dni: data.dni ?? '',
      password: '',
      password_confirmation: '',
      role_id: data.rol?.id ?? '',
      fuentes: data.fuentes?.map(f => f.id) ?? [],
      habilitado: data.habilitado,
      foto_url: data.foto_url ?? null,
    }
  }
}

async function guardar() {
  if (passNoCoincide.value) return
  guardando.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('name', form.value.name)
    fd.append('apellidos', form.value.apellidos)
    fd.append('email', form.value.email)
    fd.append('telefono', form.value.telefono)
    fd.append('dni', form.value.dni)
    fd.append('role_id', form.value.role_id)
    fd.append('habilitado', form.value.habilitado ? '1' : '0')
    form.value.fuentes.forEach(id => fd.append('fuentes[]', id))
    if (form.value.password) fd.append('password', form.value.password)
    if (fotoFile.value) fd.append('foto', fotoFile.value)

    const config = { headers: { 'Content-Type': 'multipart/form-data' } }

    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/usuarios/${route.params.id}`, fd, config)
    } else {
      await api.post('/usuarios', fd, config)
    }
    router.push('/app/usuarios')
  } catch (err) {
    error.value = err?.response?.data?.message
      || Object.values(err?.response?.data?.errors ?? {})[0]?.[0]
      || 'Error al guardar.'
  } finally {
    guardando.value = false
  }
}

onMounted(cargarDatos)
</script>
