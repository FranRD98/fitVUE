<script>

</script>

<template>
  <section class="relative w-full h-[80vh] text-white bg-[var(--color-primary)] md:bg-transparent flex flex-col items-center justify-center text-center overflow-hidden">

    <!-- Video de fondo -->
    <video
      autoplay loop muted playsinline
      class="hidden md:block absolute top-0 left-0 w-full h-full object-cover z-0"
    >
      <source src="/Video/videoBanner.mp4" type="video/mp4" />
    </video>

    <!-- Imagen de fondo en móvil -->
    <div
      class="block md:hidden absolute top-0 left-0 w-full h-full bg-cover bg-center z-0"
      style="background-image: url('/Video/videoBanner.jpg')"
    ></div>
    <!-- Capa oscura con transparencia -->
    <div class="hidden md:block absolute inset-0 bg-black/20 z-10"></div>

    <!-- Contenido principal -->
    <div class="flex flex-col items-center z-20 px-4">
      <h2 class="text-4xl md:text-6xl font-bold">
        Desbloquea tu potencial con #fitVUE
      </h2>
      <p class="mt-4 text-lg md:text-xl">
        La herramienta definitiva para entrenamientos, nutrición y bienestar
      </p>

      <router-link to="sign-in"
      class="mt-8 bg-white md:bg-[var(--color-primary)] w-3/4 sm:w-1/2 md:w-1/4 px-6 py-3 rounded-full text-[var(--color-primary)] md:text-white text-lg font-semibold transition duration-300 hover:bg-white hover:text-[var(--color-primary)]">
        Empezar gratis
      </router-link>
    </div>

  </section>
</template>
