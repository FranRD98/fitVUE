<script setup>
import { ref, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import { getRoutineCategories, createRoutine, updateRoutine, createRoutineCategory } from '@/api/services/routines'
import ExercisePickerSheet from '@/components/dashboard/pickers/ExercisePickerSheet.vue'
import { IconX, IconPlus } from '@tabler/icons-vue'

// Props y emits
const props = defineProps({
  show: Boolean,
  initialData: Object
})

const emit = defineEmits(['close', 'saved'])

// Estado
const userStore = useUserStore()
const categories = ref([])
const newCategoryTitle = ref('')
const showNewCategoryInput = ref(false)
const showExercisePicker = ref(false)

const handleCreateCategory = async () => {
  if (!newCategoryTitle.value.trim()) return

  try {
    const newCategory = await createRoutineCategory(newCategoryTitle.value.trim())
    categories.value.push(newCategory)
    routine.value.id_category = newCategory.id
    newCategoryTitle.value = ''
    showNewCategoryInput.value = false
  } catch (error) {
    console.error('Error al crear la categoría:', error)
    alert('No se pudo crear la categoría.')
  }
}

const routine = ref({
  title: '',
  description: '',
  id_category: '',
  exercises: [],
  user_id: '',
  published: false
})

onMounted(async () => {
  categories.value = await getRoutineCategories()
})

// Rellenar el formulario si se edita una rutina
watch(() => props.initialData, (newVal) => {
  if (newVal) {
    routine.value = {
      id: newVal.id,
      title: newVal.title || '',
      description: newVal.description || '',
      id_category: newVal.id_category || '',
      exercises: (newVal.exercises || []).map(ex => ({ ...ex })),
      published: newVal.published ?? false,
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function addExercise(exercise) {
  routine.value.exercises.push({
    id: exercise.id,
    name: exercise.name,
    sets: null,
    reps: null
  })
  showExercisePicker.value = false
}

function removeExercise(index) {
  routine.value.exercises.splice(index, 1)
}

// Enviar el formulario
async function submitForm() {
  if (!routine.value.title || !routine.value.id_category) {
    alert('El título y tipo de rutina son obligatorios.')
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
    id_category: '',
    exercises: []
  }
}
</script>


<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/50 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-3xl md:max-h-[90vh] bg-white md:rounded-2xl shadow-2xl flex flex-col overflow-hidden">

      <!-- Header -->
      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="emit('close')" class="text-blue-500 font-medium">Cancelar</button>
        <h2 class="font-semibold text-[var(--color-primary)]">
          {{ routine.id ? 'Editar Rutina' : 'Crear Rutina' }}
        </h2>
        <button type="button" @click="submitForm" class="text-blue-500 font-semibold">Guardar</button>
      </header>

      <!-- Formulario -->
      <div class="flex-1 overflow-y-auto px-4 py-4">
        <form @submit.prevent="submitForm" class="space-y-5">

          <input v-model="routine.title" placeholder="Título de la Rutina" class="input text-lg font-semibold" required />
          <input v-model="routine.description" placeholder="Descripción (opcional)" class="input" />

          <!-- Selección de categoría -->
          <div class="space-y-2">
            <label class="text-sm text-gray-700 font-medium mb-1 block">Tipo</label>

            <select v-model="routine.id_category" class="input" required>
              <option disabled value="">Selecciona un tipo</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
            </select>

            <button
              type="button"
              @click="showNewCategoryInput = true"
              v-if="!showNewCategoryInput"
              class="text-sm text-blue-600 hover:underline mt-1"
            >
              + Crear nueva categoría
            </button>

            <div v-if="showNewCategoryInput" class="flex gap-2 mt-2">
              <input
                v-model="newCategoryTitle"
                type="text"
                placeholder="Nombre de la nueva categoría"
                class="input flex-1"
              />
              <button
                type="button"
                @click="handleCreateCategory"
                class="bg-[var(--color-primary)] text-white px-4 rounded hover:bg-[var(--color-secondary)]"
              >
                Crear
              </button>
            </div>
          </div>

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
                class="bg-white border border-gray-200 rounded-xl shadow-sm px-4 py-3"
              >
                <div class="flex justify-between items-center mb-2">
                  <h3 class="text-[var(--color-primary)] font-semibold text-base">
                    {{ exercise.name }}
                  </h3>
                  <button type="button" @click="removeExercise(index)" class="text-red-500 text-sm hover:underline flex items-center gap-1">
                    <IconX class="w-4 h-4" /> Quitar
                  </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs text-gray-500 mb-1">Series</label>
                    <input v-model.number="exercise.sets" type="number" min="1" class="input text-sm" placeholder="0" />
                  </div>
                  <div>
                    <label class="block text-xs text-gray-500 mb-1">Repeticiones</label>
                    <input v-model.number="exercise.reps" type="number" min="1" class="input text-sm" placeholder="0" />
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
      @select="addExercise"
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
