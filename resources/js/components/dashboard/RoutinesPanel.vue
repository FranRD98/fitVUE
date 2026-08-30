<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia
import { getRoutinesByUser, assignRoutineToUser, getAssignedRoutine, unassignRoutineFromUser, updateRoutine, getCoachAssignedRoutine, deleteRoutine } from '@/api/services/routines.js'
import RoutineFormModal from '@/components/dashboard/modals/RoutineFormModal.vue'
import RoutineAssignedViewer from '@/components/dashboard/RoutineAssignedViewer.vue'

import { IconPlus, IconLayoutGrid, IconLayoutList, IconLockOff, IconRocket, IconLockOpen2, IconTrash } from '@tabler/icons-vue'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'

const routines = ref([])

const viewAssignedRoutine = ref(false)
const userStore = useUserStore()
const showModal = ref(false)
const selectedRoutine = ref(null)

const assignedRoutine = ref(null)
const assignedRoutineId = ref(null)
const assignedCoachRoutine = ref(null)
const assignedCoachRoutineId = ref(null)

const viewMode = ref('grid')
const searchQuery = ref('')
const hasSearch = computed(() => searchQuery.value.trim().length > 0)

/* Paginación */
const currentPage = ref(1)
const itemsPerPage = ref(16)

const totalPages = computed(() => {
  return Math.ceil(filteredRoutines.value.length / itemsPerPage.value)
})

const paginatedRoutines = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredRoutines.value.slice(start, start + itemsPerPage.value)
})

watch(searchQuery, () => {
  currentPage.value = 1
})




// Skeleton/loading state
const { loading, showSkeleton, start, finish } = useDelayedSkeleton(200)

