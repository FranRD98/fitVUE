<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { getReviewsById } from '@/api/services/progress'
import { IconArrowLeft } from '@tabler/icons-vue'

const router = useRouter()
const userStore = useUserStore()
const reviews = ref([])
const currentPage = ref(1)
const perPage = 10

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return reviews.value.slice(start, start + perPage)
})

const totalPages = computed(() => {
  return Math.ceil(reviews.value.length / perPage)
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(async () => {
  if (userStore.userData?.uid) {
    reviews.value = await getReviewsById(userStore.userData.uid)
  }
})

function goBack() {
  router.back()
}
</script>

<template>
  <section class="p-6 max-w-4xl mx-auto space-y-6">
    <!-- Botón volver -->
    <button
      @click="goBack"
      class="flex items-center gap-2 text-sm text-[var(--color-primary)] hover:underline"
    >
      <IconArrowLeft class="w-4 h-4" />
      Volver al panel
    </button>

    <!-- Título -->
    <h1 class="text-3xl font-bold text-[var(--color-primary)]">Historial completo de revisiones</h1>

    <!-- Lista de revisiones -->
    <ul class="space-y-4">
      <li
        v-for="review in paginatedReviews"
        :key="review.id"
        class="border border-gray-200 rounded-lg p-4 shadow hover:bg-gray-50 cursor-pointer transition"
        @click="router.push(`/user/${review.user_id}/${review.id}`)"
      >
        <div class="flex justify-between">
          <p class="text-[var(--color-primary)] font-semibold">Peso: {{ review.weight }} kg</p>
          <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
        </div>
      </li>
    </ul>

    <!-- Paginación -->
    <div class="flex justify-center items-center gap-4 mt-6">
      <button
        @click="currentPage--"
        :disabled="currentPage === 1"
        class="px-4 py-2 rounded bg-gray-200 text-sm hover:bg-gray-300 disabled:opacity-50"
      >
        Anterior
      </button>

      <span class="text-sm text-gray-600">
        Página {{ currentPage }} de {{ totalPages }}
      </span>

      <button
        @click="currentPage++"
        :disabled="currentPage === totalPages"
        class="px-4 py-2 rounded bg-gray-200 text-sm hover:bg-gray-300 disabled:opacity-50"
      >
        Siguiente
      </button>
    </div>
  </section>
</template>