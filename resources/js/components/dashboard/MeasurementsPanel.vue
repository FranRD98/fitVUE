<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { getReviewsById } from '@/api/services/progress'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'
import { IconRuler2 } from '@tabler/icons-vue'

const userStore = useUserStore()
const reviews = ref([])
const { loading, showSkeleton, start, finish } = useDelayedSkeleton(200)

const FIELDS = [
  { key: 'weight', label: 'Peso', unit: 'kg' },
  { key: 'neck', label: 'Cuello', unit: 'cm' },
  { key: 'shoulders', label: 'Hombros', unit: 'cm' },
  { key: 'chest', label: 'Pecho', unit: 'cm' },
  { key: 'biceps_relaxed', label: 'Bíceps (relajado)', unit: 'cm' },
  { key: 'biceps_flexed', label: 'Bíceps (flexionado)', unit: 'cm' },
  { key: 'forearm', label: 'Antebrazo', unit: 'cm' },
  { key: 'wrist', label: 'Muñeca', unit: 'cm' },
  { key: 'waist', label: 'Cintura', unit: 'cm' },
  { key: 'abdomen', label: 'Abdomen', unit: 'cm' },
  { key: 'hips', label: 'Cadera', unit: 'cm' },
  { key: 'quadriceps', label: 'Cuádriceps', unit: 'cm' },
  { key: 'calves', label: 'Gemelos', unit: 'cm' },
]

function filledFields(review) {
  return FIELDS.filter(f => review[f.key] !== null && review[f.key] !== undefined)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' })
}

onMounted(async () => {
  start()
  try {
    reviews.value = await getReviewsById(userStore.userData?.uid)
  } finally {
    finish()
  }
})
</script>

<template>
  <section>
    <div class="flex items-center justify-between mb-1">
      <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)]">Medidas</h1>
      <router-link
        to="/dashboard/newReview"
        class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white px-4 py-2 rounded-lg shadow transition text-sm"
      >
        Nueva medición
      </router-link>
    </div>
    <p class="text-sm text-gray-500 mb-6">Histórico de tus medidas corporales.</p>

    <div v-if="loading && !showSkeleton" />
    <div v-else-if="loading && showSkeleton" class="space-y-3 animate-pulse">
      <div v-for="n in 4" :key="n" class="bg-gray-100 h-24 rounded-xl" />
    </div>

    <div v-else-if="reviews.length" class="space-y-4">
      <div v-for="review in reviews" :key="review.id" class="bg-white shadow rounded-xl p-4">
        <p class="text-sm font-semibold text-[var(--color-primary)] mb-3">{{ formatDate(review.created_at) }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <div v-for="field in filledFields(review)" :key="field.key">
            <p class="text-xs text-gray-400">{{ field.label }}</p>
            <p class="text-sm font-semibold text-gray-800">{{ review[field.key] }} {{ field.unit }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 text-gray-500">
      <IconRuler2 class="w-12 h-12 mb-3 text-gray-300" />
      <p class="font-semibold">Aún no has registrado medidas</p>
      <p class="text-sm">Crea tu primera medición con el botón de arriba.</p>
    </div>
  </section>
</template>
