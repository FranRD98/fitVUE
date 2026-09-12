<script setup>
import { useUserStore } from '@/stores/user'
import { useGreeting } from '@/composables/useGreeting'

const userStore = useUserStore()
const { randomMessage } = useGreeting()
</script>

<template>
  <!-- Solo escritorio: en móvil el saludo vive dentro de "Entrenamiento" y la
       zona segura/vuelta a Perfil las lleva <main> directamente. -->
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
