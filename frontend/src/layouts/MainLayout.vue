<template>
  <Toast position="bottom-right" />
  <div class="flex flex-col h-screen overflow-hidden bg-[#f4f5f9]">

    <!-- ── Topbar ── -->
    <header class="flex items-center gap-3 pl-3 pr-4 py-3 md:py-4 bg-white border-b border-gray-200 shrink-0 z-10">

      <!-- Logo (coincide con ancho del sidebar en desktop) -->
      <div class="w-14 md:w-60 shrink-0 flex items-center justify-center md:pl-0">
        <template v-if="config.logoUrl">
          <img :src="config.logoUrl" alt="Logo" class="h-9 md:h-12 max-w-[120px] md:max-w-[180px] object-contain" />
        </template>
        <template v-else>
          <div class="flex items-center gap-1.5">
            <div class="flex items-center gap-0.5">
              <ion-icon name="sad-outline"   style="font-size:20px;color:#ef4444" />
              <ion-icon name="happy-outline" style="font-size:20px;color:#2ecc71" />
            </div>
            <div class="hidden md:block">
              <p class="text-base font-black tracking-tight leading-none text-[#0f1f3d]">emotix</p>
              <p class="text-[9px] text-gray-400 leading-none mt-0.5">Experiencias que se sienten</p>
            </div>
          </div>
        </template>
      </div>

      <!-- Hamburger -->
      <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700 shrink-0 p-1 ml-10">
        <ion-icon name="menu-outline" style="font-size:22px" />
      </button>

      <!-- Search (oculto en móvil) -->
      <div class="hidden sm:block flex-1 max-w-sm">
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
            <ion-icon name="search-outline" style="font-size:15px" />
          </span>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar..."
            class="w-full pl-9 pr-4 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-full focus:outline-none focus:border-[#2ecc71] transition-colors"
          />
        </div>
      </div>

      <!-- Offline badge -->
      <div
        v-if="!offline.isOnline || offline.hasPending || offline.isSyncing"
        class="flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-full shrink-0"
        :class="!offline.isOnline ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'"
      >
        <ion-icon
          :name="offline.isSyncing ? 'sync-outline' : (!offline.isOnline ? 'cloud-offline-outline' : 'cloud-upload-outline')"
          :class="{ 'animate-spin': offline.isSyncing }"
          style="font-size:13px"
        />
        <span class="hidden sm:inline">{{ offline.statusLabel }}</span>
        <button v-if="offline.hasPending && !offline.isSyncing && offline.isOnline" @click="sync.attemptSync()" class="underline font-semibold ml-1 hidden sm:inline">Sync</button>
      </div>

      <!-- Bell + User -->
      <div class="flex items-center gap-2 md:gap-3 ml-auto">
        <button
          @click="resetNotif(); $router.push('/app/comentarios')"
          class="relative text-gray-500 hover:text-gray-700 transition-colors"
        >
          <ion-icon
            :name="notifCount ? 'notifications' : 'notifications-outline'"
            style="font-size:22px"
            :style="notifCount ? 'color:#ef4444' : ''"
          />
          <span v-if="notifCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center animate-pulse">
            {{ notifCount > 9 ? '9+' : notifCount }}
          </span>
        </button>

        <!-- Backdrop user menu -->
        <div v-if="userMenuOpen" class="fixed inset-0 z-30" @click="userMenuOpen = false" />

        <div class="relative z-40">
          <div class="flex items-center gap-2 cursor-pointer select-none" @click.stop="userMenuOpen = !userMenuOpen">
            <!-- Nombre y rol: solo en desktop -->
            <div class="hidden md:block text-right">
              <p class="text-sm font-bold text-gray-800 leading-tight">{{ auth.user?.name }} {{ auth.user?.apellidos }}</p>
              <p class="text-xs text-[#2ecc71] font-medium">{{ auth.user?.rol?.nombre }}</p>
            </div>
            <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-gray-200 bg-gray-100 flex items-center justify-center shrink-0">
              <img v-if="auth.user?.foto_url" :src="auth.user.foto_url" class="w-full h-full object-cover" />
              <ion-icon v-else name="person" style="font-size:18px;color:#94a3b8" />
            </div>
            <ion-icon name="chevron-down-outline" style="font-size:13px;color:#94a3b8" class="hidden md:block" />
          </div>

          <!-- User menu -->
          <div
            v-if="userMenuOpen"
            @click.stop
            class="absolute right-0 top-full mt-3 bg-white rounded-2xl shadow-xl border border-gray-100 py-4 z-40"
            style="min-width:210px"
          >
            <p class="px-5 pb-3 text-base font-black text-gray-800">
              Hola, {{ auth.user?.name?.split(' ')[0] }}!
            </p>

            <button
              @click="userMenuOpen = false; router.push('/app/mi-cuenta')"
              class="flex items-center gap-3 w-full px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
            >
              <ion-icon name="person-circle-outline" style="font-size:18px;color:#6b7280" />
              Mi cuenta
            </button>
            <button class="flex items-center gap-3 w-full px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
              <ion-icon name="help-buoy-outline" style="font-size:18px;color:#6b7280" />
              Ayuda
            </button>
            <div class="my-2 border-t border-gray-100" />
            <button
              @click="auth.logout(); router.push('/login')"
              class="flex items-center gap-3 w-full px-5 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors"
            >
              <ion-icon name="log-out-outline" style="font-size:18px;color:#ef4444" />
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- ── Cuerpo ── -->
    <div class="flex flex-1 overflow-hidden relative">

      <!-- Backdrop móvil -->
      <div
        v-if="sidebarOpen && isMobile"
        class="absolute inset-0 bg-black/40 z-40"
        @click="sidebarOpen = false"
      />

      <!-- Sidebar -->
      <aside
        class="flex flex-col bg-[#0f1f3d] text-white transition-all duration-300 shrink-0 overflow-hidden
               absolute inset-y-0 left-0 z-50 h-full
               md:relative md:inset-auto md:z-auto md:h-auto"
        :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-64 -translate-x-full md:w-0 md:translate-x-0'"
      >
        <!-- Usuario -->
        <div class="flex flex-col items-center py-6 border-b border-white/10 px-4">
          <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#2ecc71] mb-3 bg-[#152b52] flex items-center justify-center">
            <img v-if="auth.user?.foto_url" :src="auth.user.foto_url" class="w-full h-full object-cover" />
            <ion-icon v-else name="person" style="font-size:38px;color:#94a3b8" />
          </div>
          <p class="font-bold text-sm text-center">¡Bienvenido, {{ auth.user?.name?.split(' ')[0] }}!</p>
          <p class="text-xs text-gray-400 mt-0.5">{{ auth.user?.rol?.nombre }}</p>
        </div>

        <!-- Nav -->
        <nav class="flex-1 py-3 overflow-y-auto">
          <p class="text-[10px] text-gray-500 uppercase tracking-widest px-5 mb-2 font-semibold">Menú</p>
          <RouterLink
            v-for="item in navItems"
            :key="item.name"
            :to="item.to"
            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent"
            :class="{ 'border-[#2ecc71] bg-white/5 text-white font-semibold': route.path === item.to || route.path.startsWith(item.to + '/') }"
            @click="isMobile && (sidebarOpen = false)"
          >
            <ion-icon :name="item.icon" style="font-size:17px;min-width:17px" />
            <span>{{ item.label }}</span>
          </RouterLink>
        </nav>

        <!-- Offline indicator -->
        <div v-if="!offline.isOnline" class="px-4 py-3 border-t border-white/10">
          <span class="text-[10px] bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded-full">Sin conexión</span>
        </div>
      </aside>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto p-4 md:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useOfflineStore } from '@/stores/offline'
