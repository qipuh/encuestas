import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

// Mocks de servicios db y api
vi.mock('@/services/db', () => ({
  sessionSave: vi.fn(),
  sessionGet: vi.fn().mockResolvedValue(null),
  sessionDelete: vi.fn(),
  sessionGetAll: vi.fn().mockResolvedValue([]),
  cacheSet: vi.fn(),
  cacheGetStale: vi.fn().mockResolvedValue(null),
}))

vi.mock('@/api/axios', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
  }
}))

describe('useAuthStore', () => {
  let store

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useAuthStore()
  })

  it('inicia con sesión cerrada', () => {
    expect(store.isLoggedIn).toBe(false)
    expect(store.user).toBeNull()
  })

  it('login exitoso actualiza user y token', async () => {
    const api = (await import('@/api/axios')).default
    api.post.mockResolvedValueOnce({
      data: {
        token: 'token-abc',
        user: { id: 1, email: 'admin@test.com', rol: { nombre: 'Administrador' } }
      }
    })

    await store.login('admin@test.com', 'secret')

    expect(store.token).toBe('token-abc')
    expect(store.user.email).toBe('admin@test.com')
    expect(store.isLoggedIn).toBe(true)
    expect(store.isAdmin).toBe(true)
  })

  it('logout limpia user y token', async () => {
    const api = (await import('@/api/axios')).default
    api.post.mockResolvedValueOnce({ data: { token: 'tok', user: { id: 1, rol: { nombre: 'Supervisor' } } } })
    api.post.mockResolvedValueOnce({})

    await store.login('x@x.com', 'pass')
    store.logout()

    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(store.isLoggedIn).toBe(false)
  })

  it('isSupervisor es true para Administrador', async () => {
    store.user = { id: 1, rol: { nombre: 'Administrador' } }
    expect(store.isSupervisor).toBe(true)
  })

  it('isSupervisor es true para Supervisor', async () => {
    store.user = { id: 2, rol: { nombre: 'Supervisor' } }
    expect(store.isSupervisor).toBe(true)
  })

  it('isSupervisor es false para Calidad', async () => {
    store.user = { id: 3, rol: { nombre: 'Calidad' } }
    expect(store.isSupervisor).toBe(false)
  })

  it('loginOffline falla si no hay sesión guardada', async () => {
    Object.defineProperty(navigator, 'onLine', { value: false, writable: true })
    const { sessionGetAll } = await import('@/services/db')
    sessionGetAll.mockResolvedValueOnce([])

    await expect(store.login('noexiste@test.com', 'pass')).rejects.toThrow('Sin conexión')
  })

  it('loginOffline usa sesión guardada en IndexedDB', async () => {
    Object.defineProperty(navigator, 'onLine', { value: false, writable: true })
    const { sessionGetAll } = await import('@/services/db')
    sessionGetAll.mockResolvedValueOnce([
      { user: { id: 5, email: 'fuente@test.com', rol: { nombre: 'Fuente' } }, token: 'tok-offline' }
    ])

    await store.login('fuente@test.com', 'cualquiera')

    expect(store.token).toBe('tok-offline')
    expect(store.user.email).toBe('fuente@test.com')
  })
})
