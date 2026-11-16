<template>
  <!-- Sección de Programas Guiados y Desafíos -->
  <section class="bg-[var(--color-primary)] text-white text-center py-20">
    <div class="max-w-3xl mx-auto px-6">
      <h2 v-reveal class="text-3xl sm:text-4xl font-bold mb-4">¿Listo para superarte?</h2>
      <p v-reveal class="text-lg mb-8">Descubre nuestras rutinas gratuitas que te llevarán al siguiente nivel.</p>
      <router-link
        v-reveal
        to="/rutinas"
        class="btn-fill btn-fill--solid-light px-6 py-3 rounded-full font-semibold shadow"
      >
        <span class="btn-fill__label">Ver rutinas activas</span>
      </router-link>
    </div>
  </section>
</template>
