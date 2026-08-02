<template>
  <section class="max-w-4xl mx-auto px-4 py-10 md:py-16">
    <h1 class="text-3xl md:text-4xl font-bold text-[var(--color-primary)] mb-10 text-center">
      Preguntas Frecuentes
    </h1>

    <div v-for="(faq, index) in faqs" :key="index" class="mb-4">
      <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <!-- Pregunta -->
        <button
          @click="toggleFAQ(index)"
          class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none"
          :aria-expanded="faq.open"
          :aria-controls="'faq-' + index"
        >
          <span class="text-base md:text-lg font-medium text-[var(--color-primary)]">
            {{ faq.question }}
          </span>
          <svg
            class="w-5 h-5 text-[var(--color-primary)] transform transition-transform duration-300"
            :class="faq.open ? 'rotate-180' : 'rotate-0'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Respuesta -->
        <transition name="fade-slide">
          <div
            v-show="faq.open"
            :id="'faq-' + index"
            class="px-6 pb-5 pt-5 text-sm text-gray-700 border-t border-gray-100 bg-gray-100]"
          >
            {{ faq.answer }}
          </div>
        </transition>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'FAQs',
  data() {
    return {
      faqs: [
        {
          question: '¿Qué es FitVue?',
          answer: 'FitVue es una plataforma todo-en-uno para entrenamientos, dieta y hábitos saludables.',
          open: false
        },
        {
          question: '¿Cómo creo una cuenta?',
          answer: 'Solo necesitas un correo electrónico y contraseña. Puedes empezar gratis en segundos.',
          open: false
        },
        {
          question: '¿Es gratis el servicio?',
          answer: 'Sí. Ofrecemos un plan gratuito con funcionalidades básicas. También puedes mejorar a Pro.',
          open: false
        },
        {
          question: '¿Puedo cancelar mi suscripción?',
          answer: 'Sí. Puedes cancelar en cualquier momento desde tu perfil sin penalización.',
          open: false
        }
      ]
    }
  },
  methods: {
    toggleFAQ(index) {
      this.faqs[index].open = !this.faqs[index].open
    }
  }
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>