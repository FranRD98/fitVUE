<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { supabase } from '@/supabase/config'

const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')

const login = async () => {
  if (!email.value || !password.value) {
    error.value = 'Todos los campos son obligatorios'
    return
  }

  error.value = ''

  const { data, error: authError } = await supabase.auth.signInWithPassword({
    email: email.value,
    password: password.value,
  })

  if (authError) {
    console.error(authError)
    error.value = 'Credenciales incorrectas o usuario no registrado'
    return
  }

  router.push('/dashboard')
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-[var(--color-bg-light)]">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
      <h2 class="text-2xl font-bold text-center text-[var(--color-primary)]">Iniciar sesión</h2>
      
      <form @submit.prevent="login" class="mt-6">
        <div class="mb-4">
          <label class="block text-[var(--color-secondary)]">Correo electrónico</label>
          <input v-model="email" type="email" 
            class="w-full px-4 py-2 mt-2 border border-[var(--color-accent)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]" 
            placeholder="tucorreo@example.com">
        </div>

        <div class="mb-4">
          <label class="block text-[var(--color-secondary)]">Contraseña</label>
          <input v-model="password" type="password" 
            class="w-full px-4 py-2 mt-2 border border-[var(--color-accent)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]" 
            placeholder="••••••••">
        </div>

        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>

        <button type="submit" 
          class="w-full px-4 py-2 mt-4 text-[var(--color-text-dark)] bg-[var(--color-primary)] rounded-lg hover:bg-[var(--color-secondary)] transition">
          Iniciar sesión
        </button>
      </form>

      <p class="mt-4 text-sm text-center text-[var(--color-text)]">
        ¿No tienes cuenta? <a href="/empezar" class="text-[var(--color-primary)] hover:underline">Consigue tu cambio ya</a>
      </p>
    </div>
  </div>
</template>
