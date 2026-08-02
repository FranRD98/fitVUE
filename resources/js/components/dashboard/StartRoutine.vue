<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getAssignedRoutine, getCoachAssignedRoutine } from '@/api/services/routines'
import { getLastExerciseProgress, saveExerciseProgress } from '@/api/services/exercises'

const route = useRoute()
const router = useRouter()

const routine = ref(null)
const loading = ref(true)
const step = ref(0)
const inputRefs = ref([])
const exerciseInputs = ref([])
const selectedDay = ref(null)
const showDaySelector = ref(false)
const userId = ref(null)

const currentExercise = computed(() => selectedDay.value?.exercises?.[step.value])
const totalSteps = computed(() => selectedDay.value?.exercises?.length || 0)

onMounted(async () => {
  userId.value = route.params.userId
  if (!userId.value) return

  try {
    const routineData = await getCoachAssignedRoutine(userId.value) || await getAssignedRoutine(userId.value)
    routine.value = routineData

    if (routineData?.days?.length) {
      showDaySelector.value = true
    }
  } catch (error) {
    console.error('Error al cargar la rutina:', error)
  } finally {
    loading.value = false
  }
})

const handleDaySelection = async (dayObj) => {
  selectedDay.value = dayObj
  step.value = 0
  showDaySelector.value = false

  exerciseInputs.value = await Promise.all(
    dayObj.exercises.map(async (exercise) => {
      const lastProgress = await getLastExerciseProgress(exercise.id, userId.value)

      const lastSets = lastProgress?.sets || []
      const sets = Array.from({ length: exercise.sets }).map((_, i) => ({
        reps: '',
        weight: '',
        lastReps: lastSets[i]?.reps || null,
        lastWeight: lastSets[i]?.weight || null
      }))

      return {
        exerciseId: exercise.id,
        name: exercise.name,
        sets,
        lastDate: lastProgress?.created_at ? new Date(lastProgress.created_at).toLocaleDateString('es-ES') : null
      }
    })
  )
}

watch(step, async () => {
  await nextTick()
  inputRefs.value[0]?.focus()
})

const handleNext = async () => {
  if (step.value < totalSteps.value - 1) {
    step.value++
  } else {
    try {
      await saveExerciseProgress(
        userId.value,
        routine.value.id,
        selectedDay.value.day,
        exerciseInputs.value
      )
      alert('¡Progreso guardado con éxito!')
    } catch (error) {
      console.error(error)
      alert('Error al guardar el progreso.')
    } finally {
      router.push('/dashboard')
    }
  }
}

const handleBack = () => {
  if (step.value > 0) {
    step.value--
  }
}
</script>

<template>
  <!-- Cargando -->
  <div v-if="loading" class="text-center p-6 text-white">Cargando rutina...</div>

  <!-- Sin rutina -->
  <div v-else-if="!routine || !routine.days?.length" class="flex justify-center items-center text-center p-10">
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

  <!-- Selección de día -->
  <div v-else-if="showDaySelector" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold text-[var(--color-primary)] mb-4">Selecciona un día</h2>
      <ul class="space-y-2">
        <li v-for="day in routine.days" :key="day.day">
          <button
            @click="handleDaySelection(day)"
            class="w-full px-4 py-2 bg-[var(--color-primary)] text-white rounded hover:bg-[var(--color-secondary)] transition"
          >
            {{ day.day }}
          </button>
        </li>
      </ul>
    </div>
  </div>

  <!-- Rutina paso a paso -->
  <div v-else class="fixed inset-0 bg-[var(--color-primary)] bg-opacity-60 backdrop-blur-sm flex justify-center items-start overflow-y-auto py-6 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl h-auto flex flex-col relative overflow-hidden max-h-[90vh]">

      <!-- Header -->
      <div class="text-center py-6 border-b">
        <h2 class="text-xl font-bold text-[var(--color-primary)]">
          {{ routine.title }} — {{ selectedDay.day }}
        </h2>
        <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden mt-2 mx-6">
          <div
            class="absolute top-0 left-0 h-full bg-[var(--color-primary)] transition-all duration-500"
            :style="{ width: ((step + 1) / totalSteps * 100) + '%' }"
          ></div>
        </div>
      </div>

      <!-- Ejercicio -->
      <div :key="step" class="flex-1 flex flex-col items-center text-center p-6 gap-6 overflow-y-auto">
        <h3 class="text-xl font-semibold text-gray-700">{{ currentExercise.name }}</h3>

        <p class="text-sm text-gray-500 mb-4">
          Último peso registrado: <strong>{{ exerciseInputs[step]?.lastWeight }} kg</strong>
          <span v-if="exerciseInputs[step]?.lastDate"> ({{ exerciseInputs[step].lastDate }})</span>
        </p>

        <div class="w-full max-w-sm space-y-6">
          <div
  v-for="(set, i) in exerciseInputs[step]?.sets"
  :key="i"
  class="bg-gray-50 p-4 rounded-lg shadow-sm border"
>
  <h4 class="text-lg font-bold text-[var(--color-primary)] mb-2">Serie {{ i + 1 }}</h4>

  <div class="grid grid-cols-2 gap-4">
    <div class="flex flex-col">
      <label class="text-sm text-left text-gray-600 mb-2">Peso (kg)</label>
      <input
        v-model="set.weight"
        type="number"
        class="input"
        :placeholder="set.lastWeight !== null ? `Anterior: ${set.lastWeight} kg` : 'Peso'"
      />

    </div>

    <div class="flex flex-col">
      <label class="text-sm text-left text-gray-600 mb-2">Reps</label>
      <input
        v-model="set.reps"
        type="number"
        class="input"
        :placeholder="set.lastReps !== null ? `Anterior: ${set.lastReps}` : 'Reps'"
      />
    </div>
  </div>

  <p v-if="exerciseInputs[step]?.lastDate" class="text-xs text-gray-400 mt-2">
    Último entrenamiento: {{ exerciseInputs[step].lastDate }}
  </p>
</div>


        </div>
      </div>

      <!-- Navegación -->
      <div class="p-6 flex justify-between items-center border-t">
        <button
          v-if="step > 0"
          @click="handleBack"
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700"
        >
          Atrás
        </button>

        <button
          @click="handleNext"
          class="ml-auto px-6 py-2 bg-[var(--color-primary)] text-white rounded-lg hover:bg-[var(--color-secondary)]"
        >
          {{ step === totalSteps - 1 ? 'Finalizar' : 'Siguiente' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  width: 100%;
  outline: none;
  transition: border-color 0.2s ease;
}
.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
}
</style>