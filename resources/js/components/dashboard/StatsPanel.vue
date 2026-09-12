<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { getExerciseStats } from '@/api/services/exercises'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'
import { IconTrophy } from '@tabler/icons-vue'

const userStore = useUserStore()
const stats = ref([])
const searchQuery = ref('')
const { loading, showSkeleton, start, finish } = useDelayedSkeleton(200)

const filteredStats = computed(() =>
  stats.value.filter(s => (s.exercise_name || '').toLowerCase().includes(searchQuery.value.toLowerCase()))
)

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  start()
  try {
    stats.value = await getExerciseStats(userStore.userData?.uid)
  } finally {
    finish()
  }
})
</script>

<template>
  <section>
    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)] mb-1">Estadísticas</h1>
    <p class="text-sm text-gray-500 mb-6">Peso máximo levantado en cada ejercicio.</p>

    <div v-if="loading && !showSkeleton" />
    <div v-else-if="loading && showSkeleton" class="space-y-3 animate-pulse">
      <div v-for="n in 5" :key="n" class="bg-gray-100 h-14 rounded-xl" />
    </div>

    <div v-else>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Buscar ejercicio..."
        class="w-full border border-gray-300 rounded p-2 text-sm text-gray-700 mb-4 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)]"
      />

      <div v-if="filteredStats.length" class="bg-gray-100 md:bg-white shadow rounded-xl divide-y divide-gray-100 overflow-hidden">
        <div v-for="stat in filteredStats" :key="stat.exercise_id" class="flex items-center gap-3 px-4 py-3">
          <div class="w-9 h-9 rounded-full bg-[rgba(var(--color-primary-rgb),0.1)] flex items-center justify-center shrink-0">
            <IconTrophy class="w-5 h-5 text-[var(--color-primary)]" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ stat.exercise_name || 'Ejercicio' }}</p>
            <p class="text-xs text-gray-400">{{ formatDate(stat.achieved_at) }}</p>
          </div>
          <p class="text-lg font-bold text-[var(--color-primary)] shrink-0">{{ stat.max_weight }} kg</p>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-16 text-gray-500">
        <IconTrophy class="w-12 h-12 mb-3 text-gray-300" />
        <p class="font-semibold">Aún no hay récords registrados</p>
        <p class="text-sm">Completa series con peso en un entrenamiento para verlas aquí.</p>
      </div>
    </div>
  </section>
</template>
