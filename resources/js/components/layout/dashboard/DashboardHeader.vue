<script setup>
import { computed } from 'vue'
import { IconChevronLeft } from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia
import { useDashboardNav } from '@/composables/useDashboardNav'
import { isSecondaryPanel } from '@/composables/useDashboardMenu'
import { useGreeting } from '@/composables/useGreeting'

const userStore = useUserStore()  // Usamos el store de usuario
const { activeKey, goTo } = useDashboardNav()
const { randomMessage } = useGreeting()

// En móvil, las secciones secundarias (Ejercicios, Dietas, Estadísticas...)
// se abren desde Perfil, así que aquí se muestra una flecha para volver a
// Perfil en vez del menú hamburguesa que existía antes.
const showBackToProfile = computed(() => isSecondaryPanel(activeKey.value))
</script>

<template>
  <!-- Móvil: barra mínima siempre presente (reserva la zona segura del notch
       en todas las pantallas); solo muestra la flecha en secciones secundarias
       (el saludo vive dentro de "Entrenamiento", para ganar espacio). -->
  <header
    v-if="userStore.userData"
    class="md:hidden bg-white shadow-sm px-4 pb-3 pt-[calc(env(safe-area-inset-top)+0.75rem)] flex items-center min-h-[2.75rem]"
  >
    <button v-if="showBackToProfile" class="text-gray-500" aria-label="Volver a Perfil" @click="goTo('config')">
      <IconChevronLeft class="w-7 h-7" />
    </button>
  </header>

  <!-- Escritorio: cabecera completa con avatar, saludo y frase -->
  <header
    v-if="userStore.userData"
    class="hidden md:flex bg-white shadow-sm px-10 py-6"
  >
    <div class="flex items-center gap-4">
      <img
        :src="userStore.userData.profile_image || '/img/default-profile.svg'"
        alt="profile"
        class="w-12 h-12 rounded-full"
      />
      <div class="flex-1">
        <h1 class="text-xl font-bold text-[var(--color-primary)]">
          ¡Hola, {{ userStore.userData?.name || 'Usuario' }}!
        </h1>
        <p class="text-sm text-gray-500 leading-snug max-h-[3.5rem] overflow-hidden">
          {{ randomMessage }}
        </p>
      </div>
    </div>
  </header>
</template>