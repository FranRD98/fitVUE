<script setup>
  defineProps({
    show: Boolean,
    routine: Object
  })

  defineEmits(['close'])
</script>


<template>
  <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-6xl h-[90vh] flex flex-col relative overflow-hidden">
      
      <!-- Botón cerrar -->
      <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>

      <div class="p-6 overflow-y-auto flex-1">
        <div v-if="routine" class="space-y-8">
          <!-- Título -->
          <header class="border-b pb-4">
            <h1 class="text-3xl font-bold text-[var(--color-primary)]">{{ routine.title }}</h1>
            <p class="text-gray-600">{{ routine.description }}</p>
          </header>

          <!-- Días -->
          <div v-for="(day, index) in routine.days" :key="index" class="space-y-4">
            <h2 class="text-xl font-semibold text-[var(--color-secondary)]">{{ day.day }}</h2>

            <div v-if="day.exercises.length > 0" class="space-y-4">
              <div
                v-for="exercise in day.exercises"
                :key="exercise.id"
                class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 hover:shadow-md transition"
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
                    alt="Imagen"
                    class="w-16 h-16 rounded-lg object-cover shadow ml-4"
                  />
                </div>
              </div>
            </div>

            <p v-else class="text-sm text-gray-400">Sin ejercicios asignados para este día.</p>
          </div>
        </div>

        <div v-else class="text-center text-gray-500 mt-10">
          Rutina no encontrada.
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