const filteredRoutines = computed(() =>
  routines.value.filter(routine =>
    routine.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
)

const togglePublished = async (routine) => {
  try {
    const newValue = !routine.published
    await updateRoutine(routine.id, { published: newValue })
    routine.published = newValue
  } catch (error) {
    console.error('Error al publicar rutina:', error)
  }
}

async function removeRoutine(routine) {
  if (confirm(`¿Eliminar la dieta "${routine.title}"?`)) {
    await deleteRoutine(routine.id)
    await loadRoutines()
  }
}

const loadRoutines = async () => {
  start()
  
  try {
    routines.value = await getRoutinesByUser(userStore.userData?.uid)
    assignedRoutine.value = await getAssignedRoutine(userStore.userData?.uid)  
    assignedRoutineId.value = assignedRoutine.value?.id || null

    assignedCoachRoutine.value = await getCoachAssignedRoutine(userStore.userData?.uid)  
    assignedCoachRoutineId.value = assignedCoachRoutine.value?.id || null
  } catch (error) {
    console.error('Error al cargar rutinas:', error)

  } finally {
    finish()
  }
}

onMounted(loadRoutines)

const openEditModal = (routine) => {
  if (
    userStore.userData?.plan_id !== 1 &&
    assignedCoachRoutine.value &&
    routine.id === assignedCoachRoutine.value.id
  ) {
    viewAssignedRoutine.value = true // Mostrar la vista solo lectura
  } else {
    selectedRoutine.value = routine
    showModal.value = true
  }
}

const countExercises = (routine) => {
  return routine.days?.reduce((acc, day) => acc + (day.exercises?.length || 0), 0)
}

const handleAssign = async (routineId) => {
  if (assignedRoutineId.value && assignedRoutineId.value !== routineId) {
    const confirmChange = confirm('Este usuario ya tiene una rutina asignada. ¿Deseas reemplazarla?')
    if (!confirmChange) return
  }

  await assignRoutineToUser(userStore.userData?.uid, routineId)
  assignedRoutineId.value = routineId
}

const handleUnassign = async () => {
  await unassignRoutineFromUser(userStore.userData?.uid)
  assignedRoutineId.value = null
}
</script>



<template>
  <section>
    <!-- Encabezado actualizado -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Rutinas</h1>

      <div class="flex flex-wrap gap-3 items-center">

        <!-- Usuario con plan PREMIUM y rutina asignada -->
        <button
          v-if="userStore.userData?.plan_id !== 1 && assignedCoachRoutine"
          @click="openEditModal(assignedCoachRoutine)"
          class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg cursor-pointer
                hover:bg-green-700 transition-all duration-200"
          title="Rutina asignada — listo para despegar 🚀"
        >
          <IconRocket class="w-5 h-5" />
          Rutina del coach
        </button>

        <!-- Usuario con plan PREMIUM pero sin rutina asignada aún -->
        <button
          v-else-if="userStore.userData?.plan_id !== 1 && !assignedCoachRoutine"
          disabled
          class="flex items-center gap-2 bg-neutral-200 text-neutral-500 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Aún no tienes una rutina asignada"
        >
          <IconLockOpen2 class="w-5 h-5" />
          Sin rutina del coach
        </button>

        <!-- Usuario con plan Free -->
        <button
          v-else-if="userStore.userData?.plan_id === 1"
          disabled
          class="flex items-center gap-2 bg-yellow-100 text-yellow-700 border border-yellow-300 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Actualiza a Pro para recibir una rutina personalizada"
        >
          <IconLockOff class="w-5 h-5" />
          Requiere plan Pro
        </button>

        <!-- Crear rutina -->
        <button
          @click="showModal = true"
          class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition cursor-pointer"
        >
          <IconPlus class="w-5 h-5" />
          Nueva rutina
        </button>
      </div>
    </div>

      <RoutineFormModal
        :show="showModal"
        :initialData="selectedRoutine"
        @close="showModal = false; selectedRoutine = null"
        @saved="loadRoutines"
      />

      <!-- Vista solo lectura de rutina asignada -->
      <RoutineAssignedViewer
        v-if="viewAssignedRoutine"
        :show="viewAssignedRoutine"
        :routine="assignedCoachRoutine"
        @close="viewAssignedRoutine = false"
      />

    <!-- Delay sin mostrar nada -->
    <div v-if="loading && !showSkeleton" />

    <!-- Skeleton si tarda -->
    <div v-else-if="loading && showSkeleton" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 animate-pulse">
      <div v-for="n in 4" :key="n" class="bg-gray-100 h-40 rounded-xl shadow" />
    </div>

    <!-- Buscador y vista -->
    <div v-else>
      <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
        <div class="flex-1">
          <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar rutina</label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nombre de la rutina..."
            class="w-full border border-gray-300 rounded p-2 text-sm text-gray-700 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
          />
        </div>
        <div class="flex items-center gap-1">
          <button
            @click="viewMode = 'grid'"
            :class="['p-2 rounded', viewMode === 'grid' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
            title="Vista de tarjetas"
          >
            <IconLayoutGrid />
          </button>
          <button
            @click="viewMode = 'table'"
            :class="['p-2 rounded', viewMode === 'table' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
            title="Vista de tabla"
          >
            <IconLayoutList />
          </button>
        </div>
      </div>

      <!-- Vista Grid -->
      <div v-if="viewMode === 'grid' && filteredRoutines.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div
          v-for="routine in paginatedRoutines"
          :key="routine.id"
          class="bg-white shadow rounded-xl overflow-hidden flex flex-col hover:shadow-md transition cursor-pointer justify-between w-full"
          @click="openEditModal(routine)"
        >
          <div class="p-5 flex flex-col flex-grow">
            <h3 class="text-lg font-semibold text-[var(--color-primary)] mb-1">{{ routine.title }}</h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-3">{{ routine.description }}</p>
            <p class="text-xs text-gray-500 mt-auto">Ejercicios totales: {{ countExercises(routine) }}</p>

            <div class="mt-4 flex justify-between items-center">
              <!-- Asignar Rutina -->
              <label class="flex items-center gap-2 cursor-pointer select-none" @click.stop>
                <input
                  type="checkbox"
                  class="sr-only"
                  :checked="assignedRoutineId"
                  @change="($event) => {
                    if ($event.target.checked) {
                      handleAssign(routine.id)
                    } else {
                      handleUnassign()
                    }
                  }"
                />
                <div
                  class="w-10 h-6 flex items-center bg-gray-300 rounded-full p-1 duration-300 ease-in-out"
                  :class="{ 'bg-green-500': assignedRoutineId === routine.id }"
                >
                  <div
                    class="bg-white w-4 h-4 rounded-full shadow-md transform duration-300 ease-in-out"
                    :class="{ 'translate-x-4': assignedRoutineId === routine.id }"
                  ></div>
                </div>
                <span class="text-sm text-gray-700">
                  {{ assignedRoutineId === routine.id ? 'Asignada' : 'Sin asignar' }}
                </span>
              </label>

              <!-- Botón eliminar -->
              <button
                @click.stop="removeRoutine(routine)"
                class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-full transition"
                title="Eliminar rutina"
              >
                <IconTrash class="w-5 h-5" />
              </button>
            </div>
        </div>
        </div>
      </div>

      <!-- Vista Tabla -->
       <div v-else-if="viewMode === 'table' && filteredRoutines.length" class="overflow-x-auto">
      <table class="min-w-[600px] w-full text-left text-sm">
        
        <thead class="bg-gray-200 text-gray-600 font-medium">
          <tr>
            <th class="py-3 px-2">Nombre</th>
            <th class="px-2">Descripción</th>
            <th class="px-2">Ejercicios</th>
            <th class="px-2 text-right">Asignar</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="routine in paginatedRoutines"
            :key="routine.id"
            @click="openEditModal(routine)"
            class="border-t border-gray-200 hover:bg-gray-100 transition cursor-pointer"
          >
            <td class="py-3 px-2 font-semibold text-[var(--color-primary)]">{{ routine.title }}</td>
            <td class="py-3 px-2 text-gray-600 line-clamp-2">{{ routine.description }}</td>
            <td class="py-3 px-2">{{ countExercises(routine) }}</td>
            <td class="py-3 px-2 text-right">
              <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                <input
                  type="checkbox"
                  class="sr-only peer"
                  :checked="assignedRoutineId === routine.id"
                  @change="($event) => {
                    if ($event.target.checked) {
                      handleAssign(routine.id)
                    } else {
                      handleUnassign()
                    }
                  }"
                />
                <div
                  class="group peer bg-white rounded-full duration-300 w-12 h-6 ring-2 ring-[var(--color-primary)]
                    after:transition-transform after:duration-300 after:bg-[var(--color-primary)]
                    peer-checked:after:bg-green-500 peer-checked:ring-green-500
                    after:rounded-full after:absolute after:h-4 after:w-4 after:top-1 after:left-1
                    after:content-[''] peer-checked:after:translate-x-6 peer-hover:after:scale-95"
                ></div>
              </label>
            </td>
          </tr>
        </tbody>
      </table>
       </div>
      <!-- Sin resultados -->
      <div v-if="filteredRoutines.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
        </svg>
        <p class="text-lg font-semibold">
          {{ hasSearch ? 'Sin resultados' : 'Aún no has creado ninguna rutina' }}
        </p>
        <p class="text-sm">
          {{ hasSearch
            ? 'No se encontraron rutinas que coincidan con el filtro.'
            : 'Empieza creando tu primera rutina con el botón de arriba.' }}
        </p>
      </div>
    </div>

    <!-- Paginación -->
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

