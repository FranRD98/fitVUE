<script setup>
import { ref, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia

import { getExercises } from '@/api/services/exercises'
import { getRoutineCategories, createRoutine, updateRoutine, createRoutineCategory } from '@/api/services/routines'

// Props y emits
const props = defineProps({
  show: Boolean,
  initialData: Object
})

const emit = defineEmits(['close', 'saved'])

// Estado
const userStore = useUserStore()  // Usamos el store de usuario
const exercises = ref([])
const categories = ref([])
const defaultDays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
const selectedDays = ref([])
const newCategoryName = ref('')
const showNewCategoryInput = ref(false)
const newCategoryTitle = ref('')

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
  days: [],
  user_id: '',
  published: false
})

// Cargar datos al montar
onMounted(async () => {
  exercises.value = await getExercises(userStore.userData.uid)
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
      days: [],
      published: newVal.published ?? false,
    }

    selectedDays.value = defaultDays.map(dayName => {
      const existing = newVal.days?.find(d => d.day === dayName || d.name === dayName)
      return {
        day: dayName,
        enabled: !!existing,
        exercises: existing?.exercises || [],
        selectedExercise: ''
      }
    })
  } else {
    resetForm()
  }
}, { immediate: true })

// Función para agregar ejercicio a un día
function addExerciseToDay(day) {
  const selectedId = day.selectedExercise
  const found = exercises.value.find(e => e.id === selectedId)
  if (!found) return

  day.exercises.push({
    id: found.id,
    name: found.name,
    sets: null,
    reps: null
  })
  day.selectedExercise = ''
}

// Quitar ejercicio de un día
function removeExercise(day, index) {
  day.exercises.splice(index, 1)
}

// Enviar el formulario
async function submitForm() {
  if (!routine.value.title || !routine.value.id_category) {
    alert('El título y tipo de rutina son obligatorios.')
    return
  }

  routine.value.days = selectedDays.value
    .filter(d => d.enabled && d.exercises.length)
    .map(d => ({
      day: d.day,
      exercises: d.exercises.map(ex => ({
        id: ex.id,
        name: ex.name,
        sets: ex.sets,
        reps: ex.reps
      }))
    }))

  try {
    if (routine.value.id) {
      await updateRoutine(routine.value.id, routine.value)
    } else {
      routine.value.user_id = userStore.userData?.uid // Nos aseguramos de que el uid ya esté disponible y lo asignamos antes de crearla
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
    days: []
  }

  selectedDays.value = defaultDays.map(day => ({
    day: day,
    enabled: false,
    exercises: [],
    selectedExercise: ''
  }))
}
</script>


<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex justify-center items-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-3xl relative overflow-y-auto max-h-[90vh]">
      
      <!-- Botón de cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <!-- Título -->
      <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-4">
        {{ routine.id ? 'Editar rutina' : 'Crear nueva rutina' }}
      </h2>

      <!-- Formulario -->
      <form @submit.prevent="submitForm" class="space-y-6">

        <!-- Datos básicos -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm text-gray-700 font-medium mb-1 block">Título</label>
            <input v-model="routine.title" placeholder="Ej: Rutina fuerza Lunes-Miércoles" class="input" required />
          </div>
          <div>
            <label class="text-sm text-gray-700 font-medium mb-1 block">Descripción</label>
            <input v-model="routine.description" placeholder="Opcional..." class="input" />
          </div>
        </div>

        <!-- Selección de categoría -->
        <div class="space-y-2">
          <label class="text-sm text-gray-700 font-medium mb-1 block">Tipo</label>

          <select v-model="routine.id_category" class="input" required>
            <option disabled value="">Selecciona un tipo</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
          </select>

          <!-- Botón para mostrar input de nueva categoría -->
          <button
            type="button"
            @click="showNewCategoryInput = true"
            v-if="!showNewCategoryInput"
            class="text-sm text-blue-600 hover:underline mt-1"
          >
            + Crear nueva categoría
          </button>

          <!-- Input de nueva categoría -->
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

        <!-- Solo visible si el usuario es admin -->
        <div v-if="userStore.userData?.role === 'admin'" class="flex items-center gap-3 mt-4">
          <label class="flex items-center gap-2 cursor-pointer select-none">
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
            <span class="text-sm text-gray-700">Publicar rutina</span>
          </label>
        </div>

        <!-- Días de rutina -->
        <div class="space-y-6">
          <div
            v-for="day in selectedDays"
            :key="day.day"
            class="border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white hover:border-gray-400 transition-all duration-300"
          >
            <label class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <input type="checkbox" v-model="day.enabled" />
                <span class="font-semibold text-[var(--color-primary)] text-base">{{ day.day }}</span>
              </div>
            </label>

            <Transition name="slide-fade">
              <div v-if="day.enabled" class="space-y-2">
                <div class="flex gap-2">
                  <select v-model="day.selectedExercise" class="input">
                    <option disabled selected value="">Selecciona un ejercicio</option>
                    <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }} ({{ ex.exercises_categories.category_name }})</option>
                  </select>
                  <button
                    type="button"
                    @click="addExerciseToDay(day)"
                    class="bg-[var(--color-primary)] text-white px-4 rounded"
                  >
                    Agregar
                  </button>
                </div>

                <TransitionGroup name="drop-fade" tag="div" class="space-y-2">
                  <div
                    v-for="(exercise, index) in day.exercises"
                    :key="index"
                    class="bg-white border border-gray-200 rounded-xl shadow-sm px-4 py-3 mb-3"
                  >
                    <!-- Cabecera -->
                    <div class="flex justify-between items-center mb-2">
                      <h3 class="text-[var(--color-primary)] font-semibold text-base">
                        {{ exercise.name }}
                      </h3>
                      <button
                        @click="removeExercise(day, index)"
                        class="text-red-500 text-sm hover:underline"
                      >
                        ✕ Quitar
                      </button>
                    </div>

                    <!-- Datos -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                      <div>
                        <label class="block text-xs text-gray-500 mb-1">Series</label>
                        <input
                          v-model.number="exercise.sets"
                          type="number"
                          min="1"
                          class="input text-sm"
                          placeholder="0"
                        />
                      </div>
                      <div>
                        <label class="block text-xs text-gray-500 mb-1">Repeticiones</label>
                        <input
                          v-model.number="exercise.reps"
                          type="number"
                          min="1"
                          class="input text-sm"
                          placeholder="0"
                        />
                      </div>
                    </div>
                  </div>

                </TransitionGroup>
              </div>
            </Transition>
          </div>
        </div>
      
        <!-- Guardar -->
        <div class="flex justify-end pt-4 mt-6">
          <button
            type="submit"
            class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white px-6 py-2 rounded-lg font-semibold"
          >
            Guardar rutina
          </button>
        </div>
      </form>

      
    </div>
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

.slide-fade-enter-active {
  transition: all 0.3s ease;
}
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
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
