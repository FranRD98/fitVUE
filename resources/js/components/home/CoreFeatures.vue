<template>
  <section class="max-w-7xl mx-auto px-6 py-20 gap-2 bg-white text-center flex flex-col items-center">
    <h2 class="text-4xl md:text-5xl font-bold text-[var(--color-primary)] mb-4">Lo que puedes hacer con FitVue</h2>
    <p class="text-lg md:text-xl text-gray-600 mb-12 mx-auto">
      Controla tu progreso, planifica tu rutina, cuida tu alimentación y mucho más desde una sola plataforma.
    </p>

    <!-- Grid principal -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-10 w-full max-w-6xl mx-auto px-4">
      <div
        v-for="(card, i) in cards"
        :key="i"
        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition group"
      >
        <!-- Imagen con efecto zoom -->
        <div class="overflow-hidden h-64">
          <img
            :src="card.image"
            :alt="card.title"
            class="w-full h-full object-cover transform transition-transform duration-300 group-hover:scale-110"
          />
        </div>

        <!-- Contenido -->
        <div class="p-6 text-left">
          <h3 class="text-xl font-semibold text-[var(--color-primary)] mb-2">{{ card.title }}</h3>
          <p class="text-lg text-gray-600 text-pretty">{{ card.description }}</p>
        </div>
      </div>
    </div>

    <!-- Mensaje intermedio -->
    <div class="mt-24 text-center">
      <h3 class="text-4xl md:text-5xl font-bold text-[var(--color-primary)] mb-4">¿Y si te decimos que hay más?</h3>
      <p class="text-lg md:text-xl text-gray-600 mb-8">
        Con nuestros planes <strong>Avanzado</strong> y <strong>Profesional</strong>, desbloqueas todo el poder de FitVue.
      </p>
    </div>

    <!-- Card extra a pantalla completa -->
    <div class="w-full max-w-6xl bg-[var(--color-primary)] text-white rounded-xl shadow-lg mt-12 overflow-hidden flex flex-col md:flex-row">
      <img
        src="https://placehold.co/112x112/f3f4f6/005F78?text=Premium"
        alt="Entrenador Personal"
        class="w-full md:w-1/2 object-cover h-64 md:h-auto"
      />
      <div class="p-8 flex flex-col justify-center md:w-1/2 text-left">
        <h4 class="text-3xl md:text-4xl font-bold mb-8">Tu entrenador personal, sin límites</h4>
        <p class="text-lg text-pretty mb-4">
          Con el plan <strong>Avanzado</strong>, podrás tener rutinas y dietas asignadas directamente por un entrenador o coach. Ajustadas a tus objetivos, con seguimiento en tiempo real.
        </p>
        <p class="text-lg">
          ¿Eres profesional del fitness? El plan <strong>Profesional</strong> te permite crear una red de clientes, asignar rutinas y dietas, y llevar un seguimiento de su evolución como si tuvieras tu propio software de entrenamiento personalizado.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
const cards = [
  {
    image: '/rutina.jpg',
    title: 'Gestión de rutinas',
    description: 'Crea y organiza rutinas personalizadas para cada día. Ajusta ejercicios, series y repeticiones según tu objetivo.'
  },
  {
    image: '/dieta.jpg',
    title: 'Dietas Personalizadas',
    description: 'Asigna guías y hábitos alimenticios adaptados a tus necesidades y estilo de vida. Mejora tu nutrición día a día.'
  },
  {
    image: '/monitoreo.jpg',
    title: 'Monitoreo de Progreso',
    description: 'Visualiza tu avance físico y mental. Anota tus sensaciones, carga levantada y constancia semanal.'
  },
  {
    image: '/trainer.jpg',
    title: 'Métricas y estadísticas',
    description: 'Accede a gráficos y estadísticas claras sobre tu entrenamiento. Detecta patrones y toma decisiones inteligentes.'
  }
]
</script>
