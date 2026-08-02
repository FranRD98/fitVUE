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

      <p class="text-sm text-gray-500 mb-3 line-clamp-2 sm:line-clamp-3">
        {{ item.description }}
      </p>

      <!-- Info inferior -->
      <div class="text-xs text-gray-400 mt-auto space-y-1">
        <p v-if="item.author"><strong>Autor:</strong> {{ item.author }}</p>
        <p v-if="item.id_category">
          <strong>Categoría:</strong>
          {{ categories.find(cat => cat.id === item.id_category)?.title || '—sin categoría—' }}
        </p>
        <p v-if="item.created_at">
          <strong>Fecha: </strong>{{ new Date(item.created_at).toLocaleDateString() || '—' }}
        </p>
      </div>
    </div>
  </router-link>
</template>