<script setup>
  import { ref, watch, computed } from 'vue'
  import { useRoute, useRouter } from 'vue-router'

  import { useUserStore } from '@/stores/user'
  import { getReviewsById } from '@/api/services/progress'
  import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'
  import ProgressChart from '@/components/dashboard/charts/ProgressChart.vue'

  const userStore = useUserStore()
  const router = useRouter()  
const route = useRoute()

  const reviews = ref([])
  const lastReview = computed(() => reviews.value[0])
  const { loading, showSkeleton, start, finish } = useDelayedSkeleton(300) // <-- 300ms

watch(
  () => [userStore.userData?.uid, route.query.refresh],
  async ([uid, refresh]) => {
    if (!uid) return

    start()

    try {
      const data = await getReviewsById(uid)
      reviews.value = data
    } catch (err) {
      console.error('Error cargando reviews:', err)
    } finally {
      finish()
    }

    if (refresh) {
      const { refresh, ...rest } = route.query
      router.replace({ path: route.path, query: rest })
    }
  },
  { immediate: true }
)

  function formatDate(date) {
    return new Date(date).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    })
  }

</script>

<template>

  <!-- Nada mientras loading y aún no se ha activado skeleton -->
  <section v-if="loading && !showSkeleton" />

  <!-- Skeleton si la carga tarda más de 150ms -->
  <section v-else-if="loading && showSkeleton" class="p-6 space-y-6 animate-pulse">
      <!-- Header Skeleton -->
      <div class="flex justify-between items-center animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/4"></div>
        <div class="h-8 bg-gray-200 rounded w-1/4"></div>
      </div>

    
    <!-- Stats Cards Skeleton Version -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-pulse">
      <div class="bg-white shadow rounded-xl p-4">
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        <div class="mt-2 h-6 bg-gray-200 rounded w-3/4"></div>
      </div>
      <div class="bg-white shadow rounded-xl p-4">
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        <div class="mt-2 h-6 bg-gray-200 rounded w-3/4"></div>
      </div>
      <div class="bg-white shadow rounded-xl p-4">
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        <div class="mt-2 h-6 bg-gray-200 rounded w-3/4"></div>
      </div>
      <div class="bg-white shadow rounded-xl p-4 flex justify-between items-center">
        <!-- Texto simulado -->
        <div class="w-2/3">
          <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-2/3"></div>
        </div>

        <!-- Botón simulado -->
        <div class="w-1/3 flex justify-end">
          <div class="h-8 w-24 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 animate-pulse">
  <!-- Gráfica principal -->
  <div class="col-span-1 lg:col-span-3 bg-white rounded-xl shadow p-4">
    <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
    <div class="w-full h-64 rounded bg-gray-200"></div>
  </div>

  <!-- Historial lateral -->
  <div class="bg-white rounded-xl shadow p-4 h-full overflow-y-auto">
    <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
    <ul class="space-y-3">
      <li class="text-gray-500 text-sm">
        <div class="h-4 bg-gray-200 rounded"></div>
      </li>
      <li class="border border-gray-100 p-3 rounded-lg">
        <div class="h-4 bg-gray-200 rounded w-full"></div>
        <div class="h-3 bg-gray-200 rounded w-2/3 mt-1"></div>
      </li>
    </ul>

    <ul class="space-y-3">
      <li class="border border-gray-100 p-3 rounded-lg">
        <div class="h-4 bg-gray-200 rounded w-full"></div>
        <div class="h-3 bg-gray-200 rounded w-2/3 mt-1"></div>
      </li>
    </ul>

    <ul class="space-y-3">
      <li class="border border-gray-100 p-3 rounded-lg">
        <div class="h-4 bg-gray-200 rounded w-full"></div>
        <div class="h-3 bg-gray-200 rounded w-2/3 mt-1"></div>
      </li>
    </ul>
  </div>
</div>

  </section>

  <section v-else class="px-2 py-4 sm:px-6 space-y-6">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)]">
      Panel de control
    </h1>

    <router-link
      to="/dashboard/newReview"
      class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white px-4 py-2 rounded-lg shadow transition text-sm sm:text-base"
    >
      Nueva revisión
    </router-link>
  </div>
    
    <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      <div class="bg-white shadow rounded-xl p-4">
        <h3 class="text-sm text-gray-500">Revisiones totales</h3>
        <p class="text-2xl font-bold text-[var(--color-primary)]">{{ reviews.length }}</p>
      </div>
      <div class="bg-white shadow rounded-xl p-4">
        <h3 class="text-sm text-gray-500">Último peso registrado</h3>
        <p class="text-2xl font-bold text-[var(--color-primary)]">
          {{ lastReview?.weight ? `${lastReview.weight} kg` : '—' }}
        </p>
      </div>
      <div class="bg-white shadow rounded-xl p-4">
        <h3 class="text-sm text-gray-500">Última revisión</h3>
        <p class="text-2xl font-bold text-[var(--color-primary)]">
          {{ lastReview?.created_at ? formatDate(lastReview.created_at) : '—' }}
        </p>
      </div>
      <div class="bg-white shadow rounded-xl p-4 flex justify-between items-center">
        <div>
          <h3 class="text-sm text-gray-500 mb-1">Entrenamiento</h3>
          <p class="text-[var(--color-primary)] font-semibold">Empieza tu rutina</p>
        </div>

          <router-link
          :to="`/user/${userStore.userData?.uid}/iniciar-rutina`"
            class="relative bottom-0 flex justify-center items-center gap-2 rounded-xl text-white bg-[var(--color-primary)] px-12 py-3 text-sm shadow-sm transition duration-300 group hover:bg-cyan-500 hover:text-white active:scale-95 focus:outline-none focus:ring-2 focus:ring-cyan-500"
          >
            <span class="truncate transition-all duration-300 group-hover:opacity-0 group-hover:-translate-x-4">
              ¿Iniciar?
            </span>

            <div class="absolute flex items-center gap-2 opacity-0 translate-x-4 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0">
              ¡A tope!
            </div>

          </router-link>

      </div>
    </div>

    <!-- Chart -->
  <div class="flex flex-col lg:flex-row gap-6">
    <div class="w-full lg:w-3/4 bg-white rounded-xl shadow p-4">
      <h3 class="text-lg font-semibold mb-4 text-[var(--color-primary)]">Evolución del peso corporal</h3>
      <ProgressChart v-if="reviews.length" :reviews="reviews" />    
    </div>


      <!-- Historial -->
    <div class="w-full lg:w-1/4 bg-white rounded-xl shadow p-4 max-h-[500px] overflow-y-auto">
      <h3 class="text-lg font-semibold mb-4 text-[var(--color-primary)]">Historial de revisiones</h3>
      <ul class="space-y-3">
          <li v-if="!loading && reviews.length === 0" class="text-gray-500 text-sm">Sin revisiones registradas.</li>
          <li
            v-for="review in reviews.slice(0, 10)"
            :key="review.id"
            class="border border-gray-100 p-3 rounded-lg hover:bg-gray-50 transition cursor-pointer"
            @click="router.push(`/user/${review.user_id}/${review.id}`)"
          >
            <p class="text-sm text-gray-600">Peso: {{ review.weight }} kg</p>
            <p class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</p>
          </li>
          <li class="text-center mt-4">
            <router-link
              to="/dashboard/historial"
              class="text-sm text-[var(--color-primary)] hover:underline"
            >
              Ver todas las revisiones →
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </section>


</template>
