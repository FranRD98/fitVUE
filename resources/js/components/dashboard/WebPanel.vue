<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  getRoutines, updateRoutine, deleteRoutine,
  getRoutineCategories, createRoutineCategory, deleteRoutineCategory
} from '@/api/services/routines'
import { getCatalogDiets, updateDiet, deleteDiet } from '@/api/services/diets'
import RoutineFormModal from '@/components/dashboard/modals/RoutineFormModal.vue'
import DietFormModal from '@/components/dashboard/modals/DietFormModal.vue'
import GuidesPanel from '@/components/dashboard/GuidesPanel.vue'
import { IconPlus, IconTrash } from '@tabler/icons-vue'

const tab = ref('routines')
const tabs = [
  { key: 'routines', label: 'Rutinas' },
  { key: 'categories', label: 'Categorías' },
  { key: 'diets', label: 'Dietas' },
  { key: 'guides', label: 'Guías' },
]

const routines = ref([])
const categories = ref([])
const diets = ref([])
const loading = ref(true)
const loadError = ref('')
const categoryTitle = ref('')
const savingCategory = ref(false)

const showRoutineModal = ref(false)
const selectedRoutine = ref(null)
const showDietModal = ref(false)
const selectedDiet = ref(null)

const categoryName = computed(() => {
  const map = Object.fromEntries(categories.value.map((category) => [category.id, category.title]))
  return (id) => map[id] || 'Sin categoría'
})

async function load() {
  loading.value = true
  loadError.value = ''
  const [routinesResult, categoriesResult, dietsResult] = await Promise.allSettled([
    getRoutines({ catalog: true }),
    getRoutineCategories(),
    getCatalogDiets(),
  ])

  categories.value = categoriesResult.status === 'fulfilled' ? categoriesResult.value : []
  routines.value = routinesResult.status === 'fulfilled' ? routinesResult.value : []
  diets.value = dietsResult.status === 'fulfilled' ? dietsResult.value : []

  if (routinesResult.status === 'rejected' || dietsResult.status === 'rejected') {
    loadError.value = 'No se han podido cargar las rutinas o dietas de la web. En el servidor falta la migración que añade is_catalog.'
  } else if (categoriesResult.status === 'rejected') {
    loadError.value = 'No se han podido cargar las categorías.'
  }

  loading.value = false
}

onMounted(load)

function openRoutine(routine = null) {
  selectedRoutine.value = routine
  showRoutineModal.value = true
}

function openDiet(diet = null) {
  selectedDiet.value = diet
  showDietModal.value = true
}

async function toggleRoutine(routine) {
  const next = !routine.published
  if (next && !routine.id_category) {
    alert('Asigna una categoría antes de publicarla.')
    openRoutine(routine)
    return
  }
  await updateRoutine(routine.id, { ...routine, is_catalog: true, published: next })
  await load()
}

async function removeRoutine(routine) {
  if (!confirm(`¿Eliminar «${routine.title}» del catálogo de la web?`)) return
  await deleteRoutine(routine.id)
  await load()
}

async function addCategory() {
  const title = categoryTitle.value.trim()
  if (!title) return
  savingCategory.value = true
  try {
    await createRoutineCategory(title)
    categoryTitle.value = ''
    await load()
  } catch (error) {
    alert(error.response?.data?.message || 'No se ha podido crear la categoría.')
  } finally {
    savingCategory.value = false
  }
}

async function removeCategory(category) {
  if (!confirm(`¿Eliminar la categoría «${category.title}»?`)) return
  try {
    await deleteRoutineCategory(category.id)
    await load()
  } catch (error) {
    alert(error.response?.data?.message || 'No se ha podido eliminar la categoría.')
  }
}

async function toggleDiet(diet) {
  await updateDiet(diet.id, { ...diet, is_catalog: true, published: !diet.published })
  await load()
}

async function removeDiet(diet) {
  if (!confirm(`¿Eliminar «${diet.title || 'esta dieta'}» del catálogo de la web?`)) return
  await deleteDiet(diet.id)
  await load()
}
</script>

