<script setup>
import { Radar } from 'vue-chartjs'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler
} from 'chart.js'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  ChartDataLabels
)

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const labels = Object.keys(props.data)
const values = Object.values(props.data)

const chartData = {
  labels,
  datasets: [
    {
      label: 'Distribución corporal',
      data: values,
      backgroundColor: 'rgba(59, 130, 246, 0.2)',
      borderColor: '#3B82F6',
      borderWidth: 2,
      pointBackgroundColor: '#3B82F6'
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    r: {
      beginAtZero: true,
      min: 0,
      max: Math.max(...values) + 10,
      pointLabels: {
        font: { size: 12 }
      },
      ticks: {
        display: false
      }
    }
  },
  plugins: {
    legend: { display: false },
    datalabels: {
      color: '#000',
      font: { weight: 'bold' },
      formatter: (value, context) => {
        const label = context.chart.data.labels[context.dataIndex]
        const isWeight = label.toLowerCase().includes('peso') // Preciso y flexible
        return `${value} ${isWeight ? 'kg' : 'cm'}`
      },
      anchor: 'end',
      align: 'end'
    }
  }
}
</script>

<template>
  <div class="w-full h-[350px] sm:h-[400px]">
    <Radar :data="chartData" :options="options" />
  </div>
</template>