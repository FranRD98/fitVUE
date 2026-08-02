<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia

import { getDiets, deleteDiet, getCoachAssignedDiet } from '@/api/services/diets'
import { getIngredients } from '@/api/services/ingredients'
import DietFormModal from '@/components/dashboard/modals/DietFormModal.vue'
import DietAssignedViewer from '@/components/dashboard/DietAssignedViewer.vue'


// Icons
import { IconPlus, IconLayoutGrid, IconLayoutList, IconTrash, IconLockOff, IconRocket, IconLockOpen2 } from '@tabler/icons-vue'

const diets = ref([])

const viewAssignedDiet = ref(false)
const userStore = useUserStore()  // Usamos el store de usuario
const allIngredients = ref([])
const showModal = ref(false)
const selectedDiet = ref(null)
const loading = ref(true)
const viewMode = ref('grid')
const searchQuery = ref('')
const hasDietsLoaded = ref(false)

const assignedCoachDiet = ref(null)

/* Paginación */
const currentPage = ref(1)
const itemsPerPage = ref(16)

const totalPages = computed(() => {
  return Math.ceil(filteredDiets.value.length / itemsPerPage.value)
})

const paginatedDiets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredDiets.value.slice(start, start + itemsPerPage.value)
})

watch(searchQuery, () => {
  currentPage.value = 1
})


const calculateTotalNutrients = (diet) => {
  let totalCalories = 0;
  let totalProtein = 0;
  let totalCarbs = 0;
  let totalFats = 0;

  diet.meals?.forEach(meal => {
    meal.items?.forEach(plate => {
      plate.items?.forEach(item => {
        const ingredient = item.ingredient;
        const quantity = item.quantity || 0;

        if (ingredient) {
          totalCalories += (ingredient.calories || 0) * (quantity / 100);
          totalProtein += (ingredient.protein || 0) * (quantity / 100);
          totalCarbs += (ingredient.carbs || 0) * (quantity / 100);
          totalFats += (ingredient.fats || 0) * (quantity / 100);
        }
      });
    });
  });

  return {
    totalCalories,
    totalProtein,
    totalCarbs,
    totalFats
  };
};


const enrichDietItems = (dietList, ingredients) => {
  return dietList.map(diet => {
    const enrichedMeals = diet.meals?.map(meal => {
      const enrichedPlates = meal.items?.map(plate => {
        const enrichedPlateItems = plate.items?.map(item => {
          const ingredient = ingredients.find(i => i.id === item.ingredientId)
          return {
            ...item,
            ingredient: ingredient || null
          }
        }) || []

        return {
          ...plate,
          items: enrichedPlateItems
        }
      }) || []

      return {
        ...meal,
        items: enrichedPlates
      }
    }) || []

    // Calcular los totales para esta dieta
    const { totalCalories, totalProtein, totalCarbs, totalFats } = calculateTotalNutrients(diet)

    return {
      ...diet,
      meals: enrichedMeals,
      totalCalories,
      totalProtein,
      totalCarbs,
      totalFats
    }
  })
}

const loadDiets = async () => {
  if (!userStore.userData?.uid) return

  loading.value = true

  try {
    const [dietsRaw, ingredients] = await Promise.all([
      getDiets(userStore.userData.uid),
      getIngredients(userStore.userData.uid)
    ])

    allIngredients.value = ingredients
    diets.value = enrichDietItems(dietsRaw, ingredients)

const rawAssignedDiet = await getCoachAssignedDiet(userStore.userData?.uid)
assignedCoachDiet.value = rawAssignedDiet
  ? enrichDietItems([rawAssignedDiet], ingredients)[0]
  : null
  
  } catch (e) {
    console.error('Error al cargar dietas o ingredientes:', e)
  } finally {
      hasDietsLoaded.value = true
      loading.value = false
  }
}


const openEditModal = (diet) => {

  if (
    userStore.userData?.plan_id !== 1 &&
    assignedCoachDiet.value &&
    diet.id === assignedCoachDiet.value.id
  ) {
    viewAssignedDiet.value = true // Mostrar la vista solo lectura

  } else {
      selectedDiet.value = diet
      showModal.value = true
  }
}




const handleDelete = async (diet) => {
  if (confirm(`¿Seguro que quieres eliminar la dieta "${diet.title}"?`)) {
    try {
      await deleteDiet(diet.id)
      await loadDiets()
    } catch (error) {
      console.error('Error al eliminar la dieta:', error)
    }
  }
}

