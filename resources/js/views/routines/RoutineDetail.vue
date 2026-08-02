<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getRoutineById, getRoutineCategories } from '@/api/services/routines.js'

const route = useRoute()
const router = useRouter()

const routine = ref(null)
const categoryName = ref('')
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    const id = route.params.id
    const data = await getRoutineById(id)

    if (!data || data.published === false) {
      return router.push('/404')
    }

    routine.value = data

    const categories = await getRoutineCategories()
    const cat = categories.find(cat => cat.id === data.id_category)
    categoryName.value = cat?.name || 'General'

  } catch (error) {
    console.error('Error cargando rutina:', error)
    router.push('/404')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="max-w-4xl mx-auto px-6 py-10">
    <div v-if="loading" class="text-center text-gray-500">Cargando rutina...</div>

    <div v-else-if="routine" class="space-y-10 bg-white p-8 rounded-2xl shadow-xl">
      <!-- Encabezado -->
      <header class="space-y-2 border-b pb-4">
        <h1 class="text-3xl font-bold text-[var(--color-primary)]">{{ routine.title }}</h1>
        <p class="text-gray-600">{{ routine.description }}</p>
        <p class="text-sm text-gray-400">Categoría: {{ categoryName }}</p>
      </header>

      <!-- Días -->
      <div v-for="(day, index) in routine.days" :key="index" class="space-y-4">
        <h2 class="text-2xl font-semibold text-[var(--color-secondary)]">{{ day.day }}</h2>

        <div v-if="day.exercises.length > 0" class="space-y-4">
          <div
            v-for="exercise in day.exercises"
            :key="exercise.id"
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 transition-all hover:shadow-md"
          >
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-[var(--color-primary)] font-semibold text-lg">
                  {{ exercise.name }}
                  <span class="text-sm text-gray-500">({{ exercise.sets }}x{{ exercise.reps }})</span>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                  {{ exercise.description || 'Sin descripción' }}
                </p>
              </div>
              <img
                v-if="exercise.image"
                :src="exercise.image"
                alt="Ejercicio"
                class="w-16 h-16 rounded-lg object-cover shadow-sm ml-4"
              />
            </div>
          </div>
        </div>

        <p v-else class="text-sm text-gray-400">Sin ejercicios asignados para este día.</p>
      </div>
    </div>

    <div v-else class="text-center text-gray-500">Rutina no encontrada.</div>
  </section>
</template>


