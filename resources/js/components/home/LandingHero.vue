<script setup>
</script>

<template>
  <section class="relative w-full h-dvh min-h-[480px] text-white bg-[var(--color-primary)] flex flex-col items-center justify-center text-center overflow-hidden">

    <!-- Video de fondo (también en móvil; poster evita el hueco en blanco mientras carga) -->
    <video
      autoplay loop muted playsinline
      poster="/Video/videoBanner.jpg"
      class="absolute top-0 left-0 w-full h-full object-cover z-0"
    >
      <source src="/Video/videoBanner.mp4" type="video/mp4" />
    </video>

    <!-- Capa oscura con transparencia -->
    <div class="absolute inset-0 bg-black/30 z-10"></div>

    <!-- Blobs decorativos animados, puramente visuales -->
    <div aria-hidden="true" class="blob-float absolute top-1/4 -left-16 w-72 h-72 rounded-full bg-white/10 blur-3xl z-10"></div>
    <div aria-hidden="true" class="blob-float blob-float-delay absolute bottom-0 -right-10 w-80 h-80 rounded-full bg-white/10 blur-3xl z-10"></div>

    <!-- Contenido principal -->
    <div class="hero-in flex flex-col items-center z-20 px-4">
      <h2 class="text-4xl md:text-6xl font-bold">
        Desbloquea tu potencial con #fitVUE
      </h2>
      <p class="mt-4 text-lg md:text-xl">
        La herramienta definitiva para entrenamientos, nutrición y bienestar
      </p>

      <router-link
        to="sign-in"
        class="btn-fill btn-fill--outline-light mt-8 px-8 py-3 rounded-full text-lg font-semibold"
      >
        <span class="btn-fill__label">Empezar gratis</span>
        <span class="btn-fill__sweep" aria-hidden="true">
          <span class="btn-fill__label">Empezar gratis</span>
        </span>
      </router-link>
    </div>

    <!-- Indicador de scroll -->
    <div aria-hidden="true" class="scroll-cue absolute bottom-8 left-1/2 -translate-x-1/2 z-20">
      <svg width="24" height="36" viewBox="0 0 24 36" fill="none">
        <rect x="1" y="1" width="22" height="34" rx="11" stroke="white" stroke-opacity="0.6" stroke-width="2" />
        <circle class="scroll-cue__dot" cx="12" cy="10" r="3" fill="white" />
      </svg>
    </div>
  </section>
</template>

<style scoped>
.hero-in {
  animation: hero-in 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes hero-in {
  from { opacity: 0; transform: translateY(18px); }
  to { opacity: 1; transform: none; }
}

.scroll-cue__dot {
  animation: scroll-cue-dot 1.6s ease-in-out infinite;
}
@keyframes scroll-cue-dot {
  0% { transform: translateY(0); opacity: 1; }
  60% { transform: translateY(12px); opacity: 0; }
  61% { transform: translateY(0); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
  .hero-in {
    animation: none;
  }
  .scroll-cue__dot {
    animation: none;
  }
}
</style>
