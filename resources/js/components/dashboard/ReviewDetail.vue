<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getReviewById } from '@/api/services/progress'
import { IconArrowLeft } from '@tabler/icons-vue'
import ReviewRadarChart from '@/components/dashboard/charts/ReviewRadarChart.vue'

const route = useRoute()
const router = useRouter()
const review = ref(null)
const loading = ref(true)

const fieldLabels = {
  weight: 'Peso corporal',
  neck: 'Cuello',
  shoulders: 'Hombros',
  chest: 'Pecho',
  biceps_relaxed: 'Bíceps relajado',
  biceps_flexed: 'Bíceps contraído',
  forearm: 'Antebrazo',
  wrist: 'Muñeca',
  waist: 'Cintura',
  abdomen: 'Abdomen',
  hips: 'Cadera',
  quadriceps: 'Cuádriceps',
  calves: 'Gemelos'
}

const radarData = computed(() => {
  if (!review.value) return {}

  const radarFields = [
    'neck', 'shoulders', 'chest',
    'biceps_relaxed', 'biceps_flexed',
    'forearm', 'waist', 'abdomen',
    'hips', 'quadriceps', 'calves'
  ]

  return radarFields.reduce((acc, key) => {
    if (review.value[key] !== null && review.value[key] !== undefined) {
      acc[fieldLabels[key]] = review.value[key]
    }
    return acc
  }, {})
})

const formattedFields = computed(() => {
  if (!review.value) return []
  return Object.entries(fieldLabels)
    .map(([key, label]) => ({ key, label, value: review.value[key] }))
    .filter(entry => entry.value !== null && entry.value !== undefined)
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

function goBack() {
  router.back()
}

onMounted(async () => {
  const { userId, reviewId } = route.params
  if (!userId || !reviewId) return

  try {
    review.value = await getReviewById(userId, reviewId)
  } catch (error) {
    console.error('Error al cargar la revisión:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="w-full max-w-6xl mx-auto px-4 py-10 animate-fade-slide">
    
    <!-- Volver atrás -->
    <button
      @click="goBack"
      class="flex items-center gap-2 text-sm text-[var(--color-primary)] hover:underline mb-6"
    >
      <IconArrowLeft class="w-4 h-4" />
      Volver
    </button>

    <!-- Cargando / No data -->
    <div v-if="loading" class="text-center text-gray-500 py-20">Cargando revisión...</div>
    <div v-else-if="!review" class="text-center text-gray-500 py-20">No se encontró la revisión.</div>

    <!-- Contenido -->
    <div v-else>
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-[var(--color-primary)] mb-1">
          Revisión del {{ formatDate(review.created_at) }}
        </h1>
        <p class="text-sm text-gray-500">Mediciones físicas tomadas en esta fecha.</p>
      </div>


      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        <!-- Gráfico -->
        <div>
          <ReviewRadarChart v-if="Object.keys(radarData).length" :data="radarData" />
        </div>

        <!-- Cards de medidas -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div
            v-for="item in formattedFields"
            :key="item.key"
            class="bg-white border border-gray-200 p-5 rounded-xl shadow-sm hover:shadow-md transition"
          >
            <h3 class="text-sm font-medium text-gray-500 mb-1">{{ item.label }}</h3>
              <p class="text-2xl font-semibold text-[var(--color-primary)]">
                {{ item.value }} {{ item.key === 'weight' ? 'kg' : 'cm' }}
              </p>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<style scoped>
@keyframes fade-slide {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-slide {
  animation: fade-slide 0.4s ease-out;
}
</style>