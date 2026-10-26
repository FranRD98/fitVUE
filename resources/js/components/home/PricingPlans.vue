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
    subtitle: 'Para empezar a entrenar por tu cuenta',
    price: '0€/mes',
    highlighted: false,
    buttonText: 'Empieza gratis',
    planId: 1,
    features: [
      { label: 'Hasta 3 rutinas propias', active: true },
      { label: 'Historial, calendario y récords', active: true },
      { label: 'Medidas corporales', active: true },
      { label: 'Dietas, platos e ingredientes', active: false },
      { label: 'Rutinas ilimitadas', active: false },
      { label: 'Dieta asignada por tu coach', active: false },
      { label: 'Panel de clientes', active: false }
    ]
  },
  {
    title: 'Plan Premium',
    subtitle: 'Para entrenar sin límite, con tu coach',
    price: '39,99€/mes',
    highlighted: true,
    buttonText: 'Quiero Premium',
    planId: 2,
    features: [
      { label: 'Rutinas ilimitadas', active: true },
      { label: 'Dietas, platos e ingredientes', active: true },
      { label: 'Historial, calendario y récords', active: true },
      { label: 'Medidas corporales', active: true },
      { label: 'Dieta asignada por tu coach', active: true },
      { label: 'Panel de clientes', active: false }
    ]
  },
  {
    title: 'Plan Profesional',
    subtitle: 'Para entrenadores y sus clientes',
    price: '119,99€/mes',
    highlighted: false,
    buttonText: 'Soy entrenador',
    planId: 3,
    features: [
      { label: 'Rutinas ilimitadas', active: true },
      { label: 'Dietas para ti y tus clientes', active: true },
      { label: 'Historial, calendario y récords', active: true },
      { label: 'Panel de clientes', active: true },
      { label: 'Asigna la dieta de cada cliente', active: true }
    ]
  }
]
</script>

<template>
  <section class="bg-[#f3f6f8] py-20 px-6">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="text-4xl md:text-5xl font-bold text-[var(--color-primary)] mb-6">Nuestros Planes</h2>
      <p class="text-lg md:text-xl text-gray-600 mb-14 max-w-3xl mx-auto">
        Empieza gratis con 3 rutinas. Premium quita el límite y abre dietas, platos e ingredientes. Profesional es el plan de quien entrena a otras personas.
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
