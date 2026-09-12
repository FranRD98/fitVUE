<script setup>
import { IconX } from '@tabler/icons-vue'

defineProps({
  show: Boolean,
  routine: Object
})

defineEmits(['close'])
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-2xl md:max-h-[85vh] bg-white md:rounded-xl shadow-xl flex flex-col overflow-hidden">

      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <span class="w-6"></span>
        <h2 class="font-semibold text-[var(--color-primary)] truncate">{{ routine?.title || 'Rutina' }}</h2>
        <button type="button" @click="$emit('close')" class="text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
          <IconX class="w-6 h-6" />
        </button>
      </header>

      <div class="p-4 overflow-y-auto flex-1">
        <div v-if="routine" class="space-y-4">
          <p v-if="routine.description" class="text-sm text-gray-600 pb-2 border-b">{{ routine.description }}</p>

          <div v-if="routine.exercises?.length" class="space-y-3">
            <div
              v-for="(exercise, i) in routine.exercises"
              :key="i"
              class="bg-white border border-gray-200 rounded-xl shadow-sm p-4"
            >
              <div class="flex justify-between items-center gap-3">
                <div class="min-w-0">
                  <h3 class="text-[var(--color-primary)] font-semibold truncate">{{ exercise.name }}</h3>
                  <p class="text-sm text-gray-500 mt-0.5">{{ exercise.sets || 0 }} series x {{ exercise.reps || 0 }} reps</p>
                </div>
                <img
                  v-if="exercise.image"
                  :src="exercise.image"
                  alt="Imagen"
                  class="w-14 h-14 rounded-lg object-cover shadow shrink-0"
                />
              </div>
            </div>
          </div>

          <p v-else class="text-sm text-gray-400">Sin ejercicios en esta rutina.</p>
        </div>

        <div v-else class="text-center text-gray-500 mt-10">
          Rutina no encontrada.
        </div>
      </div>
    </div>
  </div>
</template>
