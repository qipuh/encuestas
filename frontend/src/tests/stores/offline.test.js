import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useOfflineStore } from '@/stores/offline'

vi.mock('@/services/db', () => ({
  queueAdd: vi.fn().mockResolvedValue(undefined),
  queueGetPending: vi.fn().mockResolvedValue([]),
  queueMarkDone: vi.fn().mockResolvedValue(undefined),
  queueMarkError: vi.fn().mockResolvedValue(undefined),
  queueCountPending: vi.fn().mockResolvedValue(0),
  queueClear: vi.fn().mockResolvedValue(undefined),
  cacheSet: vi.fn(),
  cacheGetStale: vi.fn().mockResolvedValue(null),
}))

describe('useOfflineStore', () => {
  let store

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useOfflineStore()
  })

  it('inicia como online', () => {
    expect(store.isOnline).toBe(true)
  })

  it('statusLabel es "Sincronizado" cuando online sin pendientes', () => {
    store.isOnline = true
    store.isSyncing = false
    expect(store.statusLabel).toBe('Sincronizado')
  })

  it('statusLabel es "Sin conexión" cuando offline', () => {
    store.isOnline = false
    expect(store.statusLabel).toBe('Sin conexión')
  })

  it('statusLabel es "Sincronizando..." cuando syncing', () => {
    store.isOnline = true
    store.isSyncing = true
    expect(store.statusLabel).toBe('Sincronizando...')
  })

  it('hasPending es true cuando hay elementos en cola', () => {
    store.pendingCount = 3
    expect(store.hasPending).toBe(true)
    expect(store.statusLabel).toBe('3 pendientes')
  })

  it('enqueue aumenta pendingCount', async () => {
    const { queueCountPending } = await import('@/services/db')
    queueCountPending.mockResolvedValueOnce(1)

    await store.enqueue(1, 'tok', 'POST', '/api/respuestas', { emocion: 'positiva' })

    expect(store.pendingCount).toBe(1)
  })

  it('triggerSync no ejecuta si offline', async () => {
    store.isOnline = false
    const { queueGetPending } = await import('@/services/db')

    await store.triggerSync(1, 'tok')

    expect(queueGetPending).not.toHaveBeenCalled()
  })

  it('triggerSync no ejecuta si ya sincronizando', async () => {
    store.isSyncing = true
    const { queueGetPending } = await import('@/services/db')

    await store.triggerSync(1, 'tok')

    expect(queueGetPending).not.toHaveBeenCalled()
  })

  it('triggerSync procesa cola y marca como done', async () => {
    const { queueGetPending, queueMarkDone, queueCountPending } = await import('@/services/db')
    queueGetPending.mockResolvedValueOnce([
      { id: 10, method: 'POST', url: '/api/respuestas', token: 'tok', data: {} }
    ])
    queueCountPending.mockResolvedValue(0)

    global.fetch = vi.fn().mockResolvedValueOnce({ ok: true })

    await store.triggerSync(1, 'tok')

    expect(queueMarkDone).toHaveBeenCalledWith(10)
    expect(store.isSyncing).toBe(false)
  })
})
