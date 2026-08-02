<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getGuidesById } from '@/api/services/guides.js'

const route = useRoute()
const router = useRouter()

const guideId = route.params.id
const category = route.params.category
const guide = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const fetchedGuide = await getGuidesById(guideId)

    if (!fetchedGuide?.published) {
      // Si no está publicada, redirige al 404
      return router.push('/404')
    }

    guide.value = fetchedGuide
  } catch (err) {
    console.error('Error al cargar la guía:', err)
    router.push('/404')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="max-w-6xl mx-auto px-6 py-12">
    <div v-if="loading" class="text-center text-gray-500">Cargando guía...</div>

    <div v-else-if="guide" class="bg-white rounded-lg shadow-lg overflow-hidden">
      <!-- Imagen destacada -->
      <img
        :src="guide.header_image || 'https://placehold.co/800x400?text=Sin+imagen'"
        alt="Imagen de la guía"
        class="w-full h-96 object-cover"
      />

      <!-- Contenido -->
      <div class="p-6">
        <h1 class="text-4xl font-bold text-[var(--color-primary)] mb-4">{{ guide.title }}</h1>

        <!-- Metadatos -->
        <div class="text-sm text-gray-500 mb-4 flex flex-wrap gap-4">
          <span><strong>Autor:</strong> {{ guide.author }}</span>
          <span><strong>Categoría:</strong> {{ category || 'Sin categoría' }}</span>
          <span><strong>Publicado:</strong> {{ new Date(guide.created_at).toLocaleDateString('es-ES') || 'N/A' }}</span>
        </div>

        <!-- Contenido detallado -->
        <div class="space-y-8">
          <p class="text-gray-600 text-justify whitespace-pre-line">{{ guide.content }}</p>
        </div>
      </div>
    </div>

    <div v-else class="text-red-500 text-center mt-10">No se encontró la guía.</div>
  </section>
</template>