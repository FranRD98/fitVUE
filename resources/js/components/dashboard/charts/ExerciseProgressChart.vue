<script setup>
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

ChartJS.register(
  Title, Tooltip, Legend,
  LineElement, PointElement,
  CategoryScale, LinearScale
)

const props = defineProps({
  history: {
    type: Array,
    required: true
  }
})

// Ordenar por fecha ASCENDENTE (más antiguo primero)
const sortedHistory = [...props.history].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

// Fechas para el eje X
const labels = sortedHistory.map(entry =>
  new Date(entry.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })
)

// Obtener el peso máximo de cada día
const maxWeights = sortedHistory.map(entry => {
  const weights = entry.sets.map(set => set.weight)
  return weights.length ? Math.max(...weights) : 0
})

const chartData = {
  labels,
  datasets: [
    {
      label: 'Peso máximo (kg)',
      data: maxWeights,
      borderColor: '#3B82F6',
      backgroundColor: '#3B82F660',
      tension: 0.3,
      pointRadius: 5,
      pointHoverRadius: 7,
      borderWidth: 2
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' },
    tooltip: { mode: 'index', intersect: false },
    title: { display: false }
  },
  scales: {
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Kg levantados'
      }
    }
  }
}
</script>

<template>
  <div class="w-full h-[300px] sm:h-[400px]">
    <Line :data="chartData" :options="options" />
  </div>
</template>