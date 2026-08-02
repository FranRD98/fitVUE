<script setup>
import Header from '@/components/layout/Header.vue'
import Footer from '@/components/layout/MainFooter.vue'
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'

const route = useRoute()
const userStore = useUserStore()

// Esperar a que se obtengan los datos del usuario
const showLayout = computed(() => {
  return !route.path.startsWith('/dashboard') && !route.path.startsWith('/empezar') && !route.path.startsWith('/user')
})
// Llamar a la función para obtener los datos del usuario al montar
userStore.fetchUserData()
userStore.initAuthListener()

</script>

<template>
  <div>
    <!-- Esperar a que los datos del usuario estén listos antes de mostrar el layout -->
    <Header v-if="showLayout" />

    <main>
      <router-view />
    </main>

    <Footer v-if="showLayout" />
  </div>
</template>
