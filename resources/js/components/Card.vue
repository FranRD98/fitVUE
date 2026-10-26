<script setup>
defineProps({
  item: Object,
  categories: Array,
  routeTo: String
})
</script>

<template>
  <router-link
    :to="routeTo"
    class="bg-white rounded-xl shadow hover:shadow-lg overflow-hidden flex flex-col transition-all h-full"
  >
    <!-- Imagen -->
    <img
      :src="item?.header_image || 'https://placehold.co/600x400?text=Sin+Imagen'"
      alt="Imagen"
      class="w-full h-48 object-cover"
      loading="lazy"
    />

    <!-- Contenido -->
    <div class="p-4 flex flex-col flex-grow">
      <h3 class="text-lg font-semibold text-[var(--color-primary)] mb-1">
        {{ item.title }}
      </h3>

      <p class="text-sm text-gray-500 line-clamp-2 sm:line-clamp-3">
        {{ item.description }}
      </p>
    </div>
  </router-link>
</template>