<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useUserStore } from '@/stores/user'

import IngredientModal from '@/components/dashboard/modals/IngredientModal.vue'
import {
  getIngredients,
  createIngredient,
  updateIngredient,
  deleteIngredient,
  isIngredientUsed 
} from '@/api/services/ingredients'

const ingredients = ref([])
const loading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const selectedIngredient = ref(null)
const viewMode = ref('grid')
const searchQuery = ref('')
const userStore = useUserStore()

// Icons
import {
  IconPlus,
  IconLayoutGrid,
  IconLayoutList,
  IconTrash
} from '@tabler/icons-vue'

/* Paginación */
const currentPage = ref(1)
const itemsPerPage = ref(16)

const totalPages = computed(() => {
  return Math.ceil(filteredIngredients.value.length / itemsPerPage.value)
})

const paginatedIngredients = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredIngredients.value.slice(start, start + itemsPerPage.value)
})

watch(searchQuery, () => {
  currentPage.value = 1
})

async function loadIngredients() {
  loading.value = true
  try {
    ingredients.value = await getIngredients(userStore.userData.uid)
  } finally {
    loading.value = false
  }
}

function openEditModal(ingredient) {
  selectedIngredient.value = ingredient
  isEditing.value = true
  showModal.value = true
}

function openNewModal() {
  selectedIngredient.value = null
  isEditing.value = false
  showModal.value = true
}

function handleCloseModal() {
  showModal.value = false
  selectedIngredient.value = null
  isEditing.value = false
}

async function handleSaveFromModal(data) {
  const payload = { ...data }

  if (isEditing.value && selectedIngredient.value) {
    await updateIngredient(selectedIngredient.value.id, payload)
  } else {
    await createIngredient(payload, userStore.userData.uid)  }

  await loadIngredients()
  handleCloseModal()
}

async function removeIngredient(ingredient) {
  const inUse = await isIngredientUsed(ingredient.id)

  if (inUse) {
    alert(`❌ El ingrediente "${ingredient.name}" está siendo usado en algún plato y no puede ser eliminado.`)
    return
  }

  const confirmed = confirm(`¿Estás seguro de que deseas eliminar "${ingredient.name}"? Esta acción no se puede deshacer.`)
  if (confirmed) {
    await deleteIngredient(ingredient.id)
    await loadIngredients()
  }
}


const filteredIngredients = computed(() =>
  ingredients.value.filter(i => i.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

onMounted(loadIngredients)

</script>

<template>
  <section>
    <!-- Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Ingredientes</h1>
      <button
        @click="openNewModal"
        class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition"
      >
        <IconPlus class="w-5 h-5"/>  
        Nuevo ingrediente
      </button>
    </div>

    <!-- Ingredient Modal -->
    <IngredientModal
      :show="showModal"
      :initialData="isEditing && selectedIngredient ? selectedIngredient : { name: '', calories: '', protein: '', carbs: '', fats: '' }"
      @save="handleSaveFromModal"
      @close="handleCloseModal"
    />

    <!-- Filtro y vista -->
<div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
      <div class="flex-1">
        <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar ingrediente</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Nombre del ingrediente..."
          class="w-full border border-gray-300 rounded p-2 text-sm text-gray-700 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
        />
      </div>

      <div class="flex items-center gap-1">
        <button
          @click="viewMode = 'grid'"
          :class="['p-2 rounded', viewMode === 'grid' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
          title="Vista de tarjetas"
        ><IconLayoutGrid/>
        </button>
        <button
          @click="viewMode = 'table'"
          :class="['p-2 rounded', viewMode === 'table' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-200']"
          title="Vista de tabla"
        > <IconLayoutList/>
        </button>
      </div>
    </div>

    <!-- Grid View -->
<div v-if="viewMode === 'grid' && filteredIngredients.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
     <div
        v-for="ingredient in paginatedIngredients"
        :key="ingredient.id"
        class="bg-white rounded-xl shadow-lg p-5 hover:shadow-md transition flex flex-col justify-between cursor-pointer w-full"
        @click="openEditModal(ingredient)"
      >
        <!-- Nombre -->
        <h3 class="text-xl font-bold text-[var(--color-primary)] mb-2 truncate">
          {{ ingredient.name }}
        </h3>

        <!-- Macronutrientes -->
        <ul class="text-sm text-gray-700 mb-4 space-y-1">
          <li><strong>Proteínas:</strong> {{ ingredient.protein }} g</li>
          <li><strong>Carbohidratos:</strong> {{ ingredient.carbs }} g</li>
          <li><strong>Grasas:</strong> {{ ingredient.fats }} g</li>
        </ul>

        <!-- Separador -->
        <div class="border-t border-gray-200 my-2"></div>

        <!-- Calorías + botón eliminar -->
        <div class="flex justify-between items-center mt-auto">
          
          <button
            @click.stop="removeIngredient(ingredient)"
            class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200"
            title="Eliminar"
          >
            <IconTrash class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Table View -->
     <div v-else-if="viewMode === 'table' && filteredIngredients.length" class="overflow-x-auto">

    <table class="min-w-[800px] w-full text-left text-sm">
      <thead class="bg-gray-200 text-gray-600 font-medium">
        <tr>
          <th class="py-3 px-2">Nombre</th>
          <th class="px-2">Calorías</th>
          <th class="px-2">Proteínas</th>
          <th class="px-2">Carbs</th>
          <th class="px-2">Grasas</th>
          <th class="px-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <tr
          v-for="ingredient in paginatedIngredients"
          :key="ingredient.id"
          class="border-t border-gray-200 hover:bg-gray-100 transition cursor-pointer"
          @click="openEditModal(ingredient)"
        >
          <td class="py-3 px-2 font-semibold text-[var(--color-primary)]">{{ ingredient.name }}</td>
          <td class="py-3 px-2">{{ ingredient.calories }}</td>
          <td class="py-3 px-2">{{ ingredient.protein }}</td>
          <td class="py-3 px-2">{{ ingredient.carbs }}</td>
          <td class="py-3 px-2">{{ ingredient.fats }}</td>
          <td class="py-3 px-2 text-right">
            <button
              @click.prevent.stop="removeIngredient(ingredient)"
              class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-300"
              title="Eliminar"
            >
              <IconTrash class="w-5 h-5" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

    <!-- Sin resultados -->
    <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
      </svg>

      <p class="text-lg font-semibold">
        <template v-if="ingredients.length === 0">
          Aún no has creado ingredientes
        </template>
        <template v-else>
          No se encontraron ingredientes que coincidan con los filtros aplicados
        </template>
      </p>

      <p v-if="ingredients.length > 0" class="text-sm">
        Intenta ajustar la búsqueda o limpia los filtros.
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

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.375rem;
  margin-bottom: 0.5rem;
}
</style>
