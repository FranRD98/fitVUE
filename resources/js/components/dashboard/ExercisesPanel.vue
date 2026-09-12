<script setup>
  import { ref, computed, watch } from 'vue'
  import { getExercises, deleteExercise, getExerciseCategories } from '@/api/services/exercises'
  import ExerciseFormModal from '@/components/dashboard/modals/ExerciseFormModal.vue'
  import ExerciseFilterSheet from '@/components/dashboard/pickers/ExerciseFilterSheet.vue'
  import { EQUIPMENT_OPTIONS, equipmentLabel, groupMusclesByRegion } from '@/constants/exerciseOptions'

  import { IconPlus, IconLayoutGrid, IconLayoutList, IconTrash, IconChevronDown } from '@tabler/icons-vue'

  import { useUserStore } from '@/stores/user'
  import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'

  const userStore = useUserStore()
  const exercises = ref([])
  const exerciseCategories = ref([])
  const showModal = ref(false)
  const selectedExercise = ref(null)
  // Lista compacta por defecto; la vista de tarjetas con imagen queda como alternativa.
  const viewMode = ref('table')
  const searchQuery = ref('')
  const equipmentFilter = ref([])
  const muscleFilter = ref([])
  const activeFilterSheet = ref(null) // 'equipment' | 'muscle' | null

  /* Paginación */
  const currentPage = ref(1)
  const itemsPerPage = ref(16)

  const totalPages = computed(() => {
    return Math.ceil(filteredExercises.value.length / itemsPerPage.value)
  })

  const paginatedExercises = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    return filteredExercises.value.slice(start, start + itemsPerPage.value)
  })

  watch([searchQuery, equipmentFilter, muscleFilter], () => {
    currentPage.value = 1
  }, { deep: true })

  const { loading, showSkeleton, start, finish } = useDelayedSkeleton(300)

  const loadExercises = async () => {
    if (!userStore.userData?.uid) return

    start()
    try {
      exercises.value = await getExercises(userStore.userData?.uid)
      exerciseCategories.value = await getExerciseCategories()
    } catch (err) {
      console.error('Error al cargar ejercicios:', err)
    } finally {
      finish()
    }
  }

  watch(
    () => userStore.userData?.uid,
    async (uid) => {
      if (!uid) return
      await loadExercises()
    },
    { immediate: true }
  )

  const openEditModal = (exercise) => {
    selectedExercise.value = exercise
    showModal.value = true
  }

  const handleDelete = async (exercise) => {
    if (confirm(`¿Seguro que quieres eliminar el ejercicio "${exercise.name}"?`)) {
      await deleteExercise(exercise.id)
      await loadExercises()
    }
  }

  // Un ejercicio ajeno (p. ej. de la biblioteca del admin) no se puede borrar;
  // solo quien lo creó (o un admin) puede eliminarlo.
  function canDelete(exercise) {
    return userStore.userData?.role === 'admin' || exercise.created_by === userStore.userData?.uid
  }

  const equipmentGroups = computed(() => [{ region: null, label: null, items: EQUIPMENT_OPTIONS }])
  const muscleGroups = computed(() => groupMusclesByRegion(exerciseCategories.value))

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
</script>


