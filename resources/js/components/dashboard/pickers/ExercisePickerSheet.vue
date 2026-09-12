<script setup>
import { ref, computed, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import { getExercises, getExerciseCategories } from '@/api/services/exercises'
import ExerciseFilterSheet from '@/components/dashboard/pickers/ExerciseFilterSheet.vue'
import ExerciseFormModal from '@/components/dashboard/modals/ExerciseFormModal.vue'
import { EQUIPMENT_OPTIONS, groupMusclesByRegion } from '@/constants/exerciseOptions'
import { IconSearch, IconInfoCircle, IconChevronDown, IconX } from '@tabler/icons-vue'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close', 'select'])

const userStore = useUserStore()
const exercises = ref([])
const categories = ref([])
const searchQuery = ref('')
const equipmentFilter = ref([])
const muscleFilter = ref([])
const activeFilterSheet = ref(null) // 'equipment' | 'muscle' | null
const showCreateModal = ref(false)
const infoExercise = ref(null)

const RECENT_KEY = 'fitvue_recent_exercise_ids'

async function loadExercises() {
  exercises.value = await getExercises(userStore.userData?.uid)
}

watch(() => props.show, async (open) => {
  if (open) {
    searchQuery.value = ''
    await loadExercises()
    if (!categories.value.length) {
      categories.value = await getExerciseCategories()
    }
  }
})

const equipmentGroups = computed(() => [{ region: null, label: null, items: EQUIPMENT_OPTIONS }])
const muscleGroups = computed(() => groupMusclesByRegion(categories.value))

const recentIds = ref(JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'))

function rememberRecent(id) {
  recentIds.value = [id, ...recentIds.value.filter(i => i !== id)].slice(0, 10)
  try { localStorage.setItem(RECENT_KEY, JSON.stringify(recentIds.value)) } catch { /* almacenamiento no disponible */ }
}

const hasActiveFilters = computed(() => equipmentFilter.value.length || muscleFilter.value.length)

const filteredExercises = computed(() => {
  return exercises.value.filter(ex => {
    const matchesSearch = ex.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesEquipment = !equipmentFilter.value.length || equipmentFilter.value.includes(ex.equipment)
    const matchesMuscle = !muscleFilter.value.length ||
      muscleFilter.value.includes(ex.id_category) ||
      (ex.secondary_muscles || []).some(m => muscleFilter.value.includes(m.id))
    return matchesSearch && matchesEquipment && matchesMuscle
  })
})

const recentExercises = computed(() => {
  if (searchQuery.value || hasActiveFilters.value) return []
  return recentIds.value
    .map(id => exercises.value.find(e => e.id === id))
    .filter(Boolean)
})

function chooseExercise(exercise) {
  rememberRecent(exercise.id)
  emit('select', exercise)
}

function openInfo(exercise) {
  infoExercise.value = exercise
}

async function handleExerciseCreated() {
  showCreateModal.value = false
  await loadExercises()
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-h-[85vh] md:max-w-2xl bg-white md:rounded-xl shadow-xl flex flex-col overflow-hidden">

      <!-- Header -->
      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="emit('close')" class="text-[var(--color-primary)] font-medium">Cancelar</button>
        <h2 class="font-semibold text-[var(--color-primary)]">Agregar Ejercicio</h2>
        <button type="button" @click="showCreateModal = true" class="text-[var(--color-primary)] font-medium">Crear</button>
      </header>

      <div class="px-4 pt-3 pb-2 space-y-3 shrink-0">
        <!-- Buscador -->
        <div class="relative">
          <IconSearch class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar ejercicio"
            class="w-full bg-gray-100 rounded-lg pl-9 pr-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--color-primary)]"
          />
        </div>

        <!-- Filtros -->
        <div class="flex gap-2">
          <button
            type="button"
            @click="activeFilterSheet = 'equipment'"
            class="flex-1 flex items-center justify-center gap-1 border-2 rounded-xl px-2 py-3 text-[13px] leading-tight font-semibold whitespace-nowrap overflow-hidden"
            :class="equipmentFilter.length ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-300 text-gray-600'"
          >
            <span class="truncate">{{ equipmentFilter.length ? `Equipamiento (${equipmentFilter.length})` : 'Equipamiento' }}</span>
            <IconChevronDown class="w-4 h-4 shrink-0" />
          </button>
          <button
            type="button"
            @click="activeFilterSheet = 'muscle'"
            class="flex-1 flex items-center justify-center gap-1 border-2 rounded-xl px-2 py-3 text-[13px] leading-tight font-semibold whitespace-nowrap overflow-hidden"
            :class="muscleFilter.length ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-300 text-gray-600'"
          >
            <span class="truncate">{{ muscleFilter.length ? `Músculos (${muscleFilter.length})` : 'Músculos' }}</span>
            <IconChevronDown class="w-4 h-4 shrink-0" />
          </button>
        </div>
      </div>

      <!-- Listado -->
      <div class="flex-1 overflow-y-auto px-4 pb-4">
        <template v-if="recentExercises.length">
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 mt-2 mb-1">Ejercicios Recientes</h3>
          <div class="divide-y">
            <div v-for="exercise in recentExercises" :key="'recent-'+exercise.id" class="flex items-center gap-3 py-2.5 cursor-pointer" @click="chooseExercise(exercise)">
              <img
                :src="exercise.image || `https://placehold.co/80x80?text=${encodeURIComponent(exercise.name[0])}`"
                class="w-10 h-10 rounded-full object-cover shrink-0"
                alt=""
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ exercise.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ exercise.exercises_categories?.category_name || '—' }}</p>
              </div>
              <button type="button" @click.stop="openInfo(exercise)" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Detalles">
                <IconInfoCircle class="w-5 h-5" />
              </button>
            </div>
          </div>
        </template>

        <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 mt-4 mb-1">
          {{ hasActiveFilters || searchQuery ? 'Resultados' : 'Todos los Ejercicios' }}
        </h3>
        <div class="divide-y">
          <div v-for="exercise in filteredExercises" :key="exercise.id" class="flex items-center gap-3 py-2.5 cursor-pointer" @click="chooseExercise(exercise)">
            <img
              :src="exercise.image || `https://placehold.co/80x80?text=${encodeURIComponent(exercise.name[0])}`"
              class="w-10 h-10 rounded-full object-cover shrink-0"
              alt=""
            />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ exercise.name }}</p>
              <p class="text-xs text-gray-500 truncate">{{ exercise.exercises_categories?.category_name || '—' }}</p>
            </div>
            <button type="button" @click.stop="openInfo(exercise)" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Detalles">
              <IconInfoCircle class="w-5 h-5" />
            </button>
          </div>
        </div>

        <p v-if="!filteredExercises.length" class="text-center text-gray-400 text-sm py-10">
          Sin resultados
        </p>
      </div>
    </div>

    <ExerciseFilterSheet
      :show="activeFilterSheet === 'equipment'"
      title="Equipamiento"
      :groups="equipmentGroups"
      v-model="equipmentFilter"
      :results-count="filteredExercises.length"
      @close="activeFilterSheet = null"
    />
    <ExerciseFilterSheet
      :show="activeFilterSheet === 'muscle'"
      title="Grupo Muscular"
      :groups="muscleGroups"
      v-model="muscleFilter"
      :results-count="filteredExercises.length"
      @close="activeFilterSheet = null"
    />

    <ExerciseFormModal
      :show="showCreateModal"
      :initial-data="null"
      @close="showCreateModal = false"
      @saved="handleExerciseCreated"
    />
    <ExerciseFormModal
      :show="!!infoExercise"
      :initial-data="infoExercise"
      @close="infoExercise = null"
      @saved="loadExercises(); infoExercise = null"
    />
  </div>
</template>
