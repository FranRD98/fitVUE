<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCoachAssignedRoutine, getRoutineById } from '@/api/services/routines'
import { getLastExerciseProgress, saveExerciseProgress } from '@/api/services/exercises'
import { parseRepsTarget } from '@/utils/reps'
import { formatClock, formatSet, formatVolume, setsVolume } from '@/utils/workout'
import { useSwipeBack } from '@/composables/useSwipeBack'
import { IconCheck, IconPlus, IconTrash, IconX, IconClock } from '@tabler/icons-vue'

const route = useRoute()
const router = useRouter()

const routine = ref(null)
const loading = ref(true)
const exerciseInputs = ref([])
const userId = ref(null)
const saving = ref(false)
const summary = ref(null)

// El intervalo se congela cuando iOS suspende la PWA al cambiar de app.
// La duración se calcula con el reloj real, así al volver el tiempo
// recuperado es el que de verdad ha pasado.
const elapsedSeconds = ref(0)
let startedAt = 0
let tickHandle = null

const elapsedLabel = computed(() => formatClock(elapsedSeconds.value))

const restActive = ref(false)
const restOpen = ref(false)
const restRemaining = ref(0)
const restExerciseName = ref('')
let restEndsAt = 0

const restLabel = computed(() => formatClock(restRemaining.value))

const completedSets = computed(() =>
  exerciseInputs.value.reduce((total, ex) => total + ex.sets.filter((s) => s.done).length, 0)
)
const totalSets = computed(() =>
  exerciseInputs.value.reduce((total, ex) => total + ex.sets.length, 0)
)
const progressPct = computed(() =>
  totalSets.value ? Math.round((completedSets.value / totalSets.value) * 100) : 0
)

