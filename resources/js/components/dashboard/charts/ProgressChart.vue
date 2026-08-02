<script setup>
import { Line } from 'vue-chartjs'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import {
  Chart as ChartJS,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

ChartJS.register(
  Tooltip, Legend,
  LineElement, PointElement,
  CategoryScale, LinearScale,
  ChartDataLabels
)

const props = defineProps({
  reviews: {
    type: Array,
    required: true
  }
})

// Ordenar por fecha ASCENDENTE
const sortedReviews = [...props.reviews].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

// Fechas para el eje X
const labels = sortedReviews.map(r =>
  new Date(r.created_at).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short'
  })
)

const chartData = {
  labels,
  datasets: [
    {
      label: 'Peso corporal (kg)',
      data: sortedReviews.map(r => r.weight),
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
    legend: {
      display: true,
      position: 'bottom'
    },
    tooltip: {
      mode: 'index',
      intersect: false
    },
    datalabels: {
      color: '#374151',
      anchor: 'end',
      align: 'top',
      font: {
        weight: 'bold',
        size: 12
      },
      formatter: (value) => `${value} kg`
    }
  },
  scales: {
    y: {
      title: {
        display: true,
        text: 'Peso (kg)',
        color: '#6B7280'
      },
      ticks: {
        color: '#6B7280'
      }
    },
    x: {
      ticks: {
        color: '#6B7280'
      }
    }
  }
}
</script>

<template>
  <div class="w-full h-[400px]">
    <Line :data="chartData" :options="options" />
  </div>
</template>