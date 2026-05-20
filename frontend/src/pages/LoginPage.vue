<template>

  <!-- ══════════════════════════════════════════════════════
       DESKTOP (lg+)  — sin cambios
  ══════════════════════════════════════════════════════ -->
  <div class="hidden lg:flex h-screen overflow-hidden">

    <!-- Lado izquierdo -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#eef2f7] flex-col justify-between p-10 relative overflow-hidden">
      <img :src="'/img/logo%20completo.png'" alt="emotix" class="h-14 w-auto object-contain self-start relative z-10" />
      <img
        :src="'/img/personaje%20login.png'"
        alt=""
        class="absolute right-0 top-[2%] h-[86%] w-auto object-contain object-right pointer-events-none select-none"
      />
      <div class="flex-1 flex flex-col justify-center relative z-10 max-w-[48%]">
        <h1 class="text-[2.6rem] font-black text-[#0f1f3d] leading-tight mb-4">
          Entendemos<br>emociones.<br>
          <span class="text-[#2ecc71]">Impulsamos<br>decisiones.</span>
        </h1>
        <p class="text-gray-500 text-sm leading-relaxed">
          Transforma la voz de tus clientes en <strong>leads</strong> que generan impacto real en tu negocio.
        </p>
      </div>
      <div class="flex items-center gap-4 relative z-10">
        <img :src="'/img/emotixchat.png'" alt="emotix app" class="h-[72px] w-auto object-contain" />
        <img :src="'/img/Emotix-image-flecha-@4x.png'" alt="" class="h-5 w-auto object-contain" />
        <img :src="'/img/qr.png'" alt="Descargar app" class="h-[72px] w-auto object-contain" />
      </div>
    </div>

    <!-- Lado derecho: formulario -->
    <div class="flex-1 bg-[#0f1f3d] flex items-center justify-center px-8 py-12">
      <div class="w-full max-w-md">
        <h2 class="text-3xl font-bold text-white mb-2">¡Bienvenido de vuelta!</h2>
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
          Inicia sesión para seguir transformando<br>experiencias en resultados.
        </p>
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm text-gray-300 mb-1.5 font-medium">Usuario</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 flex items-center">
                <ion-icon name="mail-outline" style="font-size:18px" />
              </span>
              <input v-model="form.email" type="email" placeholder="Ingresa tu usuario" required
                class="w-full bg-[#152b52] text-white placeholder-gray-500 rounded-xl pl-11 pr-4 py-3.5 text-sm border border-white/10 focus:outline-none focus:border-[#2ecc71] transition-colors" />
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-sm text-gray-300 font-medium">Contraseña</label>
              <a href="#" class="text-xs text-[#2ecc71] hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 flex items-center">
                <ion-icon name="lock-closed-outline" style="font-size:18px" />
              </span>
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                placeholder="Ingresa tu contraseña" required
                class="w-full bg-[#152b52] text-white placeholder-gray-500 rounded-xl pl-11 pr-12 py-3.5 text-sm border border-white/10 focus:outline-none focus:border-[#2ecc71] transition-colors" />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 flex items-center">
                <ion-icon :name="showPassword ? 'eye-outline' : 'eye-off-outline'" style="font-size:18px" />
              </button>
            </div>
          </div>
          <p v-if="error" class="text-red-400 text-xs text-center">{{ error }}</p>
          <button type="submit" :disabled="loading"
            class="w-full bg-[#2ecc71] hover:bg-[#27ae60] text-white font-bold py-3.5 rounded-xl transition-colors text-base disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
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

  <!-- ══════════════════════════════════════════════════════
       MOBILE (< lg)
  ══════════════════════════════════════════════════════ -->
  <div class="lg:hidden min-h-screen bg-[#eef2f7] overflow-hidden">

    <!-- Personaje -->
    <div class="flex justify-center items-end overflow-hidden" style="height:50vh">
      <img :src="'/img/personaje%20login.png'" alt=""
        class="h-full w-auto object-contain object-bottom select-none pointer-events-none" />
    </div>

    <!-- Splash content -->
    <div class="px-8 pt-5 pb-36 text-center">
      <img :src="'/img/logo%20completo.png'" alt="emotix" class="h-11 mx-auto mb-2 object-contain" />
      <p class="text-gray-500 text-sm">
        Experiencias que <span class="text-[#2ecc71] font-semibold">se sienten</span>
      </p>
      <h2 class="text-[1.85rem] font-black text-[#0f1f3d] mt-5 mb-2 leading-tight">
        ¡Bienvenido<br>de vuelta!
      </h2>
      <p class="text-gray-400 text-sm leading-relaxed">
        Inicia sesión para seguir transformando<br>experiencias en resultados.
      </p>
    </div>

    <!-- Botón fijo "Iniciar sesión" (splash state) -->
    <Transition name="fade-btn">
      <div v-if="!showForm" class="fixed bottom-0 inset-x-0 px-6 pb-10 pt-6 z-10"
        style="background: linear-gradient(to top, #eef2f7 70%, transparent)">
        <button @click="showForm = true"
          class="w-full py-5 bg-[#0f1f3d] text-white text-base font-bold rounded-full shadow-xl active:scale-95 transition-transform">
          Iniciar sesión
        </button>
      </div>
    </Transition>

    <!-- Overlay tap-to-close cuando el panel está abierto -->
    <Transition name="fade-btn">
      <div v-if="showForm" class="fixed inset-0 z-10" @click="showForm = false" />
    </Transition>

    <!-- Panel oscuro deslizante -->
    <Transition name="panel-up">
      <div v-if="showForm"
        class="fixed inset-x-0 bottom-0 z-20 bg-[#0f1f3d] rounded-t-[2rem] overflow-y-auto"
        style="max-height: 70vh">
        <div class="px-7 pt-7 pb-12">

          <!-- Handle visual -->
          <div class="w-10 h-1 bg-white/20 rounded-full mx-auto mb-6" />

          <form @submit.prevent="handleLogin" class="space-y-4">
            <!-- Email -->
            <div>
              <label class="block text-sm text-white/70 mb-1.5 font-medium">Usuario</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                  <ion-icon name="mail-outline" style="font-size:18px" />
                </span>
                <input v-model="form.email" type="email" placeholder="Ingresa tu usuario" required
                  class="w-full bg-white text-gray-800 placeholder-gray-400 rounded-2xl pl-11 pr-4 py-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#2ecc71]" />
              </div>
            </div>

            <!-- Password -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-sm text-white/70 font-medium">Contraseña</label>
                <a href="#" class="text-xs text-[#2ecc71]">¿Olvidaste tu contraseña?</a>
              </div>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                  <ion-icon name="lock-closed-outline" style="font-size:18px" />
                </span>
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                  placeholder="Ingresa tu contraseña" required
                  class="w-full bg-white text-gray-800 placeholder-gray-400 rounded-2xl pl-11 pr-12 py-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#2ecc71]" />
                <button type="button" @click="showPassword = !showPassword"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                  <ion-icon :name="showPassword ? 'eye-outline' : 'eye-off-outline'" style="font-size:18px" />
                </button>
              </div>
            </div>

            <p v-if="error" class="text-red-400 text-xs text-center">{{ error }}</p>

            <button type="submit" :disabled="loading"
              class="w-full bg-[#2ecc71] hover:bg-[#27ae60] text-white font-bold py-4 rounded-2xl text-sm disabled:opacity-50 flex items-center justify-center gap-2 mt-1 active:scale-95 transition-transform">
              <ion-icon v-if="loading" name="reload-outline" class="animate-spin" style="font-size:16px" />
              Iniciar sesión
            </button>
          </form>

          <p class="text-center text-white/40 text-sm mt-5">
            ¿No tienes cuenta?
            <a href="#" class="text-[#2ecc71] font-medium">Contacta con tu manager</a>
          </p>

          <!-- Logo emotix (blanco) -->
          <div class="flex justify-center mt-7">
            <img :src="'/img/logo%20completo.png'" alt="emotix"
              class="h-7 object-contain opacity-50"
              style="filter: brightness(0) invert(1)" />
          </div>
        </div>
      </div>
    </Transition>
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
const showForm     = ref(false)

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

<style scoped>
/* Panel slide up */
.panel-up-enter-active,
.panel-up-leave-active {
  transition: transform 0.38s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.panel-up-enter-from,
.panel-up-leave-to {
  transform: translateY(100%);
}

/* Fade for button / overlay */
.fade-btn-enter-active,
.fade-btn-leave-active {
  transition: opacity 0.2s ease;
}
.fade-btn-enter-from,
.fade-btn-leave-to {
  opacity: 0;
}
</style>
