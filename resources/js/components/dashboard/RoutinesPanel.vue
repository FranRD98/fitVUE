<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { usePlan } from '@/composables/usePlan'
import {
  getRoutinesByUser, assignRoutineToUser, getAssignedRoutine, unassignRoutineFromUser,
  updateRoutine, getCoachAssignedRoutine, deleteRoutine, duplicateRoutine
} from '@/api/services/routines.js'
import RoutineFormModal from '@/components/dashboard/modals/RoutineFormModal.vue'
import RoutineAssignedViewer from '@/components/dashboard/RoutineAssignedViewer.vue'

import { IconPlus, IconLayoutGrid, IconLayoutList, IconLockOff, IconRocket, IconLockOpen2, IconDotsVertical, IconPlayerPlay } from '@tabler/icons-vue'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'

const FREE_ROUTINE_LIMIT = 3

const router = useRouter()
const routines = ref([])

const viewAssignedRoutine = ref(false)
const userStore = useUserStore()
const { isPro, isFree } = usePlan()
const showModal = ref(false)
const showUpgradePrompt = ref(false)
const selectedRoutine = ref(null)
const openMenuId = ref(null)

// El client_reference_id permite al webhook de Stripe identificar a qué usuario aplicar el plan tras el pago.
const upgradeUrl = computed(() => {
  const uid = userStore.userData?.uid
  return uid
    ? `https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00?client_reference_id=${uid}`
    : 'https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00'
})

// routines.value ya viene filtrada por el backend a solo las del usuario actual
function openCreateModal() {
  if (isFree.value && routines.value.length >= FREE_ROUTINE_LIMIT) {
    showUpgradePrompt.value = true
    return
  }

  showModal.value = true
}

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

function exercisesSummary(routine) {
  return (routine.exercises || []).map(e => e.name).join(', ')
}

function countExercises(routine) {
  return routine.exercises?.length || 0
}

async function removeRoutine(routine) {
  if (confirm(`¿Eliminar la rutina "${routine.title}"?`)) {
    await deleteRoutine(routine.id)
    await loadRoutines()
  }
  openMenuId.value = null
}

async function handleDuplicate(routine) {
  await duplicateRoutine(routine.id)
  await loadRoutines()
  openMenuId.value = null
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

function closeMenuOnOutsideClick() {
  openMenuId.value = null
}

onMounted(() => {
  loadRoutines()
  document.addEventListener('click', closeMenuOnOutsideClick)
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenuOnOutsideClick)
})

const openEditModal = (routine) => {
  openMenuId.value = null

  if (
    isPro.value &&
    assignedCoachRoutine.value &&
    routine.id === assignedCoachRoutine.value.id
  ) {
    viewAssignedRoutine.value = true // Mostrar la vista solo lectura
  } else {
    selectedRoutine.value = routine
    showModal.value = true
  }
}

