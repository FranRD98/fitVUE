<script setup>
import { ref } from 'vue'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const email = ref('')
const password = ref('')
const error = ref('')

const login = async () => {
  if (!email.value || !password.value) {
    error.value = 'Todos los campos son obligatorios'
    return
  }

  error.value = ''

  await userStore.login({ email: email.value, password: password.value })

  if (userStore.authError) {
    error.value = 'Credenciales incorrectas o usuario no registrado'
  }
}
</script>

<template>
  <div class="flex flex-col md:flex-row h-[70vh] justify-center overflow-hidden">
    <!-- Columna izquierda (formulario) -->
    <div class="w-full md:w-1/2 flex items-center justify-center bg-white px-6 sm:px-10 md:px-20 pt-10 pb-6 md:py-0">
      <div class="w-full max-w-md space-y-8">
        
        <!-- Branding -->
        <div class="mt-4 md:mt-0">
          <h1 class="text-5xl md:text-7xl font-extrabold leading-none">
            <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
          </h1>
        </div>

        <!-- Título -->
        <div class="pt-4 md:pt-0">
          <h2 class="text-3xl sm:text-4xl font-bold text-[var(--color-primary)]">Iniciar sesión</h2>
          <p class="text-sm text-gray-500 mt-1">Accede a tu cuenta para continuar.</p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="login" class="space-y-4">
          <div>
            <label class="block text-sm text-[var(--color-secondary)] mb-1">Correo electrónico</label>
            <input v-model="email" type="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" placeholder="tucorreo@fitvue.com">
          </div>

          <div>
            <label class="block text-sm text-[var(--color-secondary)] mb-1">Contraseña</label>
            <input v-model="password" type="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" placeholder="••••••••">
          </div>

            <p
              v-if="error"
              class="flex items-start gap-3 bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4"
            >
              <span>{{ error }}</span>
            </p>  

          <button type="submit" class="w-full bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-2 rounded-lg transition">
            Iniciar sesión
          </button>
        </form>

        <!-- Enlace crear cuenta -->
        <p class="text-sm text-center text-[var(--color-text)] pt-4">
          ¿No tienes cuenta?
          <router-link to="/sign-in" class="text-[var(--color-primary)] hover:underline">Crear cuenta</router-link>
        </p>
      </div>
    </div>

    <!-- Columna derecha (imagen en escritorio) -->
    <div class="hidden md:block md:w-1/2 bg-gray-200">
      <img 
      src="/img/login_form.jpg"
      alt="Vista previa"
      class="w-full h-full object-cover" />
    </div>
  </div>
</template>
