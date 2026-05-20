import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, vi } from 'vitest'

beforeEach(() => {
  setActivePinia(createPinia())

  // Mock localStorage
  const store = {}
  vi.spyOn(Storage.prototype, 'getItem').mockImplementation(k => store[k] ?? null)
  vi.spyOn(Storage.prototype, 'setItem').mockImplementation((k, v) => { store[k] = v })
  vi.spyOn(Storage.prototype, 'removeItem').mockImplementation(k => { delete store[k] })

  // Mock navigator.onLine
  Object.defineProperty(navigator, 'onLine', { value: true, writable: true })
})