function startRoutine(routine) {
  router.push({ path: `/user/${userStore.userData?.uid}/iniciar-rutina`, query: { routineId: routine.id } })
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
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Entrenamiento</h1>

      <div class="flex flex-wrap gap-3 items-center">

        <!-- Usuario con plan PREMIUM y rutina asignada -->
        <button
          v-if="isPro && assignedCoachRoutine"
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
          v-else-if="isPro && !assignedCoachRoutine"
          disabled
          class="flex items-center gap-2 bg-neutral-200 text-neutral-500 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Aún no tienes una rutina asignada"
        >
          <IconLockOpen2 class="w-5 h-5" />
          Sin rutina del coach
        </button>

        <!-- Usuario con plan Free -->
        <button
          v-else-if="isFree"
          disabled
          class="flex items-center gap-2 bg-yellow-100 text-yellow-700 border border-yellow-300 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Actualiza a Pro para recibir una rutina personalizada"
        >
          <IconLockOff class="w-5 h-5" />
          Requiere plan Pro
        </button>

        <!-- Crear rutina -->
        <button
          @click="openCreateModal"
          class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition cursor-pointer"
        >
          <IconPlus class="w-5 h-5" />
          Crear rutina
        </button>
      </div>
    </div>

      <RoutineFormModal
        :show="showModal"
        :initialData="selectedRoutine"
        @close="showModal = false; selectedRoutine = null"
        @saved="loadRoutines"
      />

      <!-- Límite del plan gratuito alcanzado -->
      <div
        v-if="showUpgradePrompt"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex justify-center items-center px-4"
        @click.self="showUpgradePrompt = false"
      >
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-yellow-100 flex items-center justify-center">
            <IconLockOff class="w-7 h-7 text-yellow-600" />
          </div>
          <h2 class="text-lg font-bold text-[var(--color-primary)] mb-2">Límite de rutinas alcanzado</h2>
          <p class="text-sm text-gray-600 mb-6">
            El plan Free permite hasta {{ FREE_ROUTINE_LIMIT }} rutinas. Actualiza tu plan para crear rutinas ilimitadas.
          </p>
          <div class="flex flex-col gap-2">
            <a
              :href="upgradeUrl"
              class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition font-semibold"
            >
              Actualizar plan
            </a>
            <button
              @click="showUpgradePrompt = false"
              class="text-sm text-gray-500 hover:text-gray-700 transition py-1"
            >
              Ahora no
            </button>
          </div>
        </div>
      </div>

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
        <div class="hidden md:flex items-center gap-1">
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

      <!-- Vista Grid (única vista en móvil) -->
      <div v-if="viewMode === 'grid' && filteredRoutines.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="routine in paginatedRoutines"
          :key="routine.id"
          class="bg-white shadow rounded-xl overflow-hidden flex flex-col justify-between w-full relative"
        >
          <div class="p-4 flex flex-col flex-grow">
            <div class="flex justify-between items-start gap-2 mb-1">
              <h3 class="text-lg font-semibold text-[var(--color-primary)] cursor-pointer" @click="openEditModal(routine)">
                {{ routine.title }}
              </h3>

              <div class="relative shrink-0">
                <button
                  type="button"
                  @click.stop="openMenuId = openMenuId === routine.id ? null : routine.id"
                  class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100"
                  aria-label="Más opciones"
                >
                  <IconDotsVertical class="w-5 h-5" />
                </button>

                <div
                  v-if="openMenuId === routine.id"
                  class="absolute right-0 top-8 z-10 bg-white border border-gray-200 rounded-lg shadow-lg w-36 py-1 text-sm"
                  @click.stop
                >
                  <button @click="handleDuplicate(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50">Duplicar</button>
                  <button @click="openEditModal(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50">Editar</button>
                  <button @click="removeRoutine(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-red-600">Borrar</button>
                </div>
              </div>
            </div>

            <p class="text-sm text-gray-500 mb-3 line-clamp-2 cursor-pointer" @click="openEditModal(routine)">
              {{ exercisesSummary(routine) || 'Sin ejercicios todavía' }}
            </p>
            <p class="text-xs text-gray-400 mb-3">Ejercicios totales: {{ countExercises(routine) }}</p>

            <button
              @click="startRoutine(routine)"
              class="w-full flex items-center justify-center gap-2 bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-2.5 rounded-lg transition mb-2"
            >
              <IconPlayerPlay class="w-4 h-4" /> Empezar Rutina
            </button>

            <!-- Marcar como rutina activa: control secundario -->
            <label class="flex items-center gap-2 cursor-pointer select-none justify-center" @click.stop>
              <input
                type="checkbox"
                class="sr-only"
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
                class="w-8 h-5 flex items-center bg-gray-300 rounded-full p-0.5 duration-300 ease-in-out"
                :class="{ 'bg-green-500': assignedRoutineId === routine.id }"
              >
                <div
                  class="bg-white w-3.5 h-3.5 rounded-full shadow-md transform duration-300 ease-in-out"
                  :class="{ 'translate-x-3': assignedRoutineId === routine.id }"
                ></div>
              </div>
              <span class="text-xs text-gray-500">
                {{ assignedRoutineId === routine.id ? 'Rutina activa' : 'Marcar como activa' }}
              </span>
            </label>
        </div>
        </div>
      </div>

      <!-- Vista Tabla (solo escritorio) -->
       <div v-else-if="viewMode === 'table' && filteredRoutines.length" class="hidden md:block overflow-x-auto">
      <table class="min-w-[600px] w-full text-left text-sm">

        <thead class="bg-gray-200 text-gray-600 font-medium">
          <tr>
            <th class="py-3 px-2">Nombre</th>
            <th class="px-2">Ejercicios</th>
            <th class="px-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="routine in paginatedRoutines"
            :key="routine.id"
            class="border-t border-gray-200 hover:bg-gray-100 transition"
          >
            <td class="py-3 px-2 font-semibold text-[var(--color-primary)] cursor-pointer" @click="openEditModal(routine)">{{ routine.title }}</td>
            <td class="py-3 px-2">{{ countExercises(routine) }}</td>
            <td class="py-3 px-2 text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="startRoutine(routine)"
                  class="flex items-center gap-1 bg-[var(--color-primary)] text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-[var(--color-secondary)]"
                >
                  <IconPlayerPlay class="w-3.5 h-3.5" /> Empezar
                </button>
                <div class="relative">
                  <button
                    type="button"
                    @click.stop="openMenuId = openMenuId === routine.id ? null : routine.id"
                    class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100"
                    aria-label="Más opciones"
                  >
                    <IconDotsVertical class="w-5 h-5" />
                  </button>
                  <div
                    v-if="openMenuId === routine.id"
                    class="absolute right-0 top-8 z-10 bg-white border border-gray-200 rounded-lg shadow-lg w-36 py-1 text-sm"
                    @click.stop
                  >
                    <button @click="handleDuplicate(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50">Duplicar</button>
                    <button @click="openEditModal(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50">Editar</button>
                    <button @click="removeRoutine(routine)" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-red-600">Borrar</button>
                  </div>
                </div>
              </div>
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
