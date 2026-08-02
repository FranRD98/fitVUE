<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { getPlates, deletePlate } from '@/api/services/plates'
import { getIngredients } from '@/api/services/ingredients'
import { useUserStore } from '@/stores/user'

// Importamos la función getMacros desde plates.js
import { getMacros } from '@/api/services/plates'

import PlateModal from '@/components/dashboard/modals/PlateModal.vue'
import {
  IconPlus,
  IconLayoutGrid,
  IconLayoutList,
  IconTrash
} from '@tabler/icons-vue'

const plates = ref([])
const ingredients = ref([])
const loading = ref(true)
const searchQuery = ref('')
const viewMode = ref('grid')
const userStore = useUserStore()

const showModal = ref(false)
const selectedPlate = ref(null)

/* Paginación */
const currentPage = ref(1)
const itemsPerPage = ref(16)

const totalPages = computed(() => {
  return Math.ceil(filteredPlates.value.length / itemsPerPage.value)
})

const paginatedPlates = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredPlates.value.slice(start, start + itemsPerPage.value)
})

watch(searchQuery, () => {
  currentPage.value = 1
})


async function loadPlates() {
  loading.value = true
  try {
    plates.value = await getPlates(userStore.userData.uid)
    ingredients.value = await getIngredients(userStore.userData.uid)  
  } finally {
    loading.value = false
  }
}

onMounted(loadPlates)

function openNewModal() {
  selectedPlate.value = null
  showModal.value = true
}
function getTotal(plate, macro) {
  const total = plate.items?.reduce((acc, item) => {
    return acc + (item[macro] || 0)
  }, 0)
  return total.toFixed(1)
}

function openEditModal(plate) {
  selectedPlate.value = plate
  showModal.value = true
}

async function removePlate(plate) {
  if (confirm(`¿Eliminar el plato "${plate.name}"?`)) {
    await deletePlate(plate.id)
    await loadPlates()
  }
}

const filteredPlates = computed(() =>
  plates.value.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

function getIngredientName(id) {
  const ing = ingredients.value.find(i => i.id === id)
  return ing ? ing.name : 'Desconocido'
}

</script>

<template>
  <section>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Platos</h1>
      <button @click="openNewModal" class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition">
        <IconPlus class="w-5 h-5" />
        Nuevo plato
      </button>
    </div>

    <!-- Modal -->
    <PlateModal
      :show="showModal"
      :plate="selectedPlate"
      :ingredients="ingredients"
      @close="showModal = false"
      @saved="loadPlates"
    />

    <!-- Filtro -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 w-full">
      <input
        v-model="searchQuery"
        placeholder="Buscar plato..."
        class="w-full border border-gray-300 rounded p-2"
      />

      <div class="flex gap-1">
        <button @click="viewMode = 'grid'" :class="['p-2 rounded', viewMode === 'grid' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']">
          <IconLayoutGrid />
        </button>
        <button @click="viewMode = 'table'" :class="['p-2 rounded', viewMode === 'table' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']">
          <IconLayoutList />
        </button>
      </div>
    </div>

        <!-- VISTA GRID -->
    <div v-if="viewMode === 'grid' && filteredPlates.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div
        v-for="plate in paginatedPlates"
        :key="plate.id"
        class="bg-white p-4 rounded-lg shadow cursor-pointer hover:shadow-md transition flex flex-col justify-between w-full"
        @click="openEditModal(plate)"
      >
        <h3 class="text-lg font-bold truncate text-[var(--color-primary)]">{{ plate.name }}</h3>
        <p class="text-sm text-gray-600">{{ plate.items.length }} ingrediente{{ plate.items.length !== 1 ? 's' : '' }}</p>

        <!-- Totales nutricionales -->
        <div class="mt-2 text-sm text-gray-700 space-y-1">
          <p>Proteínas: {{ getMacros(plate).protein }} g</p>
          <p>Carbohidratos: {{ getMacros(plate).carbs }} g</p>
          <p>Grasas: {{ getMacros(plate).fats }} g</p>
        </div>

        <!-- Divisor -->
        <div class="border-t border-gray-200 my-3"></div>

        <!-- Calorías + botón eliminar -->
        <div class="flex justify-between items-center mt-auto">
          <span
            class="inline-block px-2 py-1 rounded-full text-xs font-bold w-fit"
            style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);"
          >
            {{ getMacros(plate).calories }} kcal
          </span>
          <button
            @click.stop="removePlate(plate)"
            class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-full transition"
          >
            <IconTrash class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- VISTA TABLE -->
     <div v-else-if="viewMode === 'table' && filteredPlates.length" class="overflow-x-auto">

    <table class="min-w-[900px] w-full text-left text-sm">
      <thead class="bg-gray-100 text-gray-600 font-semibold">
        <tr>
          <th class="px-4 py-2">Nombre</th>
          <th class="px-4 py-2 text-center">Calorías</th>
          <th class="px-4 py-2 text-center">Proteínas</th>
          <th class="px-4 py-2 text-center">Carbohidratos</th>
          <th class="px-4 py-2 text-center">Grasas</th>
          <th class="px-4 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <tr
          v-for="plate in paginatedPlates"
          :key="plate.id"
          class="hover:bg-gray-50 transition cursor-pointer"
          @click="openEditModal(plate)"
        >
          <td class="px-4 py-3 font-medium text-[var(--color-primary)]">{{ plate.name }}</td>
          <td class="px-4 py-3 text-center">{{ getTotal(plate, 'calories') }} kcal</td>
          <td class="px-4 py-3 text-center">{{ getTotal(plate, 'protein') }} g</td>
          <td class="px-4 py-3 text-center">{{ getTotal(plate, 'carbs') }} g</td>
          <td class="px-4 py-3 text-center">{{ getTotal(plate, 'fats') }} g</td>
          <td class="px-4 py-3 text-right">
            <button
              @click.stop="removePlate(plate)"
              class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-full transition focus:outline-none focus:ring-2 focus:ring-red-300"
              title="Eliminar"
            >
              <IconTrash class="w-5 h-5" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
     </div>

    <!-- Sin resultados o sin contenido -->
    <div v-if="filteredPlates.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
      </svg>

      <p class="text-lg font-semibold">
        <template v-if="plates.length === 0">
          Aún no has creado ningún plato
        </template>
        <template v-else>
          No se encontraron platos que coincidan con los filtros aplicados
        </template>
      </p>

      <p v-if="plates.length > 0" class="text-sm">
        Intenta modificar la búsqueda o limpia los filtros.
      </p>
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
