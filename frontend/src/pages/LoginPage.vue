<template>
  <div class="h-screen flex overflow-hidden">

    <!-- ── Lado izquierdo ── -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#eef2f7] flex-col justify-between p-10 relative overflow-hidden">

      <!-- Logo -->
      <img :src="'/img/logo%20completo.png'" alt="emotix" class="h-14 w-auto object-contain self-start relative z-10" />

      <!-- Personaje: llena el lado derecho del panel -->
      <img
        :src="'/img/personaje%20login.png'"
        alt=""
        class="absolute right-0 top-[2%] h-[86%] w-auto object-contain object-right pointer-events-none select-none"
      />

      <!-- Texto hero (izquierda, centrado verticalmente) -->
      <div class="flex-1 flex flex-col justify-center relative z-10 max-w-[48%]">
        <h1 class="text-[2.6rem] font-black text-[#0f1f3d] leading-tight mb-4">
          Entendemos<br>emociones.<br>
          <span class="text-[#2ecc71]">Impulsamos<br>decisiones.</span>
        </h1>
        <p class="text-gray-500 text-sm leading-relaxed">
          Transforma la voz de tus clientes en <strong>leads</strong> que generan impacto real en tu negocio.
        </p>
      </div>

      <!-- App + flecha + QR -->
      <div class="flex items-center gap-4 relative z-10">
        <img :src="'/img/emotixchat.png'" alt="emotix app" class="h-[72px] w-auto object-contain" />
        <img :src="'/img/Emotix-image-flecha-@4x.png'" alt="" class="h-5 w-auto object-contain" />
        <img :src="'/img/qr.png'" alt="Descargar app" class="h-[72px] w-auto object-contain" />
      </div>
    </div>

    <!-- ── Lado derecho: formulario ── -->
    <div class="flex-1 bg-[#0f1f3d] flex items-center justify-center px-8 py-12">
      <div class="w-full max-w-md">
        <h2 class="text-3xl font-bold text-white mb-2">¡Bienvenido de vuelta!</h2>
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
          Inicia sesión para seguir transformando<br>experiencias en resultados.
        </p>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Usuario -->
          <div>
            <label class="block text-sm text-gray-300 mb-1.5 font-medium">Usuario</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 flex items-center">
                <ion-icon name="mail-outline" style="font-size:18px" />
              </span>
              <input
                v-model="form.email"
                type="email"
                placeholder="Ingresa tu usuario"
                required
                class="w-full bg-[#152b52] text-white placeholder-gray-500 rounded-xl pl-11 pr-4 py-3.5 text-sm border border-white/10 focus:outline-none focus:border-[#2ecc71] transition-colors"
              />
            </div>
          </div>

          <!-- Contraseña -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-sm text-gray-300 font-medium">Contraseña</label>
              <a href="#" class="text-xs text-[#2ecc71] hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 flex items-center">
                <ion-icon name="lock-closed-outline" style="font-size:18px" />
              </span>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Ingresa tu contraseña"
                required
                class="w-full bg-[#152b52] text-white placeholder-gray-500 rounded-xl pl-11 pr-12 py-3.5 text-sm border border-white/10 focus:outline-none focus:border-[#2ecc71] transition-colors"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 flex items-center"
              >
                <ion-icon :name="showPassword ? 'eye-outline' : 'eye-off-outline'" style="font-size:18px" />
              </button>
            </div>
          </div>

          <!-- Error -->
          <p v-if="error" class="text-red-400 text-xs text-center">{{ error }}</p>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-[#2ecc71] hover:bg-[#27ae60] text-white font-bold py-3.5 rounded-xl transition-colors text-base disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <ion-icon v-if="loading" name="reload-outline" class="animate-spin" style="font-size:16px" />
            Iniciar sesión
          </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-6">
          ¿No tienes cuenta?
          <a href="#" class="text-[#2ecc71] hover:underline font-medium">Crear cuenta</a>
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const form         = ref({ email: '', password: '' })
const showPassword = ref(false)
const loading      = ref(false)
const error        = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    const rol = auth.user?.rol?.nombre
    router.push(rol === 'Fuente' ? '/encuesta-activa' : '/app/dashboard')
  } catch (e) {
    error.value = e.response?.data?.message || 'Credenciales incorrectas'
  } finally {
    loading.value = false
  }
}
</script>
