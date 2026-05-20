<template>
  <div @click="estadoMenuOpen = false">

    <!-- Spinner -->
    <div v-if="cargando" class="flex items-center justify-center py-20">
      <div class="w-7 h-7 border-4 border-[#2ecc71] border-t-transparent rounded-full animate-spin" />
    </div>

    <template v-else-if="comentario">

      <!-- Breadcrumb -->
      <div class="flex items-start gap-2 text-sm mb-6">
        <RouterLink to="/app/comentarios" class="text-gray-400 hover:text-gray-600 transition-colors font-medium shrink-0 mt-1">Comentarios</RouterLink>
        <ion-icon name="chevron-forward-outline" style="font-size:13px;color:#9ca3af;flex-shrink:0;margin-top:5px" />
        <div>
          <p class="text-xl font-black text-[#0f1f3d] leading-tight">Seguimiento de comentario</p>
          <p class="text-xs text-gray-400 mt-0.5">Comentario # {{ comentario.id }}</p>
        </div>
      </div>

      <div class="flex gap-5">

        <!-- ── Columna principal ── -->
        <div class="flex-1 min-w-0 space-y-4">

          <!-- Información + Comentario del cliente (una sola card) -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between mb-5">
              <h2 class="font-black text-[#0f1f3d] text-base">Información del comentario</h2>

              <!-- Status dropdown -->
              <div class="relative" @click.stop>
                <button
                  @click="estadoMenuOpen = !estadoMenuOpen"
                  class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold text-white transition-colors"
                  :style="{ background: estadoColor(comentario.estado) }"
                >
                  {{ estadoLabel(comentario.estado) }}
                  <ion-icon name="chevron-down-outline" style="font-size:12px" />
                </button>
                <div v-if="estadoMenuOpen"
                  class="absolute right-0 top-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                  style="min-width:150px"
                >
                  <button v-for="s in ESTADOS" :key="s.value"
                    @click="cambiarEstado(s.value); estadoMenuOpen = false"
                    class="flex items-center gap-2 w-full px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
                    :class="comentario.estado === s.value ? 'font-bold text-[#0f1f3d]' : 'text-gray-600'"
                  >
                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: estadoColor(s.value) }" />
                    {{ s.label }}
                  </button>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-4 gap-4 pb-5 border-b border-gray-100">
              <div>
                <p class="text-xs text-gray-400 font-semibold mb-1">Fecha</p>
                <div class="flex items-center gap-1.5">
                  <ion-icon name="calendar-outline" style="font-size:16px;color:#9ca3af;flex-shrink:0" />
                  <p class="text-sm font-semibold text-[#0f1f3d]">{{ comentario.fecha }}</p>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-400 font-semibold mb-1">Hora</p>
                <div class="flex items-center gap-1.5">
                  <ion-icon name="time-outline" style="font-size:16px;color:#9ca3af;flex-shrink:0" />
                  <p class="text-sm font-semibold text-[#0f1f3d]">{{ comentario.hora?.slice(0,5) }}</p>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-400 font-semibold mb-1">Fuente</p>
                <div class="flex items-center gap-1.5">
                  <ion-icon name="home-outline" style="font-size:16px;color:#9ca3af;flex-shrink:0" />
                  <p class="text-sm font-semibold text-[#0f1f3d]">{{ comentario.fuente?.nombre ?? '—' }}</p>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-400 font-semibold mb-1">Encuesta</p>
                <div class="flex items-center gap-1.5">
                  <ion-icon name="document-text-outline" style="font-size:16px;color:#9ca3af;flex-shrink:0" />
                  <p class="text-sm font-semibold text-[#0f1f3d]">{{ comentario.encuesta?.nombre ?? '—' }}</p>
                </div>
              </div>
            </div>

            <!-- Comentario del cliente (misma card) -->
            <div class="border-t border-gray-100 pt-5">
              <h2 class="font-black text-[#0f1f3d] text-base mb-4">Comentario del cliente</h2>
              <div class="flex items-start gap-4">
                <div
                  class="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
                  :style="{ background: bgEmocion(comentario.emocion) }"
                >
                  <ion-icon
                    :name="iconEmocion[comentario.emocion] ?? 'help-circle-outline'"
                    :style="`font-size:24px;color:${colorEmocion(comentario.emocion)}`"
                  />
                </div>
                <div
                  class="flex-1 rounded-2xl px-5 py-4 text-sm leading-relaxed font-medium"
                  :style="{ background: bgEmocionSuave(comentario.emocion), color: '#374151' }"
                >
                  "{{ comentario.comentario }}"
                </div>
              </div>
            </div>
          </div>

          <!-- Bitácora -->
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="font-black text-[#0f1f3d] text-base mb-5">Bitácora de seguimiento</h2>

            <div v-if="!comentario.seguimientos?.length" class="text-center py-8 text-gray-400 text-sm">
              No hay seguimientos aún
            </div>

            <div v-else class="relative">
              <!-- Línea vertical -->
              <div class="absolute left-[7px] top-3 bottom-3 w-px bg-gray-200" />

              <div class="space-y-0">
                <div v-for="(seg, i) in comentario.seguimientos" :key="seg.id"
                  class="flex gap-5 pb-6 relative"
                >
                  <!-- Dot -->
                  <div
                    class="w-4 h-4 rounded-full shrink-0 mt-0.5 z-10"
                    :style="{ background: dotColor(seg, i) }"
                  />

                  <!-- Contenido -->
                  <div class="flex-1 flex items-center gap-4 min-w-0 border-b border-gray-50 pb-4">
                    <span class="text-xs text-gray-500 w-20 shrink-0">{{ formatFechaSeg(seg.created_at) }}</span>
                    <span class="text-xs text-gray-500 w-12 shrink-0">{{ formatHoraSeg(seg.created_at) }}</span>
                    <span class="text-xs font-semibold text-gray-700 w-28 shrink-0">{{ seg.usuario?.name ?? 'Sistema' }}</span>
                    <!-- Texto/acción ocupa el espacio restante -->
                    <span class="flex-1 min-w-0 text-xs"
                      :class="seg.accion ? 'font-bold' : 'text-gray-600'"
                      :style="seg.accion ? { color: estadoColor(seg.accion) } : {}"
                    >{{ seg.accion ? `"${estadoLabel(seg.accion)}"` : seg.nota }}</span>
                    <!-- Ícono solo si tiene archivo -->
                    <button
                      v-if="seg.archivos?.length"
                      class="shrink-0 p-1 rounded-lg transition-colors"
                      :style="hoveredArchivo === seg.id ? 'background:#eff6ff' : ''"
                      @mouseenter="hoveredArchivo = seg.id"
                      @mouseleave="hoveredArchivo = null"
                      @click.stop="verArchivo(seg)"
                      title="Ver archivo"
                    >
                      <ion-icon
                        name="document-attach-outline"
                        :style="`font-size:17px;display:block;color:${hoveredArchivo === seg.id ? '#3b82f6' : '#475569'}`"
                      />
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ── Sidebar derecho ── -->
        <div class="w-56 shrink-0 space-y-3">

          <!-- Acciones -->
          <button
            @click="mostrarFormNota = true"
            class="w-full py-3 bg-[#2ecc71] text-white text-sm font-bold rounded-2xl hover:bg-[#27ae60] transition-colors flex items-center justify-center gap-2 shadow-sm"
          >
            <ion-icon name="chatbubble-outline" style="font-size:16px" />
            Agregar nota
          </button>

          <button
            @click="exportarComentario"
            class="w-full py-3 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-2xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shadow-sm"
          >
            <ion-icon name="share-outline" style="font-size:16px" />
            Exportar
          </button>

          <RouterLink to="/app/comentarios"
            class="w-full py-3 border border-gray-200 bg-white text-gray-700 text-sm font-semibold rounded-2xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shadow-sm"
          >
            <ion-icon name="return-up-back-outline" style="font-size:16px" />
            Regresar
          </RouterLink>

          <!-- Datos del cliente -->
          <div class="bg-white rounded-2xl shadow-sm p-5 mt-2">
            <p class="text-sm font-black text-[#0f1f3d] mb-5">Datos del cliente</p>
            <div class="space-y-4">
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <ion-icon name="person-outline" style="font-size:14px;color:#6b7280" />
                  <p class="text-xs font-bold text-gray-600">Nombre</p>
                </div>
                <p class="text-sm text-gray-700 pl-5">{{ comentario.cliente_nombre || '—' }}</p>
              </div>
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <ion-icon name="card-outline" style="font-size:14px;color:#6b7280" />
                  <p class="text-xs font-bold text-gray-600">Dni</p>
                </div>
                <p class="text-sm text-gray-700 pl-5">{{ comentario.cliente_dni || '—' }}</p>
              </div>
              <div>
                <div class="flex items-center gap-1.5 mb-1">
                  <ion-icon name="call-outline" style="font-size:14px;color:#6b7280" />
                  <p class="text-xs font-bold text-gray-600">Teléfono</p>
                </div>
                <p class="text-sm text-gray-700 pl-5">{{ comentario.cliente_telefono || '—' }}</p>
              </div>
            </div>

            <!-- Editar datos cliente — temporalmente oculto
            <button
              @click="editarCliente = !editarCliente"
              class="mt-4 text-xs text-[#2ecc71] font-semibold hover:underline flex items-center gap-1"
            >
              <ion-icon name="create-outline" style="font-size:13px" />
              Editar datos
            </button>
            <div v-if="editarCliente" class="mt-3 space-y-2">
              <input v-model="clienteForm.cliente_nombre" type="text" placeholder="Nombre"
                class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
              <input v-model="clienteForm.cliente_dni" type="text" placeholder="DNI"
                class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
              <input v-model="clienteForm.cliente_telefono" type="text" placeholder="Teléfono"
                class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-[#2ecc71]" />
              <button @click="guardarCliente"
                class="w-full py-1.5 bg-[#0f1f3d] text-white text-xs font-bold rounded-lg hover:bg-[#152b52] transition-colors">
                Guardar
              </button>
            </div>
            -->
          </div>
        </div>
      </div>
    </template>

    <!-- ── Modal Archivo adjunto ── -->
    <Teleport to="body">
      <div v-if="archivoModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(0,0,0,0.5)"
        @click.self="archivoModal = null"
      >
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden" style="max-width:700px;width:100%;max-height:90vh;display:flex;flex-direction:column">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
              <ion-icon name="document-attach-outline" style="font-size:18px;color:#475569" />
              <span class="text-sm font-bold text-[#0f1f3d] truncate">{{ archivoModal.nombre_original ?? 'Archivo adjunto' }}</span>
            </div>
            <button @click="archivoModal = null" class="text-gray-400 hover:text-gray-600">
              <ion-icon name="close-outline" style="font-size:22px" />
            </button>
          </div>

          <!-- Contenido -->
          <div class="flex-1 overflow-auto flex items-center justify-center p-6 bg-gray-50">
            <img v-if="esImagen(archivoModal)"
              :src="archivoModal.url"
              class="max-w-full max-h-full rounded-xl object-contain shadow"
              :alt="archivoModal.nombre_original"
            />
            <div v-else class="flex flex-col items-center gap-4 py-10">
              <ion-icon name="document-outline" style="font-size:64px;color:#9ca3af" />
              <p class="text-sm text-gray-500">{{ archivoModal.nombre_original }}</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
            <button @click="archivoModal = null"
              class="px-5 py-2 border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
              Cerrar
            </button>
            <button @click="descargarArchivo(archivoModal)"
              class="px-5 py-2 bg-[#0f1f3d] text-white text-sm font-bold rounded-xl hover:bg-[#152b52] transition-colors flex items-center gap-2">
              <ion-icon name="download-outline" style="font-size:15px" />
              Descargar
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal Agregar nota ── -->
    <Teleport to="body">
      <div v-if="mostrarFormNota"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(0,0,0,0.35)"
        @click.self="mostrarFormNota = false"
      >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-7" @click.stop>
          <h3 class="text-base font-black text-[#0f1f3d] mb-5">Agregar nota de seguimiento</h3>

          <textarea
            v-model="nuevaNota"
            rows="5"
            placeholder=""
            class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-100 rounded-xl focus:outline-none focus:border-[#2ecc71] resize-none mb-5"
            maxlength="1000"
          />

          <div class="flex items-center gap-3">
            <button
              @click="agregarSeguimiento"
              :disabled="!nuevaNota.trim() || enviando"
              class="px-7 py-2.5 bg-[#2ecc71] text-white text-sm font-bold rounded-xl hover:bg-[#27ae60] disabled:opacity-50 transition-colors flex items-center gap-2"
            >
              <div v-if="enviando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              Agregar
            </button>

            <button
              @click="mostrarFormNota = false"
              class="px-7 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>

            <!-- Adjuntar archivo -->
            <label class="flex items-center gap-2 ml-auto cursor-pointer group">
              <ion-icon name="attach-outline" style="font-size:18px;color:#9ca3af" class="group-hover:text-gray-600" />
              <span v-if="archivoAdjunto" class="text-xs text-gray-600 flex items-center gap-1">
                {{ archivoAdjunto.name }}
                <ion-icon name="checkmark-circle" style="font-size:14px;color:#2ecc71" />
              </span>
              <span v-else class="text-xs text-gray-400">Adjuntar archivo</span>
              <input type="file" class="hidden" @change="onArchivoChange" />
            </label>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const comentario      = ref(null)
const cargando        = ref(false)
const nuevaNota       = ref('')
const nuevoEstado     = ref('')
const enviando        = ref(false)
const mostrarFormNota = ref(false)
const estadoMenuOpen  = ref(false)
const editarCliente   = ref(false)
const archivoAdjunto  = ref(null)
const hoveredArchivo  = ref(null)

function onArchivoChange(e) {
  archivoAdjunto.value = e.target.files[0] ?? null
}
const clienteForm     = ref({ cliente_nombre: '', cliente_dni: '', cliente_telefono: '' })

const iconEmocion = { positiva: 'happy-outline', neutral: 'remove-circle-outline', negativa: 'sad-outline' }

const ESTADOS = [
  { value: 'pendiente',  label: 'Pendiente'  },
  { value: 'en_proceso', label: 'En Proceso' },
  { value: 'resuelto',   label: 'Resuelto'   },
]

function estadoLabel(e) {
  return { pendiente: 'Pendiente', en_proceso: 'En Proceso', resuelto: 'Resuelto' }[e] ?? e
}
function estadoColor(e) {
  return { pendiente: '#94a3b8', en_proceso: '#3b82f6', resuelto: '#2ecc71' }[e] ?? '#94a3b8'
}

function colorEmocion(e) {
  return { positiva: '#16a34a', neutral: '#64748b', negativa: '#dc2626' }[e] ?? '#64748b'
}
function bgEmocion(e) {
  return { positiva: '#dcfce7', neutral: '#f1f5f9', negativa: '#fee2e2' }[e] ?? '#f1f5f9'
}
function bgEmocionSuave(e) {
  return { positiva: '#f0fdf4', neutral: '#f8fafc', negativa: '#fff1f2' }[e] ?? '#f8fafc'
}

function dotColor(seg, i) {
  if (i === 0) return '#f59e0b'
  if (seg.accion === 'en_proceso') return '#3b82f6'
  if (seg.accion === 'resuelto')   return '#2ecc71'
  return '#94a3b8'
}

function formatFechaSeg(d) {
  if (!d) return ''
  const dt = new Date(d)
  return `${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}/${dt.getFullYear()}`
}
function formatHoraSeg(d) {
  if (!d) return ''
  const dt = new Date(d)
  return `${String(dt.getHours()).padStart(2,'0')}:${String(dt.getMinutes()).padStart(2,'0')}`
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get(`/comentarios/${route.params.id}`)
    comentario.value = data
    clienteForm.value = {
      cliente_nombre:   data.cliente_nombre   ?? '',
      cliente_dni:      data.cliente_dni       ?? '',
      cliente_telefono: data.cliente_telefono  ?? '',
    }
  } finally {
    cargando.value = false
  }
}

async function cambiarEstado(estado) {
  if (estado === comentario.value.estado) return
  await api.patch(`/comentarios/${comentario.value.id}`, { estado })
  comentario.value.estado = estado

  // Registrar hito en la bitácora
  try {
    const { data } = await api.post(`/comentarios/${comentario.value.id}/seguimientos`, {
      nota:   `Estado cambiado a "${estadoLabel(estado)}"`,
      accion: estado,
    })
    if (!comentario.value.seguimientos) comentario.value.seguimientos = []
    comentario.value.seguimientos.push(data)
  } catch {}
}

async function guardarCliente() {
  await api.patch(`/comentarios/${comentario.value.id}`, clienteForm.value)
  Object.assign(comentario.value, clienteForm.value)
  editarCliente.value = false
}

async function agregarSeguimiento() {
  if (!nuevaNota.value.trim()) return
  enviando.value = true
  try {
    let data

    if (archivoAdjunto.value) {
      const form = new FormData()
      form.append('nota',   nuevaNota.value)
      form.append('accion', nuevoEstado.value || '')
      form.append('archivos[]', archivoAdjunto.value)
      ;({ data } = await api.post(
        `/comentarios/${comentario.value.id}/seguimientos`,
        form,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      ))
    } else {
      ;({ data } = await api.post(`/comentarios/${comentario.value.id}/seguimientos`, {
        nota:   nuevaNota.value,
        accion: nuevoEstado.value || null,
      }))
    }

    if (!comentario.value.seguimientos) comentario.value.seguimientos = []
    comentario.value.seguimientos.push(data)
    if (nuevoEstado.value) comentario.value.estado = nuevoEstado.value
    nuevaNota.value       = ''
    nuevoEstado.value     = ''
    archivoAdjunto.value  = null
    mostrarFormNota.value = false
  } finally {
    enviando.value = false
  }
}

// ── Exportar PDF ──────────────────────────────────────────────
function exportarComentario() {
  const c = comentario.value
  const win = window.open('', '_blank')
  win.document.write(`<!DOCTYPE html><html lang="es"><head>
    <meta charset="UTF-8"/>
    <title>Comentario #${c.id}</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 32px; color: #1a202c; }
      h1 { font-size: 20px; color: #0f1f3d; margin-bottom: 4px; }
      p.sub { font-size: 12px; color: #6b7280; margin-bottom: 24px; }
      table { width: 100%; border-collapse: collapse; font-size: 13px; }
      thead tr { background: #0f1f3d; color: white; }
      th { padding: 10px 12px; text-align: left; font-weight: 600; }
      td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
      tbody tr:nth-child(even) { background: #f9fafb; }
      .badge { display:inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
      .pendiente  { background:#f1f5f9; color:#475569; }
      .en_proceso { background:#dbeafe; color:#1d4ed8; }
      .resuelto   { background:#dcfce7; color:#15803d; }
      .positiva   { background:#dcfce7; color:#15803d; }
      .neutral    { background:#f1f5f9; color:#475569; }
      .negativa   { background:#fee2e2; color:#dc2626; }
      h2 { font-size: 14px; color: #0f1f3d; margin: 28px 0 10px; }
      .bit td { font-size: 12px; }
    </style>
  </head><body>
    <h1>Emotix — Reporte de Comentario</h1>
    <p class="sub">Generado el ${new Date().toLocaleString('es-PE')}</p>

    <table>
      <thead><tr>
        <th>Fuente</th><th>Fecha</th><th>Hora</th>
        <th>Emoción</th><th>Comentario</th><th>Estado</th>
      </tr></thead>
      <tbody><tr>
        <td>${c.fuente?.nombre ?? '—'}</td>
        <td>${c.fecha ?? '—'}</td>
        <td>${c.hora?.slice(0,5) ?? '—'}</td>
        <td><span class="badge ${c.emocion}">${c.emocion ?? '—'}</span></td>
        <td>${c.comentario ?? '—'}</td>
        <td><span class="badge ${c.estado}">${estadoLabel(c.estado)}</span></td>
      </tr></tbody>
    </table>

    ${(c.seguimientos?.length) ? `
    <h2>Bitácora de seguimiento</h2>
    <table class="bit">
      <thead><tr><th>Fecha</th><th>Hora</th><th>Usuario</th><th>Nota / Acción</th></tr></thead>
      <tbody>${c.seguimientos.map(s => `<tr>
        <td>${formatFechaSeg(s.created_at)}</td>
        <td>${formatHoraSeg(s.created_at)}</td>
        <td>${s.usuario?.name ?? 'Sistema'}</td>
        <td>${s.accion ? `<span class="badge ${s.accion}">${estadoLabel(s.accion)}</span>` : (s.nota ?? '')} </td>
      </tr>`).join('')}</tbody>
    </table>` : ''}

  </body></html>`)
  win.document.close()
  win.focus()
  setTimeout(() => win.print(), 400)
}

// ── Modal archivo adjunto ──────────────────────────────────────
const archivoModal = ref(null)

function verArchivo(seg) {
  const arch = seg.archivos?.[0]
  if (!arch) return
  // Always use a relative path so requests go through the Vite proxy (/storage → http://emotix.test)
  let url
  if (arch.ruta_archivo) {
    url = `/storage/${arch.ruta_archivo}`
  } else if (arch.url) {
    try { url = new URL(arch.url).pathname } catch { url = arch.url }
  }
  archivoModal.value = {
    url,
    nombre_original: arch.nombre_original ?? 'Archivo',
  }
}

function esImagen(archivo) {
  return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(archivo.nombre_original ?? archivo.url ?? '')
}

function descargarArchivo(archivo) {
  const a = document.createElement('a')
  a.href = archivo.url
  a.download = archivo.nombre_original ?? 'archivo'
  a.click()
}

onMounted(cargar)
</script>
