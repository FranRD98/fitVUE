<script setup>
import { ref } from 'vue'

const show = ref(false)
function open() { show.value = true }
function close() { show.value = false }
defineExpose({ open, close })
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white dark:bg-[#0f172a] md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-md md:max-h-[85vh] bg-white dark:bg-[#1e293b] md:rounded-xl shadow-xl flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b dark:border-white/10 pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <span class="w-6"></span>
        <h2 class="font-semibold text-[var(--color-primary)]">Acerca de</h2>
        <button type="button" @click="close" class="text-[var(--color-primary)] font-medium">Hecho</button>
      </header>

      <div class="p-8 overflow-y-auto flex-1 flex flex-col items-center text-center gap-4">
        <img src="/img/icons/apple-touch-icon.png" alt="fitVUE" class="w-24 h-24 object-contain rounded-2xl shadow" />
        <h1 class="text-2xl font-bold dark:text-white">
          <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-300">
          La app para mejorar tu estilo de vida: entrenamientos, nutrición y seguimiento de progreso.
        </p>
        <p class="text-xs text-gray-400 mt-4">fitvue.es</p>
      </div>
    </div>
  </div>
</template>
