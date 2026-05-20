<template>
  <div class="w-full" style="height:50px;overflow:hidden">
    <canvas ref="canvasRef" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler } from 'chart.js'

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler)

const props = defineProps({
  data: { type: Array, default: () => [] },
  color: { type: String, default: '#2ecc71' }
})

const canvasRef = ref(null)
let chart = null

function destroyChart() {
  if (chart) {
    chart.destroy()
    chart = null
  }
}

function buildChart() {
  if (!canvasRef.value || !props.data.length) return
  destroyChart()

  const ctx = canvasRef.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 50)
  gradient.addColorStop(0, props.color + '33')
  gradient.addColorStop(1, props.color + '00')

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.data.map((_, i) => i),
      datasets: [{
        data: props.data,
        borderColor: props.color,
        borderWidth: 2,
        fill: true,
        backgroundColor: gradient,
        tension: 0.4,
        pointRadius: 0
      }]
    },
    options: {
      responsive: false,
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      scales: { x: { display: false }, y: { display: false } },
      animation: false
    }
  })

  // Ajustar canvas al contenedor
  const container = canvasRef.value.parentElement
  if (container) {
    chart.resize(container.clientWidth, 50)
  }
}

onMounted(buildChart)
onUnmounted(destroyChart)
watch(() => props.data, buildChart, { deep: true })
</script>
