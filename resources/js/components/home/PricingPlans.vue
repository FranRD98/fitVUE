<script setup>
import { useRouter } from 'vue-router'
import PlanCard from '@/components/home/PlanCard.vue'

const router = useRouter()

/* Function to handle de plan saved in local storage */
function handlePlanSelect(planId) {
  localStorage.setItem('selectedPlan', planId)
  router.push('/sign-in')
}

const plans = [
  {
    title: 'Plan Gratuito',
    subtitle: 'Ideal para empezar tu cambio',
    price: '0€/mes',
    highlighted: false,
    buttonText: 'Empieza gratis',
    planId: 1,
    features: [
      // Funciones personales
      { label: 'Crea tus propias rutinas de entrenamiento', active: true },
      { label: 'Planifica tus comidas y dietas personales', active: true },
      { label: 'Herramientas de seguimiento y métricas', active: true },
      { label: 'Visualiza tu progreso en tiempo real', active: true },

      // Comunidad y acompañamiento
      { label: 'Acceso a la comunidad FitVue', active: false },
      { label: 'Acompañamiento profesional', active: false },
      { label: 'Rutinas y dietas asignadas por expertos', active: false },
      { label: 'Atención personalizada', active: false },

      // Modo entrenador
      { label: 'Gestión de clientes (modo entrenador)', active: false }
    ]
  },
  {
    title: 'Plan Premium',
    subtitle: 'Acompañamiento por expertos',
    price: '39,99€/mes',
    highlighted: true,
    buttonText: 'Quiero acompañamiento',
    planId: 2,
    features: [
      { label: 'Crea tus propias rutinas de entrenamiento', active: true },
      { label: 'Planifica tus comidas y dietas personales', active: true },
      { label: 'Herramientas de seguimiento y métricas', active: true },
      { label: 'Visualiza tu progreso en tiempo real', active: true },

      { label: 'Acceso a la comunidad FitVue', active: true },
      { label: 'Acompañamiento profesional', active: true },
      { label: 'Rutinas y dietas asignadas por expertos', active: true },
      { label: 'Atención personalizada', active: true },

      { label: 'Gestión de clientes (modo entrenador)', active: false }
    ]
  },
  {
    title: 'Plan Profesional',
    subtitle: 'Diseñado para entrenadores',
    price: '119,99€/mes',
    highlighted: false,
    buttonText: 'Soy entrenador',
    planId: 3,
    features: [
      { label: 'Crea rutinas de entrenamiento para tus clientes', active: true },
      { label: 'Planifica comidas y dietas para tus clientes', active: true },
      { label: 'Herramientas de seguimiento y métricas', active: true },
      { label: 'Visualiza el progreso de tus clientes', active: true },

      { label: 'Acceso a la comunidad FitVue', active: true },
      { label: 'Panel de control profesional', active: true },
      { label: 'Seguimiento avanzado por cliente', active: true },
      { label: 'Acceso a herramientas exclusivas de coaches', active: true },
      { label: 'Asesoría técnica y soporte prioritario', active: true }
    ]
  }
]
</script>

<template>
  <section class="bg-[#f3f6f8] py-20 px-6">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="text-4xl md:text-5xl font-bold text-[var(--color-primary)] mb-6">Nuestros Planes</h2>
      <p class="text-lg md:text-xl text-gray-600 mb-14 max-w-3xl mx-auto">
        Elige el plan que mejor se adapte a tu etapa y empieza tu transformación con FitVue.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <PlanCard
          v-for="(plan, i) in plans"
          :key="i"
          v-bind="plan"
          @select="handlePlanSelect"
        />
      </div>
    </div>
  </section>
</template>