<template>
  <section>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Ejercicios</h1>
      <button
        @click="showModal = true"
        class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition"
      >
        <IconPlus class="w-5 h-5"/>
        Nuevo ejercicio
      </button>

    </div>

    <!-- Modal -->
    <ExerciseFormModal
      :show="showModal"
      :initialData="selectedExercise"
      @close="showModal = false; selectedExercise = null"
      @saved="loadExercises()"
    />

    <!-- Delay antes del skeleton -->
    <div v-if="loading && !showSkeleton" />

    <!-- Skeleton visible si tarda en cargar -->
    <div v-else-if="loading && showSkeleton" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-pulse">
      <div v-for="n in 4" :key="n" class="bg-gray-100 h-64 rounded-xl shadow" />
    </div>

    <!-- Panel -->
    <div v-else class="mb-6 space-y-3">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
        <div class="flex-1">
          <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar ejercicio</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nombre del ejercicio..."
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
          />
        </div>

        <div class="flex items-center gap-1 shrink-0">
          <button
            @click="viewMode = 'table'"
            :class="['p-2 rounded', viewMode === 'table' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
            title="Vista de lista"
          >
            <IconLayoutList/>
          </button>
          <button
            @click="viewMode = 'grid'"
            :class="['p-2 rounded', viewMode === 'grid' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
            title="Vista de tarjetas"
          >
            <IconLayoutGrid/>
          </button>
        </div>
      </div>

      <!-- Filtros de equipamiento / grupo muscular: mismo patrón que al agregar un ejercicio a una rutina -->
      <div class="flex gap-2">
        <button
          type="button"
          @click="activeFilterSheet = 'equipment'"
          class="flex-1 md:flex-none flex items-center justify-center gap-1 border-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
          :class="equipmentFilter.length ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-300 text-gray-600'"
        >
          {{ equipmentFilter.length ? `Equipamiento (${equipmentFilter.length})` : 'Equipamiento' }}
          <IconChevronDown class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="activeFilterSheet = 'muscle'"
          class="flex-1 md:flex-none flex items-center justify-center gap-1 border-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
          :class="muscleFilter.length ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-300 text-gray-600'"
        >
          {{ muscleFilter.length ? `Músculos (${muscleFilter.length})` : 'Grupo muscular' }}
          <IconChevronDown class="w-4 h-4" />
        </button>
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

    <!-- Grid -->
    <div v-if="viewMode === 'grid' && filteredExercises.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
        v-for="exercise in paginatedExercises"
          :key="exercise.id"
          class="bg-gray-100 md:bg-white rounded-xl shadow-lg overflow-hidden flex flex-col transition cursor-pointer w-full"
          @click="openEditModal(exercise)"
        >
        <img
          :src="(!exercise.image || exercise.image === '') ? `https://placehold.co/600x400?text=${encodeURIComponent(exercise.name)}` : exercise.image"
          alt="Imagen del ejercicio"
          class="w-full aspect-video object-cover"
        />

        <div class="p-5 flex flex-col flex-grow justify-between">

          <!-- Nombre -->
          <h3 class="text-xl font-bold text-gray-800 mb-1 truncate">{{ exercise.name }}</h3>

          <!-- Descripción con espacio reservado aunque esté vacía -->
          <p v-if="exercise.description" class="text-sm text-gray-600 mb-2 line-clamp-2">
            {{ exercise.description }}
          </p>

          <!-- Badge + acciones -->
          <div class="mt-2 flex justify-between items-center gap-2">
            <div class="flex flex-wrap gap-1">
              <p
                class="inline-block px-2 py-1 rounded text-xs font-bold w-fit"
                style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);"
              >
                {{ exercise?.exercises_categories?.category_name || '—' }}
              </p>
              <p v-if="exercise.equipment" class="inline-block px-2 py-1 rounded text-xs font-bold w-fit bg-gray-100 text-gray-600">
                {{ equipmentLabel(exercise.equipment) }}
              </p>
            </div>

              <button
                v-if="canDelete(exercise)"
                @click.prevent.stop="handleDelete(exercise)"
                class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200 shrink-0"
                title="Eliminar"
              >
                <IconTrash class="w-5 h-5" />
              </button>

            </div>
          </div>
        </div>
      </div>

    <!-- Table view mode -->
     <div v-else-if="viewMode === 'table' && filteredExercises.length"class="overflow-x-auto">

    <table class="min-w-[600px] w-full text-left text-sm">
      <thead class="bg-gray-200 text-gray-600 font-medium">
        <tr>
          <th class="py-3 px-2">Nombre</th>
          <th class="px-2">Grupo muscular</th>
          <th class="px-2">Equipamiento</th>
          <th class="px-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <tr
        v-for="exercise in paginatedExercises"
          :key="exercise.id"
          @click="openEditModal(exercise)"
          class="border-t border-gray-200 hover:bg-gray-100 transition cursor-pointer"
        >
          <td class="py-3 px-2 font-semibold  text-[var(--color-primary)]">{{ exercise.name }}</td>
          <td class="py-3 px-2">
            <p
              class="inline-block px-2 py-1 rounded-full text-xs font-bold w-fit"
              style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);"
            >
            {{ exercise?.exercises_categories?.category_name || '—' }}

            </p>
          </td>
          <td class="py-3 px-2 text-gray-600">{{ exercise.equipment ? equipmentLabel(exercise.equipment) : '—' }}</td>
          <td class="py-3 px-2 text-right relative">
            <button
                v-if="canDelete(exercise)"
                @click.prevent.stop="handleDelete(exercise)"
                class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200"
                title="Eliminar"
              >
                <IconTrash class="w-5 h-5" />
              </button>
              <span v-else class="text-gray-300 text-xs">—</span>
          </td>
        </tr>
      </tbody>
    </table>
</div>

    <!-- Si no hay coincidencias -->
    <div v-if="!loading && filteredExercises.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
      </svg>
      <p class="text-lg font-semibold">Sin resultados</p>
      <p class="text-sm">No se encontraron ejercicios que coincidan con los filtros aplicados.</p>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-6">
      <button
        @click="currentPage--"
        :disabled="currentPage === 1"
        class="px-3 py-1 rounded border disabled:opacity-50"
      >
        Anterior
      </button>

      <span class="text-sm font-medium">
        Página {{ currentPage }} de {{ totalPages }}
      </span>

      <button
        @click="currentPage++"
        :disabled="currentPage === totalPages"
        class="px-3 py-1 rounded border disabled:opacity-50"
      >
        Siguiente
      </button>
    </div>


  </section>
</template>