<template>
  <section>
    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)] mb-1">WEB</h1>
    <p class="text-sm text-gray-500 mb-5">Contenido de la landing, aparte de las rutinas y dietas de los clientes.</p>
    <p v-if="loadError" class="text-sm text-red-600 mb-4">{{ loadError }}</p>

    <div class="flex gap-2 overflow-x-auto mb-6">
      <button
        v-for="item in tabs"
        :key="item.key"
        type="button"
        @click="tab = item.key"
        class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition"
        :class="tab === item.key
          ? 'bg-[var(--color-primary)] text-white'
          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      >
        {{ item.label }}
      </button>
    </div>

    <div v-if="tab === 'routines'" class="space-y-4">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Las publicadas aparecen en /rutinas, agrupadas por categoría.</p>
        <button
          type="button"
          @click="openRoutine()"
          class="shrink-0 flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg text-sm font-semibold"
        >
          <IconPlus class="w-4 h-4" /> Nueva
        </button>
      </div>

      <div v-if="loading" class="bg-gray-100 h-24 rounded-xl animate-pulse" />
      <div v-else-if="!routines.length" class="bg-gray-100 md:bg-white shadow rounded-xl p-6 text-sm text-gray-500">
        Todavía no hay rutinas de la web.
      </div>
      <div v-else class="bg-gray-100 md:bg-white shadow rounded-xl divide-y divide-gray-100 overflow-hidden">
        <div v-for="routine in routines" :key="routine.id" class="flex items-center gap-3 px-4 py-3">
          <button type="button" @click="openRoutine(routine)" class="flex-1 min-w-0 text-left">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ routine.title }}</p>
            <p class="text-xs text-gray-400">{{ categoryName(routine.id_category) }}</p>
          </button>
          <button
            type="button"
            @click="toggleRoutine(routine)"
            class="text-xs font-semibold px-3 py-1.5 rounded-full shrink-0"
            :class="routine.published ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'"
          >
            {{ routine.published ? 'Publicada' : 'Borrador' }}
          </button>
          <button type="button" @click="removeRoutine(routine)" class="text-gray-400 hover:text-red-500" aria-label="Eliminar">
            <IconTrash class="w-4 h-4" />
          </button>
        </div>
      </div>

      <RoutineFormModal
        :show="showRoutineModal"
        :initialData="selectedRoutine"
        catalog
        @close="showRoutineModal = false; selectedRoutine = null"
        @saved="load"
      />
    </div>

    <div v-else-if="tab === 'categories'" class="space-y-4">
      <p class="text-sm text-gray-500">Salen en el menú de Rutinas de la landing cuando hay alguna rutina publicada en ellas.</p>
      <form class="flex gap-2" @submit.prevent="addCategory">
        <input
          v-model="categoryTitle"
          type="text"
          placeholder="Nueva categoría"
          class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
        />
        <button
          type="submit"
          :disabled="savingCategory"
          class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-60"
        >
          Añadir
        </button>
      </form>
      <div v-if="!categories.length" class="text-sm text-gray-400">No hay categorías.</div>
      <div v-else class="bg-gray-100 md:bg-white shadow rounded-xl divide-y divide-gray-100 overflow-hidden">
        <div v-for="category in categories" :key="category.id" class="flex items-center justify-between gap-3 px-4 py-3">
          <p class="text-sm font-semibold text-gray-800">{{ category.title }}</p>
          <button type="button" @click="removeCategory(category)" class="text-gray-400 hover:text-red-500" aria-label="Eliminar">
            <IconTrash class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="tab === 'diets'" class="space-y-4">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Separadas de las dietas de cada cliente. Márcalas como publicadas para tenerlas listas en el catálogo de la web.</p>
        <button
          type="button"
          @click="openDiet()"
          class="shrink-0 flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg text-sm font-semibold"
        >
          <IconPlus class="w-4 h-4" /> Nueva
        </button>
      </div>
      <div v-if="loading" class="bg-gray-100 h-24 rounded-xl animate-pulse" />
      <div v-else-if="!diets.length" class="bg-gray-100 md:bg-white shadow rounded-xl p-6 text-sm text-gray-500">
        Todavía no hay dietas de la web.
      </div>
      <div v-else class="bg-gray-100 md:bg-white shadow rounded-xl divide-y divide-gray-100 overflow-hidden">
        <div v-for="diet in diets" :key="diet.id" class="flex items-center gap-3 px-4 py-3">
          <button type="button" @click="openDiet(diet)" class="flex-1 min-w-0 text-left">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ diet.title || 'Sin título' }}</p>
          </button>
          <button
            type="button"
            @click="toggleDiet(diet)"
            class="text-xs font-semibold px-3 py-1.5 rounded-full shrink-0"
            :class="diet.published ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'"
          >
            {{ diet.published ? 'Publicada' : 'Borrador' }}
          </button>
          <button type="button" @click="removeDiet(diet)" class="text-gray-400 hover:text-red-500" aria-label="Eliminar">
            <IconTrash class="w-4 h-4" />
          </button>
        </div>
      </div>
      <DietFormModal
        :show="showDietModal"
        :initialData="selectedDiet"
        catalog
        @close="showDietModal = false; selectedDiet = null"
        @saved="load"
      />
    </div>

    <GuidesPanel v-else />
  </section>
</template>
