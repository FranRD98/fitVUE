<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/supabase/useAuth';

const { register, authError } = useAuth()

const email = ref('')
const password = ref('')
const userData = ref({})

// Cargar posibles datos temporales desde localStorage
onMounted(() => {
  const storedData = localStorage.getItem('userData')
  if (storedData) {
    userData.value = JSON.parse(storedData)
  }
})

const registerUser = async () => {
  await register({
    email: email.value,
    password: password.value,
    ...userData.value
  })

  localStorage.removeItem('userData')
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-[var(--color-bg-light)]">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold text-center text-[var(--color-primary)]">Registrarse</h2>
      
      <form @submit.prevent="registerUser" class="mt-6">
        <div class="mb-4">
          <label class="block text-[var(--color-secondary)]">Correo electrónico</label>
          <input v-model="email" type="email" 
            class="w-full px-4 py-2 mt-2 border border-[var(--color-primary)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" 
            placeholder="tucorreo@example.com">
        </div>

        <div class="mb-4">
          <label class="block text-[var(--color-secondary)]">Contraseña</label>
          <input v-model="password" type="password" 
            class="w-full px-4 py-2 mt-2 border border-[var(--color-primary)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" 
            placeholder="••••••••">
        </div>

        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>

        <button 
        type="submit" 
        class="w-full px-4 py-2 mt-4 text-[var(--color-text-dark)] bg-[var(--color-primary)] rounded-lg hover:bg-[var(--color-secondary)] transition">
          Registrarse
        </button>
      </form>

      <p class="mt-4 text-sm text-center text-[var(--color-text)]">
        ¿No tienes cuenta? <a href="/empezar" class="text-[var(--color-primary)] hover:underline">Consigue tu cambio ya</a>
      </p>
    </div>
  </div>
</template>