<template>
  <div class="relative h-screen w-full">
    <!-- Imagen de fondo -->
    <img src="/img/404/404.jpg" alt="404" class="absolute inset-0 w-full h-full object-cover z-0" />

    <!-- Capa oscura (overlay) -->
    <div class="absolute inset-0 bg-black opacity-40 z-10"></div>

    <!-- Contenido centrado -->
    <div class="relative z-20 flex flex-col items-center justify-center h-full text-white text-center p-8">
      <h1 class="text-5xl font-bold">¡Oops! Página no encontrada</h1>
      <p class="mt-4 text-lg">Parece que esta ruta no existe</p>
      <router-link
        to="/"
        class="mt-6 px-6 py-3 bg-[var(--color-primary)] rounded-full hover:bg-[var(--color-secondary)] transition"
      >
        Volver al inicio
      </router-link>
    </div>
  </div>
</template>
