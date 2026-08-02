<template>
  <div class="bg-white min-h-screen py-16 text-[var(--color-text)]">

    <!-- Encabezado -->
    <header class="text-center py-12">
      <h1 class="text-4xl md:text-5xl font-extrabold text-[var(--color-primary)]">¿Necesitas ayuda?</h1>
      <p class="mt-4 text-lg md:text-xl">Estamos disponibles 24/7 para resolver tus dudas.</p>
    </header>

    <!-- Sección principal -->
    <section class="px-6 py-16">
      <div class="container mx-auto max-w-6xl grid grid-cols-1 md:grid-cols-2 gap-16">

        <!-- Info contacto -->
        <div>
          <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-6">Información de contacto</h2>
          <ul class="space-y-5 text-[var(--color-text)] text-base">
            <li class="flex items-center gap-3">
              <svg class="w-5 h-5 text-[var(--color-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span>info@fitvue.com</span>
            </li>
            <li class="flex items-center gap-3">
              <svg class="w-5 h-5 text-[var(--color-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a9 9 0 0018 0M12 14v3m0 4h.01"/>
              </svg>
              <span>+34 600 123 456</span>
            </li>
            <li class="flex items-center gap-3">
              <svg class="w-5 h-5 text-[var(--color-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v2a2 2 0 01-2 2H9a2 2 0 01-2-2v-2m10 0H7"/>
              </svg>
              <span>Calle Ficticia 123, Alicante</span>
            </li>
          </ul>
        </div>

        <!-- Formulario -->
        <div>
          <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-6">Envíanos un mensaje</h2>
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-[var(--color-primary)]">Nombre</label>
              <input
                v-model="form.name"
                required
                aria-label="Nombre"
                type="text"
                placeholder="Tu nombre"
                class="w-full p-4 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-accent)]"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-primary)]">Correo electrónico</label>
              <input
                v-model="form.email"
                required
                aria-label="Correo electrónico"
                type="email"
                placeholder="email@ejemplo.com"
                class="w-full p-4 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-accent)]"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-primary)]">Mensaje</label>
              <textarea
                v-model="form.message"
                required
                aria-label="Mensaje"
                rows="5"
                placeholder="Escribe tu mensaje..."
                class="w-full p-4 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-accent)]"
              ></textarea>
            </div>
            <button
              type="submit"
              class="w-full py-3 bg-[var(--color-primary)] text-white font-semibold rounded-full hover:bg-[var(--color-secondary)] transition"
            >
              Enviar mensaje
            </button>
          </form>
        </div>

      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  name: '',
  email: '',
  message: ''
})

function handleSubmit() {
  if (!form.value.name || !form.value.email || !form.value.message) return

  // Aquí puedes enviar a tu backend o API de correo
  console.log('📬 Enviado:', form.value)

  alert('¡Mensaje enviado! Te responderemos pronto.')
  form.value = { name: '', email: '', message: '' }
}
</script>
