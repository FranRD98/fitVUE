<script setup>
import { ref } from 'vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia

const userStore = useUserStore()  // Usamos el store de usuario

const welcomeMessages = [
  'Estás haciendo un gran progreso hoy, ¡sigue así! 💪',
  '¡Hoy es un gran día para avanzar en tus metas! 🚀',
  '¡Vamos con todo, estás imparable! 🔥',
  'No te detengas, cada paso cuenta 🏃‍♂️',
  '¡Excelente trabajo, sigue construyendo tu mejor versión! 🛠️',
  'Hoy entrenas el cuerpo… y la disciplina 🧠💪',
  '¡Eres más constante que el WiFi del gimnasio! 📶',
  '¡A romperla! 💥 Tu constancia es tu superpoder.'
]

const randomMessage = ref(welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)])
const emit = defineEmits(['toggleSidebar'])

</script>

<template>
  <header
    v-if="userStore.userData"
    class="bg-white shadow-sm px-4 pb-4 pt-[calc(env(safe-area-inset-top)+1rem)] md:px-10 md:py-6 md:pt-6"
  >
    <div class="flex items-center gap-4">
      <!-- Botón hamburguesa solo en móvil -->
      <button class="md:hidden text-2xl" @click="$emit('toggleSidebar')">☰</button>

      <img
        :src="userStore.userData.profile_image || '/img/default-profile.svg'"
        alt="profile"
        class="w-10 h-10 md:w-12 md:h-12 rounded-full"
      />
      <div class="flex-1">
        <h1 class="text-lg md:text-xl font-bold text-[var(--color-primary)]">
          ¡Hola, {{ userStore.userData?.name || 'Usuario' }}!
        </h1>
        <p class="text-xs md:text-sm text-gray-500 leading-snug max-h-[3.5rem] overflow-hidden">
          {{ randomMessage }}
        </p>

      </div>
    </div>
  </header>
</template>