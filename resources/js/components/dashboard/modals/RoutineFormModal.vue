<script setup>
import { ref, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import { createRoutine, updateRoutine } from '@/api/services/routines'
import ExercisePickerSheet from '@/components/dashboard/pickers/ExercisePickerSheet.vue'
import { isValidReps } from '@/utils/reps'
import { IconX, IconPlus, IconArrowUp, IconArrowDown, IconChevronDown, IconGripVertical } from '@tabler/icons-vue'

// Props y emits
const props = defineProps({
  show: Boolean,
  initialData: Object
})

const emit = defineEmits(['close', 'saved'])

// Estado
const userStore = useUserStore()
const showExercisePicker = ref(false)
const collapsedExercises = ref(new Set())
const draggingIndex = ref(null)
const dragOverIndex = ref(null)

const routine = ref({
  title: '',
  description: '',
  exercises: [],
  user_id: '',
  published: false
})

// Rellenar el formulario si se edita una rutina
watch(() => props.initialData, (newVal) => {
  if (newVal) {
    routine.value = {
      id: newVal.id,
      title: newVal.title || '',
      description: newVal.description || '',
      exercises: (newVal.exercises || []).map(ex => ({ ...ex, note: ex.note || '' })),
      published: newVal.published ?? false,
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function addExercises(newExercises) {
  for (const exercise of newExercises) {
    routine.value.exercises.push({
      id: exercise.id,
      name: exercise.name,
      sets: null,
      reps: null,
      note: ''
    })
  }
  showExercisePicker.value = false
}

function removeExercise(index) {
  routine.value.exercises.splice(index, 1)
}

function moveExercise(index, direction) {
  const target = index + direction
  if (target < 0 || target >= routine.value.exercises.length) return

  const exercises = routine.value.exercises
  ;[exercises[index], exercises[target]] = [exercises[target], exercises[index]]
}

// Plegar/desplegar una tarjeta: se referencia por el propio objeto del
// ejercicio (no por índice), así el estado no se descoloca al reordenar.
function toggleCollapse(exercise) {
  const next = new Set(collapsedExercises.value)
  if (next.has(exercise)) {
    next.delete(exercise)
  } else {
    next.add(exercise)
  }
  collapsedExercises.value = next
}

function isCollapsed(exercise) {
  return collapsedExercises.value.has(exercise)
}

// Reordenar arrastrando la tarjeta (drag & drop nativo)
function onDragStart(index, event) {
  draggingIndex.value = index
  event.dataTransfer.effectAllowed = 'move'
}

function onDragOver(index) {
  if (draggingIndex.value === null) return
  dragOverIndex.value = index
}

function onDrop(index) {
  if (draggingIndex.value === null || draggingIndex.value === index) {
    draggingIndex.value = null
    dragOverIndex.value = null
    return
  }

  const exercises = routine.value.exercises
  const [moved] = exercises.splice(draggingIndex.value, 1)
  exercises.splice(index, 0, moved)
  draggingIndex.value = null
  dragOverIndex.value = null
}

function onDragEnd() {
  draggingIndex.value = null
  dragOverIndex.value = null
}

// Enviar el formulario
async function submitForm() {
  if (!routine.value.title) {
    alert('El título de la rutina es obligatorio.')
    return
  }

  const invalidReps = routine.value.exercises.find(ex => !isValidReps(ex.reps))
  if (invalidReps) {
    alert(`Repeticiones no válidas en "${invalidReps.name}". Usa un valor exacto (12) o un rango (6-12).`)
    return
  }

  try {
    if (routine.value.id) {
      await updateRoutine(routine.value.id, routine.value)
    } else {
      routine.value.user_id = userStore.userData?.uid
      await createRoutine(routine.value)
    }

    emit('saved')
    close()
  } catch (error) {
    console.error('Error al guardar la rutina:', error)
    alert(error.response?.data?.message || 'Ocurrió un error al guardar la rutina.')
  }
}

// Cerrar y resetear
function close() {
  resetForm()
  emit('close')
}

// Resetear formulario
function resetForm() {
  routine.value = {
    title: '',
    description: '',
    exercises: []
  }
  collapsedExercises.value = new Set()
}
</script>


<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/50 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-3xl md:max-h-[90vh] bg-white md:rounded-2xl shadow-2xl flex flex-col overflow-hidden">

      <!-- Header -->
      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="emit('close')" class="text-[var(--color-primary)] font-medium">Cancelar</button>
        <h2 class="font-semibold text-[var(--color-primary)]">
          {{ routine.id ? 'Editar Rutina' : 'Crear Rutina' }}
        </h2>
        <button type="button" @click="submitForm" class="text-[var(--color-primary)] font-semibold">Guardar</button>
      </header>

      <!-- Formulario -->
      <div class="flex-1 overflow-y-auto px-4 py-4">
        <form @submit.prevent="submitForm" class="space-y-5">

          <input v-model="routine.title" placeholder="Título de la Rutina" class="input text-lg font-semibold" required />
          <input v-model="routine.description" placeholder="Descripción (opcional)" class="input" />

          <!-- Solo visible si el usuario es admin: publica la rutina como rutina pública
               de ejemplo, visible para cualquier visitante en la web (/rutinas) -->
          <div v-if="userStore.userData?.role === 'admin'" class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-xl p-3">
            <label class="flex items-center gap-2 cursor-pointer select-none shrink-0">
              <input type="checkbox" v-model="routine.published" class="sr-only" />
              <div
                class="w-10 h-6 flex items-center bg-gray-300 rounded-full p-1 duration-300 ease-in-out"
                :class="{ 'bg-green-500': routine.published }"
              >
                <div
                  class="bg-white w-4 h-4 rounded-full shadow-md transform duration-300 ease-in-out"
                  :class="{ 'translate-x-4': routine.published }"
                ></div>
              </div>
            </label>
            <div>
              <p class="text-sm font-medium text-gray-700">Publicar como rutina pública</p>
              <p class="text-xs text-gray-500">Aparecerá en la web para cualquier visitante, en el listado público de rutinas (/rutinas). No afecta a tus rutinas personales.</p>
            </div>
          </div>

          <!-- Lista plana de ejercicios -->
          <div class="space-y-3 pt-2">
            <TransitionGroup name="drop-fade" tag="div" class="space-y-3">
              <div
                v-for="(exercise, index) in routine.exercises"
                :key="index"
                draggable="true"
                @dragstart="onDragStart(index, $event)"
                @dragover.prevent="onDragOver(index)"
                @drop.prevent="onDrop(index)"
                @dragend="onDragEnd"
                class="bg-white border-2 rounded-xl shadow-sm px-4 py-3 transition-colors"
                :class="[
                  draggingIndex === index ? 'border-dashed border-[var(--color-primary)] opacity-50' : 'border-gray-200',
                  dragOverIndex === index && draggingIndex !== index ? 'border-t-4 border-t-[var(--color-primary)]' : ''
                ]"
              >
                <div class="flex items-center gap-2 mb-2">
                  <IconGripVertical class="w-4 h-4 text-gray-300 shrink-0 cursor-grab active:cursor-grabbing" />
                  <button
                    type="button"
                    @click="toggleCollapse(exercise)"
                    class="flex items-center gap-1 min-w-0 flex-1 text-left"
                  >
                    <IconChevronDown
                      class="w-4 h-4 text-gray-400 shrink-0 transition-transform"
                      :class="isCollapsed(exercise) ? '-rotate-90' : ''"
                    />
                    <h3 class="text-[var(--color-primary)] font-semibold text-base min-w-0 truncate">
                      {{ exercise.name }}
                    </h3>
                    <span v-if="isCollapsed(exercise)" class="text-xs text-gray-400 shrink-0">
                      {{ exercise.sets || 0 }}x{{ exercise.reps || 0 }}
                    </span>
                  </button>
                  <div class="flex items-center gap-1 shrink-0">
                    <button
                      type="button"
                      @click="moveExercise(index, -1)"
                      :disabled="index === 0"
                      class="text-gray-400 hover:text-[var(--color-primary)] disabled:opacity-30 disabled:hover:text-gray-400 p-1"
                      aria-label="Subir"
                    >
                      <IconArrowUp class="w-4 h-4" />
                    </button>
                    <button
                      type="button"
                      @click="moveExercise(index, 1)"
                      :disabled="index === routine.exercises.length - 1"
                      class="text-gray-400 hover:text-[var(--color-primary)] disabled:opacity-30 disabled:hover:text-gray-400 p-1"
                      aria-label="Bajar"
                    >
                      <IconArrowDown class="w-4 h-4" />
                    </button>
                    <button type="button" @click="removeExercise(index)" class="text-red-500 text-sm hover:underline flex items-center gap-1 ml-1">
                      <IconX class="w-4 h-4" /> Quitar
                    </button>
                  </div>
                </div>

                <div v-if="!isCollapsed(exercise)">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs text-gray-500 mb-1">Series</label>
                      <input v-model.number="exercise.sets" type="number" min="1" class="input text-sm" placeholder="0" />
                    </div>
                    <div>
                      <label class="block text-xs text-gray-500 mb-1">Repeticiones</label>
                      <input v-model="exercise.reps" type="text" inputmode="numeric" class="input text-sm" placeholder="12 o 6-12" />
                    </div>
                  </div>

                  <div class="mt-3">
                    <label class="block text-xs text-gray-500 mb-1">Nota (opcional)</label>
                    <input v-model="exercise.note" type="text" class="input text-sm" placeholder="Ej. con mancuernas, agarre estrecho..." />
                  </div>
                </div>
              </div>
            </TransitionGroup>

            <button
              type="button"
              @click="showExercisePicker = true"
              class="w-full flex items-center justify-center gap-2 bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-3 rounded-xl transition"
            >
              <IconPlus class="w-5 h-5" /> Agregar ejercicio
            </button>
          </div>
        </form>
      </div>
    </div>

    <ExercisePickerSheet
      :show="showExercisePicker"
      @close="showExercisePicker = false"
      @select="addExercises"
    />
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  background-color: white;
  color: #374151;
  transition: all 0.2s ease;
}

.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
  outline: none;
}

.drop-fade-enter-active {
  transition: all 0.3s ease;
}
.drop-fade-leave-active {
  transition: all 0.2s ease;
  position: absolute;
}
.drop-fade-enter-from {
  opacity: 0;
  transform: translateY(-15px);
}
.drop-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
