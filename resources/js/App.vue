<script setup>
import Header from '@/components/layout/Header.vue'
import Footer from '@/components/layout/MainFooter.vue'
import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'

const route = useRoute()
const userStore = useUserStore()

// Esperar a que se obtengan los datos del usuario
const showLayout = computed(() => {
  if (isStandalone && ['/login', '/sign-in'].includes(route.path)) return false

  return !route.path.startsWith('/dashboard') && !route.path.startsWith('/empezar') && !route.path.startsWith('/user')
})

// PWA instalada (sin barra del navegador) sin sesión: se abre directamente
// en /login por la guarda de rutas. Ahí no tiene sentido la cabecera/footer
// de la web pública (menú, enlaces, etc.) — se ocultan para que se vea una
// pantalla de acceso limpia, como el resto de la app instalada.
const isStandalone = window.matchMedia('(display-mode: standalone)').matches
  || window.navigator.standalone === true

// El dashboard usa un fondo de página gris (detrás de tarjetas blancas); la
// web pública usa blanco. Se marca en <body> para que el rebote de scroll en
// iOS muestre el mismo color en vez de un destello blanco fuera de lugar.
watch(
  () => route.path,
  (path) => {
    document.body.classList.toggle('dashboard-bg', !showLayout.value || path.startsWith('/user'))
  },
  { immediate: true }
)

// Llamar a la función para obtener los datos del usuario al montar
userStore.fetchUserData()
userStore.initAuthListener()

</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Esperar a que los datos del usuario estén listos antes de mostrar el layout -->
    <Header v-if="showLayout" />

    <main class="flex-1">
      <router-view />
    </main>

    <Footer v-if="showLayout" />
  </div>
</template>
