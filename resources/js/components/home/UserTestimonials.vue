<script setup>
const testimonios = [
  {
    nombre: "María López",
    rol: "Nutricionista",
    comentario: "FitVue ha transformado cómo planifico las dietas de mis pacientes. Me permite hacer un seguimiento real y efectivo.",
    foto: "https://randomuser.me/api/portraits/women/68.jpg"
  },
  {
    nombre: "Carlos Pérez",
    rol: "Entrenador Personal",
    comentario: "Desde que uso FitVue con mis clientes, todo es más profesional. Me ahorro horas de trabajo y mis clientes lo notan.",
    foto: "https://randomuser.me/api/portraits/men/52.jpg"
  },
  {
    nombre: "Ana Torres",
    rol: "Fisioterapeuta",
    comentario: "Me encanta cómo puedo integrar el progreso físico con rutinas personalizadas. Súper intuitiva y visual.",
    foto: "https://randomuser.me/api/portraits/women/45.jpg"
  },
  {
    nombre: "Brian Betancourth",
    rol: "Usuario Avanzado",
    comentario: "Antes usaba Excel. Ahora con FitVue no solo hago seguimiento, sino que disfruto organizando mis rutinas.",
    foto: "https://randomuser.me/api/portraits/men/36.jpg"
  },
  {
    nombre: "Lucía Navarro",
    rol: "Coach de Bienestar",
    comentario: "Un sistema completo. Desde rutinas, dietas hasta métricas. ¡FitVue es todo lo que necesitaba para mis clientes!",
    foto: "https://randomuser.me/api/portraits/women/29.jpg"
  },
  {
    nombre: "Lernik Gasparyan",
    rol: "Preparador Físico",
    comentario: "La app perfecta si trabajas con muchas personas. Ahorra tiempo, profesionaliza tu servicio y mejora la comunicación.",
    foto: "https://randomuser.me/api/portraits/men/77.jpg"
  }
]
</script>

<template>
  <section class="py-24 px-6 bg-white">
    <div class="text-center mb-14 max-w-4xl mx-auto">
      <h2 class="text-4xl md:text-5xl font-bold text-[var(--color-primary)]">Lo que dicen sobre FitVue</h2>
      <p class="text-gray-600 text-lg md:text-xl mt-3">
        Confianza, resultados y apoyo real. Estas son algunas de las voces que ya han experimentado el cambio con FitVue.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
      <div
        v-for="(t, i) in testimonios"
        :key="i"
        :class="[
          'bg-white rounded-2xl shadow-lg p-6 border transition-all duration-300',
          i >= 6 ? 'fade-out-mask' : '',
          i === testimonios.length - 1 ? 'mobile-only-fade' : ''
        ]"
      >
        <!-- Estrellas -->
        <div class="text-yellow-500 text-lg mb-4">
          <span v-for="n in 5" :key="n">★</span>
        </div>

        <!-- Comentario -->
        <p class="text-gray-600 text-base leading-relaxed line-clamp-5 mb-6">
          {{ t.comentario }}
        </p>

        <!-- Usuario -->
        <div class="flex items-center gap-3 mt-auto">
          <img :src="t.foto" alt="foto" class="w-10 h-10 rounded-full object-cover border" />
          <div>
            <p class="text-[var(--color-text)]">{{ t.nombre }}</p>
            <span class="text-sm font-semibold text-[var(--color-primary)]">{{ t.rol }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.fade-out-mask {
  mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
  -webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
}
.line-clamp-5 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
