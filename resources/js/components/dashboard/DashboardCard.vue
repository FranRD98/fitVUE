<script setup>
  defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    button: Boolean
  })
</script>


<template>
  <div class="bg-white rounded-xl shadow p-4 flex flex-col justify-between h-full min-h-[120px] w-full">
    <!-- Título + icono -->
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-sm text-gray-500 font-medium">{{ title }}</h3>
      <component v-if="icon" :is="icon" class="w-5 h-5 text-[var(--color-primary)]" />
    </div>

    <!-- Valor o contenido -->
    <div class="text-2xl font-bold text-[var(--color-primary)]">
      <slot name="value">{{ value }}</slot>
    </div>

    <!-- Botón opcional -->
    <slot name="footer">
      <button
        v-if="button"
        class="mt-4 text-sm text-white bg-[var(--color-primary)] px-3 py-1 rounded hover:bg-[var(--color-secondary)] transition"
      >
        Ejecutar
      </button>
    </slot>
  </div>
</template>