onMounted(async () => {
  document.addEventListener('visibilitychange', tick)
  window.addEventListener('pageshow', tick)
  window.addEventListener('focus', tick)

  userId.value = route.params.userId
  if (!userId.value) return

  try {
    const routineId = route.query.routineId
    const routineData = routineId
      ? await getRoutineById(routineId)
      : await getCoachAssignedRoutine(userId.value)
    routine.value = routineData

    if (routineData?.exercises?.length) {
      await loadExercises(routineData.exercises)
    }
  } catch (error) {
    console.error('Error al cargar la rutina:', error)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (tickHandle) clearInterval(tickHandle)
  document.removeEventListener('visibilitychange', tick)
  window.removeEventListener('pageshow', tick)
  window.removeEventListener('focus', tick)
})

function syncElapsed() {
  if (!startedAt) return
  elapsedSeconds.value = Math.max(0, Math.floor((Date.now() - startedAt) / 1000))
}

function syncRest() {
  if (!restActive.value) return
  const left = Math.ceil((restEndsAt - Date.now()) / 1000)
  if (left <= 0) {
    endRest(true)
    return
  }
  restRemaining.value = left
}

function tick() {
  syncElapsed()
  syncRest()
}

function startSessionClock() {
  startedAt = Date.now()
  elapsedSeconds.value = 0
  if (tickHandle) clearInterval(tickHandle)
  tickHandle = setInterval(tick, 1000)
}

function startRest(exercise) {
  const seconds = Number(exercise.restSeconds)
  if (!Number.isFinite(seconds) || seconds <= 0) return
  document.activeElement?.blur?.()
  restExerciseName.value = exercise.name
  restEndsAt = Date.now() + seconds * 1000
  restActive.value = true
  restOpen.value = true
  syncRest()
}

function endRest(completed) {
  restActive.value = false
  restOpen.value = false
  restRemaining.value = 0
  if (completed) navigator.vibrate?.(180)
}

function skipRest() {
  endRest(false)
}

function postponeRest() {
  if (!restActive.value) return
  restEndsAt += 30_000
  syncRest()
}

function closeRestSheet() {
  restOpen.value = false
}

const loadExercises = async (exercises) => {
  exerciseInputs.value = await Promise.all(
    exercises.map(async (exercise) => {
      const lastProgress = await getLastExerciseProgress(exercise.id, userId.value)
      const lastSets = lastProgress?.sets || []
      const plannedSets = exercise.sets || 1

      const sets = Array.from({ length: plannedSets }).map((_, i) => ({
        reps: '',
        weight: '',
        lastReps: lastSets[i]?.reps ?? parseRepsTarget(exercise.reps),
        lastWeight: lastSets[i]?.weight ?? null,
        done: false,
      }))

      return {
        exerciseId: exercise.id,
        name: exercise.name,
        note: exercise.note || '',
        targetReps: exercise.reps,
        restSeconds: exercise.rest ?? 90,
        sets,
        lastDate: lastProgress?.created_at ? new Date(lastProgress.created_at).toLocaleDateString('es-ES') : null,
      }
    })
  )

  startSessionClock()
}

function addSet(exercise) {
  const last = exercise.sets[exercise.sets.length - 1]
  exercise.sets.push({
    reps: '',
    weight: '',
    lastReps: last?.lastReps ?? null,
    lastWeight: last?.lastWeight ?? null,
    done: false,
  })
}

function removeSet(exercise) {
  if (exercise.sets.length <= 1) return
  exercise.sets.pop()
}

// Al marcar una serie, si no se ha escrito nada se rellena con lo de la última vez
// (o el objetivo de la rutina), igual que en apps como Hevy: un toque y listo.
function toggleDone(exercise, set) {
  if (!set.done) {
    if (set.weight === '') set.weight = set.lastWeight ?? 0
    if (set.reps === '') set.reps = set.lastReps ?? 0
    set.done = true
    const workoutComplete = exerciseInputs.value.every((item) => item.sets.every((itemSet) => itemSet.done))
    if (!workoutComplete) startRest(exercise)
    return
  }
  set.done = false
}

async function finishWorkout() {
  syncElapsed()
  const payload = exerciseInputs.value
    .map((exercise) => ({
      exerciseId: exercise.exerciseId,
      name: exercise.name,
      sets: exercise.sets
        .filter((s) => s.done)
        .map((s) => ({ reps: s.reps, weight: s.weight })),
    }))
    .filter((exercise) => exercise.sets.length)

  if (!payload.length) {
    alert('Marca al menos una serie como completada antes de finalizar.')
    return
  }

  saving.value = true
  try {
    await saveExerciseProgress(userId.value, routine.value.id, null, payload, elapsedSeconds.value)
    endRest(false)
    const volume = payload.reduce((total, exercise) => total + setsVolume(exercise.sets), 0)
    summary.value = {
      title: routine.value.title,
      duration: elapsedSeconds.value,
      exercises: payload,
      setCount: payload.reduce((total, exercise) => total + exercise.sets.length, 0),
      volume,
    }
  } catch (error) {
    console.error(error)
    const status = error.response?.status
    const serverMessage = error.response?.data?.message
    const validationErrors = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(' ')
      : null
    alert(
      'Error al guardar el progreso.' +
      (status ? ` (código ${status})` : ' (sin respuesta del servidor)') +
      (serverMessage ? `\n${serverMessage}` : '') +
      (validationErrors ? `\n${validationErrors}` : '')
    )
  } finally {
    saving.value = false
  }
}

function confirmExit() {
  if (completedSets.value > 0 && !confirm('¿Seguro que quieres salir? Perderás el progreso de este entrenamiento.')) {
    return
  }
  router.back()
}

function closeSummary() {
  router.push({ path: '/dashboard', query: { refresh: 'true' } })
}

// Deslizar desde el borde izquierdo también sale del entrenamiento.
useSwipeBack(() => {
  if (summary.value) {
    closeSummary()
    return
  }
  confirmExit()
})
</script>

<template>
  <!-- Cargando -->
  <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-[var(--color-primary)] text-white">
    Cargando rutina...
  </div>

  <div v-else-if="summary" class="fixed inset-0 bg-gray-50 z-50 flex flex-col">
    <header class="bg-white border-b shrink-0 pt-[calc(env(safe-area-inset-top)+0.75rem)] pb-3 px-4">
      <h1 class="font-bold text-[var(--color-primary)] text-center">Entrenamiento completado</h1>
      <p class="text-sm text-gray-500 text-center truncate">{{ summary.title }}</p>
    </header>

    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
      <div class="grid grid-cols-3 gap-2">
        <div class="bg-white rounded-xl border shadow-sm p-3 text-center">
          <p class="text-lg font-bold text-[var(--color-primary)] tabular-nums">{{ formatClock(summary.duration) }}</p>
          <p class="text-[11px] text-gray-400">Duración</p>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-3 text-center">
          <p class="text-lg font-bold text-[var(--color-primary)] tabular-nums">{{ summary.setCount }}</p>
          <p class="text-[11px] text-gray-400">Series</p>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-3 text-center">
          <p class="text-sm font-bold text-[var(--color-primary)] tabular-nums leading-tight">{{ formatVolume(summary.volume) }}</p>
          <p class="text-[11px] text-gray-400">Volumen</p>
        </div>
      </div>

      <div
        v-for="exercise in summary.exercises"
        :key="exercise.exerciseId"
        class="bg-white rounded-xl border shadow-sm p-4"
      >
        <div class="flex items-center justify-between gap-3 mb-2">
          <h2 class="font-semibold text-[var(--color-primary)]">{{ exercise.name }}</h2>
          <span class="text-xs text-gray-400 shrink-0">{{ exercise.sets.length }} series</span>
        </div>
        <ul class="space-y-1">
          <li
            v-for="(set, i) in exercise.sets"
            :key="i"
            class="flex items-center justify-between text-sm text-gray-600"
          >
            <span>Serie {{ i + 1 }}</span>
            <span class="tabular-nums font-medium">{{ formatSet(set) }}</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="shrink-0 border-t bg-white px-4 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)]">
      <button
        type="button"
        @click="closeSummary"
        class="w-full bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-3 rounded-xl transition"
      >
        Hecho
      </button>
    </div>
  </div>

  <!-- Sin rutina -->
  <div v-else-if="!routine || !routine.exercises?.length" class="fixed inset-0 flex justify-center items-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full text-gray-700 border border-red-200">
      <h2 class="text-lg font-semibold mb-4 text-red-600">Rutina no asignada</h2>
      <p class="mb-4">Debes asignarte una rutina antes de registrar un entrenamiento.</p>
      <button
        @click="$router.back()"
        class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-medium py-2 px-4 rounded"
      >
        Volver atrás
      </button>
    </div>
  </div>

  <!-- Entrenamiento en curso: todos los ejercicios visibles, sin pasos -->
  <div v-else class="fixed inset-0 bg-gray-50 z-50 flex flex-col">
    <!-- Barra superior -->
    <header class="bg-white border-b shrink-0 pt-[calc(env(safe-area-inset-top)+0.75rem)] pb-3 px-4 flex items-center justify-between gap-3">
      <button @click="confirmExit" class="text-gray-400 hover:text-gray-600 shrink-0" aria-label="Salir">
        <IconX class="w-6 h-6" />
      </button>
      <div class="text-center flex-1 min-w-0">
        <h1 class="font-bold text-[var(--color-primary)] truncate">{{ routine.title }}</h1>
      </div>
      <div class="flex items-center gap-1 text-sm font-semibold text-gray-600 tabular-nums shrink-0">
        <IconClock class="w-4 h-4" />
        {{ elapsedLabel }}
      </div>
    </header>

    <!-- Progreso general -->
    <div class="px-4 py-2 bg-white border-b shrink-0">
      <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
        <span>{{ completedSets }} / {{ totalSets }} series completadas</span>
        <span>{{ progressPct }}%</span>
      </div>
      <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
        <div
          class="h-full bg-[var(--color-primary)] transition-all duration-300"
          :style="{ width: progressPct + '%' }"
        ></div>
      </div>
    </div>

    <!-- Lista de ejercicios -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
      <div
        v-for="exercise in exerciseInputs"
        :key="exercise.exerciseId"
        class="bg-white rounded-xl shadow-sm border overflow-hidden"
      >
        <div class="px-4 py-3 border-b bg-gray-50">
          <h3 class="font-semibold text-[var(--color-primary)]">{{ exercise.name }}</h3>
          <p v-if="exercise.targetReps" class="text-xs text-gray-500">Objetivo: {{ exercise.targetReps }} reps</p>
          <p v-if="Number(exercise.restSeconds) > 0" class="text-xs text-gray-400">Descanso: {{ exercise.restSeconds }}s</p>
          <p v-if="exercise.note" class="text-xs text-gray-400">{{ exercise.note }}</p>
          <p v-if="exercise.lastDate" class="text-xs text-gray-400">Último entrenamiento: {{ exercise.lastDate }}</p>
        </div>

        <div class="px-2 pt-1">
          <div class="grid grid-cols-[2rem_1fr_3.5rem_3.5rem_2.5rem] gap-2 px-2 py-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
            <span>Serie</span>
            <span>Anterior</span>
            <span class="text-center">Kg</span>
            <span class="text-center">Reps</span>
            <span></span>
          </div>

          <div
            v-for="(set, i) in exercise.sets"
            :key="i"
            class="grid grid-cols-[2rem_1fr_3.5rem_3.5rem_2.5rem] gap-2 items-center px-2 py-1.5 rounded-lg transition-colors"
            :class="set.done ? 'bg-emerald-50' : ''"
          >
            <span class="text-sm font-bold text-gray-500">{{ i + 1 }}</span>
            <span class="text-xs text-gray-400 truncate">
              {{ set.lastWeight != null ? `${set.lastWeight}kg × ${set.lastReps}` : (set.lastReps != null ? `${set.lastReps} reps` : '—') }}
            </span>
            <input
              v-model="set.weight"
              type="number"
              inputmode="decimal"
              class="set-input"
              :placeholder="set.lastWeight ?? '0'"
              :disabled="set.done"
            />
            <input
              v-model="set.reps"
              type="number"
              inputmode="numeric"
              class="set-input"
              :placeholder="set.lastReps ?? '0'"
              :disabled="set.done"
            />
            <button
              type="button"
              @click="toggleDone(exercise, set)"
              class="w-8 h-8 rounded-lg flex items-center justify-center border transition-colors"
              :class="set.done
                ? 'bg-[var(--color-primary)] border-[var(--color-primary)] text-white'
                : 'border-gray-300 text-gray-300 hover:border-gray-400'"
              :aria-label="set.done ? 'Serie completada' : 'Marcar serie como completada'"
            >
              <IconCheck class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="px-4 py-3 flex items-center justify-between border-t bg-gray-50">
          <button
            type="button"
            @click="addSet(exercise)"
            class="text-sm font-medium text-[var(--color-primary)] hover:underline flex items-center gap-1"
          >
            <IconPlus class="w-4 h-4" /> Añadir serie
          </button>
          <button
            v-if="exercise.sets.length > 1"
            type="button"
            @click="removeSet(exercise)"
            class="text-sm text-gray-400 hover:text-red-500 flex items-center gap-1"
          >
            <IconTrash class="w-4 h-4" /> Quitar
          </button>
        </div>
      </div>
    </div>

    <button
      v-if="restActive && !restOpen"
      type="button"
      @click="restOpen = true"
      class="shrink-0 border-t bg-white px-4 py-2.5 flex items-center justify-between gap-3"
    >
      <span class="text-sm text-gray-500 truncate">Descanso · {{ restExerciseName }}</span>
      <span class="text-base font-bold tabular-nums text-[var(--color-primary)]">{{ restLabel }}</span>
    </button>

    <!-- Finalizar -->
    <div class="shrink-0 border-t bg-white px-4 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)]">
      <button
        type="button"
        @click="finishWorkout"
        :disabled="saving"
        class="w-full bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition"
      >
        {{ saving ? 'Guardando...' : 'Finalizar entrenamiento' }}
      </button>
    </div>

    <div
      v-if="restOpen"
      class="absolute inset-0 z-10 flex flex-col justify-end bg-black/40"
      @click.self="closeRestSheet"
    >
      <div class="bg-white rounded-t-3xl px-5 pt-6 pb-[calc(env(safe-area-inset-bottom)+1.25rem)] shadow-xl">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 text-center">Descanso</p>
        <p class="text-sm text-gray-500 text-center truncate mt-1">{{ restExerciseName }}</p>
        <p class="text-6xl font-bold text-center tabular-nums text-[var(--color-primary)] my-4">{{ restLabel }}</p>
        <div class="grid grid-cols-3 gap-2">
          <button
            type="button"
            @click="skipRest"
            class="py-3 rounded-xl bg-[var(--color-primary)] text-white text-sm font-semibold"
          >
            Seguir
          </button>
          <button
            type="button"
            @click="postponeRest"
            class="py-3 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700"
          >
            Posponer
          </button>
          <button
            type="button"
            @click="closeRestSheet"
            class="py-3 rounded-xl text-sm font-semibold text-gray-500"
          >
            Cerrar
          </button>
        </div>
        <p class="text-center text-xs text-gray-400 mt-3">Posponer suma 30 segundos. Cerrar deja el contador abajo.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.set-input {
  width: 100%;
  padding: 0.4rem 0.25rem;
  text-align: center;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  /* 16px mínimo: por debajo de esto, iOS hace zoom automático al enfocar el input
     aunque el viewport tenga user-scalable=no */
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}
.set-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
}
.set-input:disabled {
  background-color: #f3f4f6;
  color: #6b7280;
}
</style>
