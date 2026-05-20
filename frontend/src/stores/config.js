import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export const useConfigStore = defineStore('config', () => {
  const logoUrl = ref(null)
  const organizacion = ref('')

  async function cargar() {
    try {
      const { data } = await api.get('/configuracion/general')
      logoUrl.value = data.logo_url ? `${data.logo_url}?v=${Date.now()}` : null
      organizacion.value = data.organizacion ?? ''
    } catch {}
  }

  function setLogo(url) {
    logoUrl.value = url ? `${url}?v=${Date.now()}` : null
  }

  return { logoUrl, organizacion, cargar, setLogo }
})
