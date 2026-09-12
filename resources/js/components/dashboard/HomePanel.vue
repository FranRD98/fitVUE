<script setup>
import { ref, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { getWorkoutSessions } from '@/api/services/exercises'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'
import { IconBarbell, IconClock, IconClipboardList } from '@tabler/icons-vue'

const userStore = useUserStore()
const sessions = ref([])
const { loading, showSkeleton, start, finish } = useDelayedSkeleton(300)

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', {
    weekday: 'long', day: 'numeric', month: 'long'
  })
}

function formatDuration(seconds) {
  if (!seconds && seconds !== 0) return '—'
  const m = Math.floor(seconds / 60)
  const h = Math.floor(m / 60)
  if (h > 0) return `${h}h ${m % 60}min`
  return `${m}min`
}

onMounted(async () => {
  start()
  try {
    sessions.value = await getWorkoutSessions(userStore.userData?.uid)
  } finally {
    finish()
  }
})
</script>

<template>
  <section class="px-2 py-4 sm:px-6 space-y-6">
    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)]">Historial de entrenamientos</h1>

    <div v-if="loading && !showSkeleton" />
    <div v-else-if="loading && showSkeleton" class="space-y-3 animate-pulse">
      <div v-for="n in 4" :key="n" class="bg-gray-100 h-24 rounded-xl" />
    </div>

    <div v-else-if="sessions.length" class="space-y-4">
      <div
        v-for="(session, i) in sessions"
        :key="i"
        class="bg-white shadow rounded-xl p-4 flex items-center gap-4"
      >
        <div class="w-11 h-11 rounded-full bg-[rgba(var(--color-primary-rgb),0.1)] flex items-center justify-center shrink-0">
          <IconBarbell class="w-6 h-6 text-[var(--color-primary)]" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-800 truncate">{{ session.routine_title }}</p>
          <p class="text-xs text-gray-500 capitalize">{{ formatDate(session.created_at) }}</p>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-500 shrink-0">
          <span class="flex items-center gap-1" title="Duración">
            <IconClock class="w-4 h-4" /> {{ formatDuration(session.duration_seconds) }}
          </span>
          <span class="flex items-center gap-1" title="Ejercicios">
            <IconClipboardList class="w-4 h-4" /> {{ session.exercise_count }}
          </span>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 text-gray-500">
      <IconBarbell class="w-12 h-12 mb-3 text-gray-300" />
      <p class="font-semibold">Aún no has completado ningún entrenamiento</p>
      <p class="text-sm">Empieza una rutina desde "Entrenamiento" para ver aquí tu historial.</p>
    </div>
  </section>
</template>
