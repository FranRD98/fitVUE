<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useUserStore } from '@/stores/user'
import api from '@/api/client'
import { createExercise, updateExercise, getExerciseCategories, getExerciseHistory } from '@/api/services/exercises'
import ExerciseProgressChart from '@/components/dashboard/charts/ExerciseProgressChart.vue'
import MuscleEquipmentPicker from '@/components/dashboard/pickers/MuscleEquipmentPicker.vue'
import { EQUIPMENT_OPTIONS, equipmentLabel, groupMusclesByRegion } from '@/constants/exerciseOptions'
import { IconCamera, IconChevronRight, IconX } from '@tabler/icons-vue'

const props = defineProps({ show: Boolean, initialData: Object })
const emit = defineEmits(['close', 'saved'])

const userStore = useUserStore()
const selectedTab = ref('info')
const exerciseCategories = ref([])
const exerciseHistory = ref([])
const loading = ref(true)

const exercise = ref({
  name: '',
  description: '',
  id_category: '',
  equipment: '',
  image: '',
  created_by: '',
  secondary_muscle_ids: []
})
const imageFile = ref(null)
const activePicker = ref(null) // 'equipment' | 'primary' | 'secondary' | null

const isEditable = computed(() => {
  return !exercise.value.id || exercise.value.created_by === userStore.userData?.uid
})

const muscleGroups = computed(() => groupMusclesByRegion(exerciseCategories.value))
const equipmentGroups = computed(() => [{ region: null, label: null, items: EQUIPMENT_OPTIONS }])

const primaryMuscleLabel = computed(() =>
  exerciseCategories.value.find(c => c.id === exercise.value.id_category)?.category_name
)
const secondaryMuscleLabels = computed(() =>
  exerciseCategories.value
    .filter(c => exercise.value.secondary_muscle_ids.includes(c.id))
    .map(c => c.category_name)
)

onMounted(async () => {
  try {
    exerciseCategories.value = await getExerciseCategories()
  } catch (error) {
    console.error('Error al obtener categorías:', error)
  } finally {
    loading.value = false
  }
})

