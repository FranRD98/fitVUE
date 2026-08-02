<script setup>
// Imports
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getPublishedRoutines, getPublishedRoutineCategoriesInUse } from '@/api/services/routines.js'
import Card from '@/components/Card.vue'

// Variables de estado
const route = useRoute()
const router = useRouter()

const routines = ref([])                 // Lista de rutinas
const categories = ref([])              // Lista de categorías
const selectedCategory = ref('Todas')   // Categoría seleccionada
const currentPage = ref(1)              // Página actual
const itemsPerPage = 6                  // Rutinas por página

// Carga inicial al montar el componente
onMounted(async () => {
  routines.value = await getPublishedRoutines()
  categories.value = await getPublishedRoutineCategoriesInUse()

  const categoryParam = route.params.category
  if (categoryParam) {
    selectedCategory.value = decodeURIComponent(categoryParam)
  }
})

// Reaccionar al cambio de categoría en la URL
watch(() => route.params.category, (newCategory) => {
  selectedCategory.value = newCategory ? decodeURIComponent(newCategory) : 'Todas'
})

// Filtrar rutinas por categoría
const filteredRoutines = computed(() => {
  if (selectedCategory.value === 'Todas') return routines.value

  const selectedCat = categories.value.find(cat => cat.title === selectedCategory.value)
  if (!selectedCat) return []

  return routines.value.filter(routine => routine.id_category === selectedCat.id)
})

// Rutinas paginadas
const paginatedRoutines = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredRoutines.value.slice(start, start + itemsPerPage)
})

// Total de páginas
const totalPages = computed(() => Math.ceil(filteredRoutines.value.length / itemsPerPage))

// Cambiar página
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

// Actualizar la URL al cambiar el filtro desde el select
watch(selectedCategory, (newVal) => {
  const categoryPath = newVal === 'Todas'
    ? '/rutinas'
    : `/rutinas/categoria/${encodeURIComponent(newVal)}`
  router.push(categoryPath)
})

// Generar slug a partir de ID de categoría
function getCategoryNameById(id_category) {
  const cat = categories.value.find(c => c.id === id_category)
  const title = cat?.title || 'general'

  return title
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // Eliminar acentos
    .replace(/\s+/g, '-')            // Espacios → guiones
    .replace(/[^a-z0-9-]/g, '')      // Quitar caracteres no válidos
    .replace(/-+/g, '-')             // Evitar guiones dobles
    .replace(/^-+|-+$/g, '')         // Quitar guiones extremos
}
</script>

<template>
  <section v-if="routines" class="max-w-7xl mx-auto px-6 py-10">
    <div class="flex flex-col md:flex-row gap-6">

      <!-- Filtros -->
      <aside class="w-full md:w-1/4 space-y-6">
        <h1 class="text-3xl font-bold text-[var(--color-primary)]">Rutinas</h1>

        <div>
          <label class="block text-sm font-medium text-[var(--color-primary)] mb-2">
            Filtrar por categoría
          </label>
          <select
            class="w-full border border-gray-300 rounded p-2 text-gray-700"
            v-model="selectedCategory"
          >
            <option value="Todas">Todas</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.title"
            >
              {{ category.title }}
            </option>
          </select>
        </div>
      </aside>

      <!-- Contenido principal -->
      <div class="w-full">
        <h2 class="text-3xl font-bold text-[var(--color-primary)] mb-6">
          {{ selectedCategory === 'Todas' ? 'Todas las rutinas' : selectedCategory }}
        </h2>

        <!-- Resultados -->
        <main
          v-if="paginatedRoutines.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          <Card
            v-for="routine in paginatedRoutines"
            :key="routine.id"
            :item="routine"
            :categories="categories"
            :route-to="`/rutinas/${getCategoryNameById(routine.id_category)}/${routine.id}`"
          />
        </main>

        <!-- Sin resultados -->
        <div
          v-else
          class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
          </svg>
          <p class="text-lg font-semibold">Sin resultados</p>
          <p class="text-sm">No se encontraron rutinas que coincidan con los filtros aplicados.</p>
        </div>

        <!-- Paginación -->
        <div class="flex justify-center mt-10 space-x-2" v-if="totalPages > 1">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-4 py-2 rounded',
              page === currentPage ? 'bg-[var(--color-primary)] text-white' : 'bg-white border'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

