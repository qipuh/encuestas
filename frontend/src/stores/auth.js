import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'
import { sessionSave, sessionGet, sessionDelete, sessionGetAll } from '@/services/db'
import { cacheSet, cacheGetStale } from '@/services/db'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.rol?.nombre === 'Administrador')
  const isSupervisor = computed(() => ['Administrador', 'Supervisor'].includes(user.value?.rol?.nombre))
  const userId = computed(() => user.value?.id ?? null)

  async function login(email, password) {
    if (!navigator.onLine) {
      return await loginOffline(email, password)
    }

    const { data } = await api.post('/login', { email, password })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('userId', data.user.id)

    // Guardar sesión en IndexedDB para login offline futuro
    await sessionSave(data.user, data.token)
    // Cachear perfil del usuario
    await cacheSet(data.user.id, 'user_profile', data.user, 1440) // 24h
  }

  async function loginOffline(email, password) {
    // Buscar sesión guardada que coincida con el email
    const sessions = await sessionGetAll()
    const session = sessions.find(s => s.user?.email === email)

    if (!session) {
      throw new Error('Sin conexión. Este usuario no tiene sesión guardada en este dispositivo.')
    }

    // No podemos verificar contraseña offline — solo permitimos si ya se logueó antes
    // En producción podría guardarse un hash local, por ahora usamos la sesión existente
    token.value = session.token
    user.value = session.user
    localStorage.setItem('token', session.token)
    localStorage.setItem('userId', session.user.id)

    return { offline: true }
  }

  async function fetchUser() {
    if (!navigator.onLine) {
      // Intentar desde IndexedDB
      const userId = localStorage.getItem('userId')
      if (userId) {
        const cached = await cacheGetStale(userId, 'user_profile')
        if (cached) { user.value = cached; return }
        const session = await sessionGet(parseInt(userId))
        if (session) { user.value = session.user; return }
      }
      throw new Error('Sin conexión')
    }

    const { data } = await api.get('/user')
    user.value = data
    // Actualizar caché
    await sessionSave(data, token.value)
    await cacheSet(data.id, 'user_profile', data, 1440)
  }

  function logout() {
    if (navigator.onLine) {
      api.post('/logout').catch(() => {})
    }
    const uid = userId.value
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('userId')
    // NO borrar sesión de IndexedDB para permitir login offline posterior
  }

  function forgetDevice(uid) {
    // Borrar sesión offline del dispositivo explícitamente
    sessionDelete(uid)
  }

  return {
    user, token, isLoggedIn, isAdmin, isSupervisor, userId,
    login, fetchUser, logout, forgetDevice
  }
})
