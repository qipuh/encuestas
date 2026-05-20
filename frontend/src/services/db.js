import { openDB } from 'idb'

const DB_VERSION = 1
const DB_NAME = 'emotix_offline'

// Un único DB, stores separados por userId
function getDB() {
  return openDB(DB_NAME, DB_VERSION, {
    upgrade(db) {
      // Cache de datos por usuario
      if (!db.objectStoreNames.contains('cache')) {
        const cache = db.createObjectStore('cache', { keyPath: 'key' })
        cache.createIndex('userId', 'userId')
        cache.createIndex('expires', 'expires')
      }
      // Cola de mutaciones pendientes (offline queue)
      if (!db.objectStoreNames.contains('queue')) {
        const queue = db.createObjectStore('queue', { keyPath: 'id', autoIncrement: true })
        queue.createIndex('userId', 'userId')
        queue.createIndex('status', 'status')
        queue.createIndex('createdAt', 'createdAt')
      }
      // Sesiones de usuario cacheadas (para login offline)
      if (!db.objectStoreNames.contains('sessions')) {
        db.createObjectStore('sessions', { keyPath: 'userId' })
      }
    }
  })
}

// ── Cache API ────────────────────────────────────────────────
export async function cacheSet(userId, key, data, ttlMinutes = 60) {
  const db = await getDB()
  await db.put('cache', {
    key: `${userId}:${key}`,
    userId,
    data,
    cachedAt: Date.now(),
    expires: Date.now() + ttlMinutes * 60 * 1000
  })
}

export async function cacheGet(userId, key) {
  const db = await getDB()
  const entry = await db.get('cache', `${userId}:${key}`)
  if (!entry) return null
  if (Date.now() > entry.expires) return null // expirado
  return entry.data
}

export async function cacheGetStale(userId, key) {
  // Devuelve aunque esté expirado (para offline)
  const db = await getDB()
  const entry = await db.get('cache', `${userId}:${key}`)
  return entry?.data ?? null
}

export async function cacheDelete(userId, key) {
  const db = await getDB()
  await db.delete('cache', `${userId}:${key}`)
}

export async function cacheClearUser(userId) {
  const db = await getDB()
  const tx = db.transaction('cache', 'readwrite')
  const index = tx.store.index('userId')
  let cursor = await index.openCursor(IDBKeyRange.only(userId))
  while (cursor) {
    await cursor.delete()
    cursor = await cursor.continue()
  }
  await tx.done
}

// ── Queue API ────────────────────────────────────────────────
export async function queueAdd(userId, token, method, url, data, meta = {}) {
  const db = await getDB()
  return db.add('queue', {
    userId,
    token,
    method,
    url,
    data,
    meta,             // offline_id, type, etc.
    status: 'pending',
    retries: 0,
    createdAt: Date.now()
  })
}

export async function queueGetPending(userId) {
  const db = await getDB()
  const all = await db.getAllFromIndex('queue', 'userId', userId)
  return all.filter(item => item.status === 'pending').sort((a, b) => a.createdAt - b.createdAt)
}

export async function queueMarkDone(id) {
  const db = await getDB()
  const item = await db.get('queue', id)
  if (item) await db.put('queue', { ...item, status: 'done' })
}

export async function queueMarkError(id, error) {
  const db = await getDB()
  const item = await db.get('queue', id)
  if (item) await db.put('queue', { ...item, status: 'error', error, retries: item.retries + 1 })
}

export async function queueCountPending(userId) {
  const pending = await queueGetPending(userId)
  return pending.length
}

export async function queueClear(userId) {
  const db = await getDB()
  const tx = db.transaction('queue', 'readwrite')
  const index = tx.store.index('userId')
  let cursor = await index.openCursor(IDBKeyRange.only(userId))
  while (cursor) {
    if (cursor.value.status === 'done') await cursor.delete()
    cursor = await cursor.continue()
  }
  await tx.done
}

// ── Sessions API (login offline) ─────────────────────────────
export async function sessionSave(user, token) {
  const db = await getDB()
  await db.put('sessions', {
    userId: user.id,
    user,
    token,
    savedAt: Date.now()
  })
}

export async function sessionGet(userId) {
  const db = await getDB()
  return db.get('sessions', userId)
}

export async function sessionGetAll() {
  const db = await getDB()
  return db.getAll('sessions')
}

export async function sessionDelete(userId) {
  const db = await getDB()
  await db.delete('sessions', userId)
}