watch(
  () => props.initialData,
  async (newVal) => {
    if (newVal) {
      exercise.value = {
        ...newVal,
        secondary_muscle_ids: (newVal.secondary_muscles || []).map(m => m.id)
      }
      if (selectedTab.value === 'info') {
        await loadExerciseHistory()
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

watch(
  () => selectedTab.value,
  async (newTab) => {
    if (newTab === 'history' && exercise.value.id) {
      await loadExerciseHistory()
    }
  }
)

async function loadExerciseHistory() {
  try {
    exerciseHistory.value = await getExerciseHistory(exercise.value.id, userStore.userData?.uid)
  } catch (e) {
    console.error('Error cargando historial:', e)
  }
}

const uploadImage = async () => {
  if (!imageFile.value) return

  const formData = new FormData()
  formData.append('image', imageFile.value)

  try {
    const { data } = await api.post('/uploads/exercises', formData)
    exercise.value.image = data.url

    if (exercise.value.id) {
      await updateExercise(exercise.value.id, { image: data.url })
    }
  } catch (error) {
    console.error('Error al subir imagen:', error)
  }
}

const deleteImage = async () => {
  const imageUrl = exercise.value.image
  if (!imageUrl || imageUrl.startsWith('blob:')) return

  const path = imageUrl.split('/storage/')[1]
  if (path) await api.delete('/uploads', { data: { path } })
  if (exercise.value.id) await updateExercise(exercise.value.id, { image: '' })

  exercise.value.image = ''
  imageFile.value = null
}

const submitForm = async () => {
  if (!exercise.value.equipment || !exercise.value.id_category) {
    alert('El equipamiento y el grupo muscular primario son obligatorios.')
    return
  }

  if (imageFile.value) await uploadImage()
  if (exercise.value.id) {
    await updateExercise(exercise.value.id, exercise.value)
  } else {
    exercise.value.created_by = userStore.userData?.uid
    await createExercise(exercise.value)
  }
  emit('saved')
  close()
}

function close() {
  resetForm()
  emit('close')
}

function resetForm() {
  exercise.value = {
    name: '',
    description: '',
    id_category: '',
    equipment: '',
    image: '',
    created_by: '',
    secondary_muscle_ids: []
  }
  imageFile.value = null
  exerciseHistory.value = []
}

const handleImageChange = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  const resizedFile = await resizeImage(file, 800)
  exercise.value.image = URL.createObjectURL(resizedFile)
  imageFile.value = resizedFile
}

const resizeImage = (file, maxWidth = 800) => {
  return new Promise((resolve) => {
    const img = new Image()
    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const scaleFactor = maxWidth / img.width
      canvas.width = maxWidth
      canvas.height = img.height * scaleFactor

      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

      canvas.toBlob((blob) => {
        const resizedFile = new File([blob], file.name, { type: file.type })
        resolve(resizedFile)
      }, file.type, 0.8)
    }
    reader.readAsDataURL(file)
  })
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-3xl md:max-h-[90vh] bg-white md:rounded-xl shadow-xl flex flex-col relative overflow-hidden">

      <!-- Botón cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 z-10 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <IconX class="h-6 w-6" />
      </button>

      <!-- Tabs: "Historial" no tiene sentido si el ejercicio aún no existe -->
      <div class="flex border-b divide-x pt-[calc(env(safe-area-inset-top)+0.5rem)] md:pt-0 shrink-0">
        <div class="flex-1 text-center py-4 cursor-pointer hover:bg-gray-100"
             :class="{ 'bg-gray-100 font-semibold text-[var(--color-primary)]': selectedTab === 'info' }"
             @click="selectedTab = 'info'">Información</div>

        <div v-if="exercise.id" class="flex-1 text-center py-4 cursor-pointer hover:bg-gray-100"
             :class="{ 'bg-gray-100 font-semibold text-[var(--color-primary)]': selectedTab === 'history' }"
             @click="selectedTab = 'history'">Historial</div>
      </div>

      <!-- Contenido dinámico -->
      <transition name="fade" mode="out-in">
        <div :key="selectedTab" class="p-6 overflow-y-auto flex-1">

          <!-- Info -->
          <div v-if="selectedTab === 'info'" class="space-y-4">
            <p v-if="!isEditable" class="text-sm text-red-500 mt-2">⚠️ No puedes modificar un ejercicio de la plataforma.</p>
            <h2 class="text-xl font-semibold text-[var(--color-primary)]">
              {{ exercise.id ? 'Editar ejercicio' : 'Crear ejercicio' }}
            </h2>

            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Imagen -->
              <div v-if="isEditable" class="flex flex-col items-center gap-2 py-2">
                <label for="image-upload-input" class="relative cursor-pointer w-24 h-24 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50 hover:border-gray-400">
                  <img v-if="exercise.image" :src="exercise.image" alt="Imagen del ejercicio" class="w-full h-full object-cover" />
                  <IconCamera v-else class="w-7 h-7 text-gray-400" />
                </label>
                <input type="file" accept="image/*" @change="handleImageChange" class="hidden" id="image-upload-input" />
                <label v-if="!exercise.image" for="image-upload-input" class="text-sm text-[var(--color-primary)] font-medium cursor-pointer">
                  Añadir multimedia
                </label>
                <button v-else type="button" @click="deleteImage" class="text-sm text-red-500 hover:underline">Quitar imagen</button>
              </div>

              <input v-model="exercise.name" :disabled="!isEditable" placeholder="Nombre de Ejercicio" class="input" required />
              <input v-model="exercise.description" :disabled="!isEditable" placeholder="Descripción (opcional)" class="input" />

              <!-- Equipamiento -->
              <button type="button" :disabled="!isEditable" @click="activePicker = 'equipment'"
                class="w-full flex items-center justify-between py-3 border-b text-left disabled:cursor-default">
                <span class="text-sm font-medium text-gray-700">Equipamiento</span>
                <span class="flex items-center gap-1 text-sm" :class="exercise.equipment ? 'text-[var(--color-primary)]' : 'text-[var(--color-primary)]/70'">
                  {{ exercise.equipment ? equipmentLabel(exercise.equipment) : 'Seleccionar' }}
                  <IconChevronRight class="w-4 h-4 text-gray-400" />
                </span>
              </button>

              <!-- Grupo Muscular Primario -->
              <button type="button" :disabled="!isEditable" @click="activePicker = 'primary'"
                class="w-full flex items-center justify-between py-3 border-b text-left disabled:cursor-default">
                <span class="text-sm font-medium text-gray-700">Grupo Muscular Primario</span>
                <span class="flex items-center gap-1 text-sm" :class="primaryMuscleLabel ? 'text-[var(--color-primary)]' : 'text-[var(--color-primary)]/70'">
                  {{ primaryMuscleLabel || 'Seleccionar' }}
                  <IconChevronRight class="w-4 h-4 text-gray-400" />
                </span>
              </button>

              <!-- Otros músculos -->
              <button type="button" :disabled="!isEditable" @click="activePicker = 'secondary'"
                class="w-full flex items-center justify-between py-3 border-b text-left disabled:cursor-default">
                <span class="text-sm font-medium text-gray-700">Otros músculos</span>
                <span class="flex items-center gap-1 text-sm text-[var(--color-primary)]/70 max-w-[60%] justify-end text-right">
                  <span class="truncate">{{ secondaryMuscleLabels.length ? secondaryMuscleLabels.join(', ') : 'Seleccionar (opcional)' }}</span>
                  <IconChevronRight class="w-4 h-4 text-gray-400 shrink-0" />
                </span>
              </button>

              <div class="text-right pt-2">
                <button v-if="isEditable" type="submit" class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg hover:bg-[var(--color-secondary)]">
                  {{ exercise.id ? 'Guardar cambios' : 'Crear ejercicio' }}
                </button>
              </div>
            </form>

            <MuscleEquipmentPicker
              :show="activePicker === 'equipment'"
              title="Equipamiento"
              :groups="equipmentGroups"
              v-model="exercise.equipment"
              @close="activePicker = null"
            />
            <MuscleEquipmentPicker
              :show="activePicker === 'primary'"
              title="Grupo Muscular Primario"
              :groups="muscleGroups"
              v-model="exercise.id_category"
              @close="activePicker = null"
            />
            <MuscleEquipmentPicker
              :show="activePicker === 'secondary'"
              title="Otros músculos"
              multiple
              :groups="muscleGroups"
              v-model="exercise.secondary_muscle_ids"
              @close="activePicker = null"
            />
          </div>

          <!-- Historial -->
          <div v-else-if="selectedTab === 'history'">
            <h2 class="text-xl font-semibold text-[var(--color-primary)] mb-4">Historial de levantamientos</h2>

            <ExerciseProgressChart :history="exerciseHistory" class="mb-8" />

            <table v-if="exerciseHistory.length" class="min-w-full text-left text-sm border">
              <thead class="bg-gray-200 text-gray-600 font-medium">
                <tr>
                  <th class="py-3 px-2">Fecha</th>
                  <th class="px-2">Series registradas</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-for="(entry, index) in exerciseHistory" :key="index" class="border-t border-gray-200">
                  <td class="py-3 px-2 font-medium">{{ formatDate(entry.created_at) }}</td>
                  <td class="py-3 px-2 text-sm">
                    <ul class="space-y-1">
                      <li v-for="(set, i) in entry.sets" :key="i">
                        Serie {{ i + 1 }}: <strong>{{ set.reps }}</strong> reps x <strong>{{ set.weight }}</strong> kg
                      </li>
                    </ul>
                  </td>
                </tr>
              </tbody>
            </table>

            <p v-else class="text-gray-500">No hay datos registrados para este ejercicio.</p>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  line-height: 1.5rem;
  outline: none;
  transition: border-color 0.2s ease;
}

.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  padding: 0.75rem 0.5rem;
  text-align: left;
  vertical-align: top;
}

th {
  background-color: #f3f4f6;
}

</style>
