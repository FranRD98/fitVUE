<script setup>
  import { ref, computed, watch } from 'vue'
  import { getExercises, deleteExercise, getExerciseCategories } from '@/api/services/exercises'
  import ExerciseFormModal from '@/components/dashboard/modals/ExerciseFormModal.vue'

  import { IconPlus, IconLayoutGrid, IconLayoutList, IconTrash } from '@tabler/icons-vue'
  
  import { useUserStore } from '@/stores/user'
  import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'

  const props = defineProps({
    userData: Object
  })

  const userStore = useUserStore()
  const exercises = ref([])
  const exerciseCategories = ref([])
  const showModal = ref(false)
  const selectedExercise = ref(null)
  const viewMode = ref('grid')
  const searchQuery = ref('')
  const selectedCategory = ref('')


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

watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1
})

  const { loading, showSkeleton, start, finish } = useDelayedSkeleton(300) // <-- 300ms

  watch(
    () => userStore.userData?.uid,
    async (uid) => {
      if (!uid) return

      start()

      try {
        exercises.value = await getExercises(userStore.userData?.uid)        
        exerciseCategories.value = await getExerciseCategories()
      } catch (err) {
        console.error('Error al cargar ejercicios:', error)
      } finally {
        finish()
      }
    },
    { immediate: true }
  )

  const openEditModal = (exercise) => {
    selectedExercise.value = exercise
    showModal.value = true
  }

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

  const handleDelete = async (exercise) => {
    if (confirm(`¿Seguro que quieres eliminar el ejercicio "${exercise.name}"?`)) {
      await deleteExercise(exercise.id)
      await loadExercises()
    }
  }

  const filteredExercises = computed(() => {
    return exercises.value.filter(ex => {
      const matchesSearch = ex.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      const matchesCategory = !selectedCategory.value || ex.exercises_categories?.category_name === selectedCategory.value
      return matchesSearch && matchesCategory
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
    <div v-else class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
      
      <!-- Buscador -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar ejercicio</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Nombre del ejercicio..."
          class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
        />
      </div>

      <!-- Filtro grupo muscular -->
      <div>
        <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Grupo muscular</label>
        <select v-model="selectedCategory" class="w-full border border-gray-300 p-2 rounded text-sm text-gray-700">
          <option value="">Todos</option>
          <option v-for="category in exerciseCategories" :key="category.id" :value="category.category_name">
            {{ category.category_name }}
          </option>
        </select>
      </div>

      <!-- View Mode -->
      <div class="flex items-center gap-1">
        <button
          @click="viewMode = 'grid'"
          :class="['p-2 rounded', viewMode === 'grid' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
          title="Vista de tarjetas"
          >
          <IconLayoutGrid/>
        </button>
        <button
          @click="viewMode = 'table'"
          :class="['p-2 rounded', viewMode === 'table' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
          title="Vista de tabla"
          >
          <IconLayoutList/>
        </button>
      </div>
    </div>

    <!-- Grid -->
    <div v-if="viewMode === 'grid' && filteredExercises.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
        v-for="exercise in paginatedExercises"
          :key="exercise.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col transition cursor-pointer w-full"
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
          <div class="mt-2 flex justify-between items-center">
            <p
              class="inline-block px-2 py-1 rounded text-xs font-bold w-fit"
              style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);"
            >
              {{ exercise?.exercises_categories.category_name || '—' }}
            </p>

              <button
                @click.prevent.stop="handleDelete(exercise)"
                class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200"
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
          <th class="px-2">Descripción</th>
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
            {{ exercise?.exercises_categories.category_name || '—' }}

            </p>
          </td>
          <td class="py-3 px-2 text-gray-600 line-clamp-2">{{ exercise.description }}</td>
          <td class="py-3 px-2 text-right relative">
            <button
                @click.prevent.stop="handleDelete(exercise)"
                class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200"
                title="Eliminar"
              >
                <IconTrash class="w-5 h-5" />
              </button>
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