import { useConfigStore } from '@/stores/config'
import { useSync } from '@/composables/useSync'
import { useNotificaciones } from '@/composables/useNotificaciones'
import Toast from 'primevue/toast'

const route   = useRoute()
const router  = useRouter()
const auth    = useAuthStore()
const offline = useOfflineStore()
const config  = useConfigStore()
const sync    = useSync()
const { pendingCount: notifCount, resetear: resetNotif } = useNotificaciones()

onMounted(config.cargar)

// ── Responsivo ──────────────────────────────────────
const isMobile    = ref(window.innerWidth < 768)
const sidebarOpen = ref(window.innerWidth >= 768)

function onResize() {
  const mobile = window.innerWidth < 768
  isMobile.value = mobile
  if (!mobile) sidebarOpen.value = true
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

onMounted(() => window.addEventListener('resize', onResize))
onUnmounted(() => window.removeEventListener('resize', onResize))

// Cerrar sidebar al navegar en móvil
watch(() => route.path, () => { if (isMobile.value) sidebarOpen.value = false })

// ── Resto ────────────────────────────────────────────
const search       = ref('')
const userMenuOpen = ref(false)

const navItems = [
  { name: 'dashboard',     to: '/app/dashboard',     label: 'Dashboard',     icon: 'grid-outline' },
  { name: 'encuestas',     to: '/app/encuestas',     label: 'Encuestas',     icon: 'clipboard-outline' },
  { name: 'comentarios',   to: '/app/comentarios',   label: 'Comentarios',   icon: 'chatbubbles-outline' },
  { name: 'reportes',      to: '/app/reportes',      label: 'Reportes',      icon: 'bar-chart-outline' },
  { name: 'usuarios',      to: '/app/usuarios',      label: 'Usuarios',      icon: 'people-outline' },
  { name: 'configuracion', to: '/app/configuracion', label: 'Configuración', icon: 'settings-outline' },
]
</script>