const filteredDiets = computed(() => {
  return diets.value.filter(diet =>
    diet.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

watch(
  () => userStore.userData?.uid,
  (id) => {
    if (id) loadDiets()
  },
  { immediate: true }
)
</script>

<template>
  <section>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Dietas</h1>

        <div class="flex gap-4 items-center">

        <!-- Usuario con plan PREMIUM y rutina asignada -->
        <button
          v-if="userStore.userData?.plan_id !== 1 && assignedCoachDiet"
          @click="openEditModal(assignedCoachDiet)"
          class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg cursor-pointer
                hover:bg-green-700 transition-all duration-200"
          title="Dieta asignada — listo para despegar 🚀"
        >
          <IconRocket class="w-5 h-5" />
          Dieta del coach
        </button>

        <!-- Usuario con plan PREMIUM pero sin dieta asignada aún -->
        <button
          v-else-if="userStore.userData?.plan_id !== 1 && !assignedCoachDiet"
          disabled
          class="flex items-center gap-2 bg-neutral-200 text-neutral-500 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Aún no tienes una dieta asignada"
        >
          <IconLockOpen2 class="w-5 h-5" />
          Sin dieta del coach
        </button>

        <!-- Usuario con plan Free -->
        <button
          v-else-if="userStore.userData?.plan_id === 1"
          disabled
          class="flex items-center gap-2 bg-yellow-100 text-yellow-700 border border-yellow-300 px-4 py-2 rounded-lg cursor-not-allowed"
          title="Actualiza a Pro para recibir una dieta personalizada"
        >
          <IconLockOff class="w-5 h-5" />
          Requiere plan Pro
        </button>

        <!-- Crear dieta -->
        <button
          @click="showModal = true"
          class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition"
        >
          <IconPlus class="w-5 h-5" />
          Nueva dieta
        </button>

            </div>
    </div>

    <DietFormModal
      :show="showModal"
      :initialData="selectedDiet"
      @close="showModal = false; selectedDiet = null"
      @saved="loadDiets"
    />

    <!-- Vista solo lectura de la dieta asignada -->
      <DietAssignedViewer
        v-if="viewAssignedDiet"
        :show="viewAssignedDiet"
        :diet="assignedCoachDiet"
        @close="viewAssignedDiet = false"
      />

    <div v-if="loading">Cargando dietas...</div>
    
    <div v-else-if="hasDietsLoaded && filteredDiets.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
      </svg>
      <p class="text-lg font-semibold">
        {{ diets.length === 0 ? 'Aún no has creado ninguna dieta.' : 'No se encontraron dietas con los filtros aplicados.' }}
      </p>
      <p class="text-sm" v-if="diets.length !== 0">Prueba con otra búsqueda o crea una nueva dieta.</p>
    </div>

  <div v-else class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
      <div class="flex-1">
        <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar dieta</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Nombre de la dieta..."
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

    <!-- Grid view -->
    <div v-if="viewMode === 'grid' && filteredDiets.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div
        v-for="diet in paginatedDiets"
        :key="diet.id"
        class="bg-white rounded-xl shadow-lg p-5 hover:shadow-md transition flex flex-col justify-between cursor-pointer w-full"
        @click="openEditModal(diet)"
      >
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-xl font-bold text-[var(--color-primary)] truncate">{{ diet.title }}</h3>
          <span class="inline-block px-2 py-1 rounded-full text-xs font-bold w-fit"
            style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);">
            {{ diet.totalCalories.toFixed(0) }} kcal
          </span>
        </div>

        <div class="mt-4">
          <p><strong>Proteínas:</strong> {{ diet.totalProtein.toFixed(1) }} g</p>
          <p><strong>Carbohidratos:</strong> {{ diet.totalCarbs.toFixed(1) }} g</p>
          <p><strong>Grasas:</strong> {{ diet.totalFats.toFixed(1) }} g</p>
        </div>

        <div class="border-t border-gray-200 my-3"></div>

        <div class="flex justify-between items-center">
          <span class="inline-block px-2 py-1 rounded-full text-xs font-bold w-fit"
            style="background-color: rgba(var(--color-primary-rgb), 0.2); color: var(--color-primary);">
            {{ diet.meals?.length || 0 }} comida{{ diet.meals?.length === 1 ? '' : 's' }}
          </span>
          <button
            @click.prevent.stop="handleDelete(diet)"
            class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200"
            title="Eliminar"
          >
            <IconTrash class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Table view -->
     <div v-else-if="viewMode === 'table' && filteredDiets.length" class="overflow-x-auto">
    <table class="min-w-[900px] w-full text-left text-sm">
      <thead class="bg-gray-200 text-gray-600 font-medium">
        <tr>
          <th class="py-3 px-2">Nombre</th>
          <th class="px-2">Descripción</th>
          <th class="px-2">Calorías</th>
          <th class="px-2">Proteínas</th>
          <th class="px-2">Carbohidratos</th>
          <th class="px-2">Grasas</th>
          <th class="px-2">Comidas</th>
          <th class="px-2">Fecha</th>
          <th class="px-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <tr
          v-for="diet in paginatedDiets"
          :key="diet.id"
          class="border-t border-gray-200 hover:bg-gray-100 transition"
          @click="openEditModal(diet)"
        >
          <td class="py-3 px-2 text-[var(--color-primary)]">{{ diet.title }}</td>
          <td class="py-3 px-2 text-gray-600 line-clamp-2">{{ diet.description }}</td>
          <td class="py-3 px-2">{{ diet.totalCalories.toFixed(0) }} kcal</td>
          <td class="py-3 px-2">{{ diet.totalProtein.toFixed(1) }} g</td>
          <td class="py-3 px-2">{{ diet.totalCarbs.toFixed(1) }} g</td>
          <td class="py-3 px-2">{{ diet.totalFats.toFixed(1) }} g</td>
          <td class="py-3 px-2">{{ diet.meals?.length || 0 }}</td>
          <td class="py-3 px-2">
            {{ new Date(diet.created).toLocaleDateString() || '—' }}
          </td>
          <td class="py-3 px-2 text-right">
            <button
              @click.prevent.stop="handleDelete(diet)"
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