<template>
  <div class="flex gap-6">
    <!-- Columna principal -->
    <div class="flex-1 min-w-0 space-y-4">
      <!-- Sección 1: Perfil -->
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-7 h-7 bg-[#0f1f3d] text-white text-xs font-black rounded-full flex items-center justify-center shrink-0">1</div>
          <h2 class="font-bold text-[#0f1f3d]">Perfil</h2>
        </div>

        <!-- Avatar -->
        <div class="flex items-center gap-5 mb-5">
          <div class="relative">
            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#2ecc71] bg-gray-100 flex items-center justify-center">
              <img v-if="preview || perfil.foto_url" :src="preview || perfil.foto_url" class="w-full h-full object-cover" />
              <ion-icon v-else name="person" style="font-size:36px;color:#94a3b8" />
            </div>
            <label class="absolute -bottom-1 -right-1 w-7 h-7 bg-[#2ecc71] rounded-full flex items-center justify-center cursor-pointer hover:bg-[#27ae60] transition-colors">
              <ion-icon name="camera-outline" style="font-size:14px;color:white" />
              <input type="file" accept="image/*" class="hidden" @change="seleccionarFoto" />
            </label>
          </div>
          <div>
            <p class="text-xl font-black text-[#0f1f3d]">{{ perfil.name }} {{ perfil.apellidos }}</p>
            <p class="text-sm text-[#2ecc71] font-medium">{{ perfil.rol?.nombre }}</p>
            <p class="text-sm text-gray-500 mt-0.5">{{ perfil.email }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombres</label>
            <input v-model="form.name" type="text"
              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Apellidos</label>
            <input v-model="form.apellidos" type="text"
              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono</label>
            <input v-model="form.telefono" type="text"
              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">DNI</label>
            <input v-model="form.dni" type="text"
              class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
          </div>
        </div>
      </div>

      <!-- Sección 2: Acceso y permisos (solo lectura) -->
      <div class="bg-white rounded-2xl shadow-sm p-6 opacity-70">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-7 h-7 bg-gray-300 text-white text-xs font-black rounded-full flex items-center justify-center shrink-0">2</div>
          <h2 class="font-bold text-gray-400">Acceso y permisos</h2>
          <span class="text-xs text-gray-400 ml-auto flex items-center gap-1">
            <ion-icon name="lock-closed-outline" style="font-size:12px" />
            Solo lectura
          </span>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-semibold text-gray-400 mb-1.5">Rol</label>
            <div class="w-full px-4 py-2.5 text-sm bg-gray-100 border border-gray-200 rounded-xl text-gray-500">
              {{ perfil.rol?.nombre ?? '—' }}
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <label class="text-sm font-semibold text-gray-400">Estado</label>
          <div class="w-11 h-6 rounded-full bg-[#2ecc71] relative pointer-events-none">
            <div class="w-4 h-4 bg-white rounded-full absolute top-1 translate-x-5 shadow-sm"></div>
          </div>
          <span class="text-sm text-green-600">Habilitado</span>
        </div>
      </div>

      <!-- Sección 3: Notificaciones Push -->
      <div v-if="pushSupported" class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-7 h-7 bg-[#0f1f3d] text-white text-xs font-black rounded-full flex items-center justify-center shrink-0">3</div>
          <h2 class="font-bold text-[#0f1f3d]">Notificaciones Push</h2>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-semibold text-gray-700">Recibir notificaciones</p>
            <p class="text-xs text-gray-500 mt-0.5">Recibe alertas de nuevos comentarios aunque la app esté cerrada</p>
          </div>
          <button @click="togglePush" :disabled="pushLoading"
            class="relative w-12 h-6 rounded-full transition-colors duration-200 focus:outline-none disabled:opacity-50"
            :class="pushSubscribed ? 'bg-[#2ecc71]' : 'bg-gray-300'">
            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-200"
              :class="pushSubscribed ? 'left-7' : 'left-1'"></span>
          </button>
        </div>
        <p v-if="pushError" class="text-xs text-red-500 mt-2">{{ pushError }}</p>
      </div>

      <!-- Sección 4: Contraseña -->
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-7 h-7 bg-[#0f1f3d] text-white text-xs font-black rounded-full flex items-center justify-center shrink-0">4</div>
          <h2 class="font-bold text-[#0f1f3d]">Cambiar contraseña</h2>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2 md:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña actual</label>
            <div class="relative">
              <input v-model="pass.actual" :type="showPass.actual ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
              <button type="button" @click="showPass.actual = !showPass.actual"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <ion-icon :name="showPass.actual ? 'eye-off-outline' : 'eye-outline'" style="font-size:16px" />
              </button>
            </div>
          </div>
          <div></div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva contraseña</label>
            <div class="relative">
              <input v-model="pass.nuevo" :type="showPass.nuevo ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-[#2ecc71]" />
              <button type="button" @click="showPass.nuevo = !showPass.nuevo"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <ion-icon :name="showPass.nuevo ? 'eye-off-outline' : 'eye-outline'" style="font-size:16px" />
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar contraseña</label>
            <div class="relative">
              <input v-model="pass.confirmation" :type="showPass.conf ? 'text' : 'password'"
                class="w-full px-4 py-2.5 pr-10 text-sm bg-gray-50 border rounded-xl focus:outline-none focus:border-[#2ecc71]"
                :class="passNoCoincide ? 'border-red-300 bg-red-50' : 'border-gray-200'" />
              <button type="button" @click="showPass.conf = !showPass.conf"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <ion-icon :name="showPass.conf ? 'eye-off-outline' : 'eye-outline'" style="font-size:16px" />
              </button>
            </div>
            <p v-if="passNoCoincide" class="text-xs text-red-500 mt-1">Las contraseñas no coinciden</p>
          </div>
        </div>

        <p v-if="mensajePass" class="text-sm mt-3" :class="errorPass ? 'text-red-500' : 'text-green-600'">{{ mensajePass }}</p>
      </div>
    </div>

    <!-- Sidebar derecho -->
    <div class="w-64 shrink-0 space-y-3">
      <div class="bg-white rounded-2xl shadow-sm p-4 space-y-2">
        <button @click="guardarPerfil" :disabled="guardandoPerfil"
          class="w-full py-2.5 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-60 transition-colors flex items-center justify-center gap-2">
          <div v-if="guardandoPerfil" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <ion-icon v-else name="save-outline" style="font-size:16px" />
          {{ guardandoPerfil ? 'Guardando...' : 'Guardar cambios' }}
        </button>

        <button @click="cambiarPassword" :disabled="guardandoPass || passNoCoincide || !pass.actual"
          class="w-full py-2.5 bg-[#0f1f3d] text-white text-sm font-bold rounded-xl hover:bg-[#152b52] disabled:opacity-60 transition-colors flex items-center justify-center gap-2">
          <div v-if="guardandoPass" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <ion-icon v-else name="lock-closed-outline" style="font-size:15px" />
          {{ guardandoPass ? 'Cambiando...' : 'Cambiar contraseña' }}
        </button>
      </div>

      <p v-if="mensajePerfil" class="text-sm px-3" :class="errorPerfil ? 'text-red-500' : 'text-green-600'">{{ mensajePerfil }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { usePushNotifications } from '@/composables/usePushNotifications'

const { isSupported: pushSupported, isSubscribed: pushSubscribed, isLoading: pushLoading, subscribe: pushSubscribe, unsubscribe: pushUnsubscribe, syncSubscriptionState } = usePushNotifications()
const pushError = ref('')

async function togglePush() {
  pushError.value = ''
  try {
    if (pushSubscribed.value) {
      await pushUnsubscribe()
    } else {
      await pushSubscribe()
      if (!pushSubscribed.value) pushError.value = 'Activa los permisos de notificación en tu navegador.'
    }
  } catch {
    pushError.value = 'Error al cambiar las notificaciones.'
  }
}

const auth = useAuthStore()
const perfil = ref({})
const preview = ref(null)
const fotoFile = ref(null)

const form = ref({ name: '', apellidos: '', telefono: '', dni: '' })
const pass = ref({ actual: '', nuevo: '', confirmation: '' })
const showPass = ref({ actual: false, nuevo: false, conf: false })

const guardandoPerfil = ref(false)
const guardandoPass = ref(false)
const mensajePerfil = ref('')
const errorPerfil = ref(false)
const mensajePass = ref('')
const errorPass = ref(false)

const passNoCoincide = computed(() => pass.value.confirmation && pass.value.nuevo !== pass.value.confirmation)

async function cargar() {
  const { data } = await api.get('/mi-cuenta')
  perfil.value = data
  form.value = { name: data.name, apellidos: data.apellidos ?? '', telefono: data.telefono ?? '', dni: data.dni ?? '' }
}

function seleccionarFoto(e) {
  const file = e.target.files[0]
  if (!file) return
  fotoFile.value = file
  preview.value = URL.createObjectURL(file)
}

async function guardarPerfil() {
  guardandoPerfil.value = true
  mensajePerfil.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => fd.append(k, v ?? ''))
    if (fotoFile.value) fd.append('foto', fotoFile.value)

    const { data } = await api.post('/mi-cuenta', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    perfil.value = data
    auth.user = { ...auth.user, ...data }
    mensajePerfil.value = 'Perfil actualizado correctamente.'
    errorPerfil.value = false
    fotoFile.value = null
  } catch (err) {
    mensajePerfil.value = err?.response?.data?.message || 'Error al actualizar el perfil.'
    errorPerfil.value = true
  } finally {
    guardandoPerfil.value = false
  }
}

async function cambiarPassword() {
  if (passNoCoincide.value) return
  guardandoPass.value = true
  mensajePass.value = ''
  try {
    await api.put('/mi-cuenta/cambiar-password', {
      password_actual: pass.value.actual,
      password_nuevo: pass.value.nuevo,
      password_nuevo_confirmation: pass.value.confirmation,
    })
    mensajePass.value = 'Contraseña cambiada correctamente.'
    errorPass.value = false
    pass.value = { actual: '', nuevo: '', confirmation: '' }
  } catch (err) {
    mensajePass.value = err?.response?.data?.message || Object.values(err?.response?.data?.errors ?? {})[0]?.[0] || 'Error.'
    errorPass.value = true
  } finally {
    guardandoPass.value = false
  }
}

onMounted(async () => {
  await cargar()
  await syncSubscriptionState()
})
</script>
