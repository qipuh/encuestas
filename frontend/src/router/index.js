import { createRouter, createWebHistory } from 'vue-router'
import pinia from '@/piniaInstance'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/login' },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/LoginPage.vue'),
      meta: { guest: true }
    },
    {
      path: '/app',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: { name: 'dashboard' } },
        { path: 'dashboard',              name: 'dashboard',          component: () => import('@/pages/DashboardPage.vue') },
        { path: 'encuestas',              name: 'encuestas',          component: () => import('@/pages/encuestas/EncuestasPage.vue') },
        { path: 'encuestas/crear',        name: 'encuestas.crear',    component: () => import('@/pages/encuestas/EncuestaFormPage.vue') },
        { path: 'encuestas/:id/editar',   name: 'encuestas.editar',   component: () => import('@/pages/encuestas/EncuestaFormPage.vue') },
        { path: 'comentarios',            name: 'comentarios',        component: () => import('@/pages/comentarios/ComentariosPage.vue') },
        { path: 'comentarios/:id',        name: 'comentarios.detalle',component: () => import('@/pages/comentarios/ComentarioDetallePage.vue') },
        { path: 'reportes',               name: 'reportes',           component: () => import('@/pages/ReportesPage.vue') },
        { path: 'usuarios',               name: 'usuarios',           component: () => import('@/pages/usuarios/UsuariosPage.vue') },
        { path: 'usuarios/crear',         name: 'usuarios.crear',     component: () => import('@/pages/usuarios/UsuarioFormPage.vue') },
        { path: 'usuarios/:id/editar',    name: 'usuarios.editar',    component: () => import('@/pages/usuarios/UsuarioFormPage.vue') },
        { path: 'configuracion',          name: 'configuracion',      component: () => import('@/pages/ConfiguracionPage.vue') },
        { path: 'mi-cuenta',              name: 'mi-cuenta',          component: () => import('@/pages/MiCuentaPage.vue') }
      ]
    },
    {
      path: '/encuesta-activa',
      name: 'encuesta-activa',
      component: () => import('@/pages/EncuestaActivaPage.vue'),
      meta: { requiresAuth: true }
    },
    { path: '/:pathMatch(.*)*', redirect: '/login' }
  ]
})

router.beforeEach(async (to) => {
  const auth = useAuthStore(pinia)

  if (!auth.user && localStorage.getItem('token')) {
    try { await auth.fetchUser() } catch { auth.logout() }
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) return { name: 'login' }
  if (to.meta.guest && auth.isLoggedIn) return { path: '/app/dashboard' }
})

export default router
