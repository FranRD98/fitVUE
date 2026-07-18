<script setup>
import { useRoute, useRouter } from 'vue-router'
import { onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import api from '@/supabase/config'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

onMounted(async () => {
  const sessionId = route.query.session_id

  if (!sessionId) {
    // El enlace de pago de Stripe no incluye el session_id: no podemos confirmar el pago de forma segura.
    console.error('Falta session_id: configura el "after payment redirect" del Payment Link con {CHECKOUT_SESSION_ID}.')
    return router.push('/dashboard')
  }

  try {
    // El backend verifica el pago contra la API de Stripe antes de aplicar el plan.
    await api.post('/stripe/confirm', { session_id: sessionId })
  } catch (error) {
    console.error('❌ Error confirmando el pago:', error)
    return router.push('/dashboard')
  }

  await userStore.fetchUserData()

  if (userStore.userData?.completed_form) {
    router.push('/dashboard')
  } else {
    router.push('/empezar')
  }
})


</script>

<template>
  <div class="text-center p-10">
    <h2 class="text-2xl font-bold text-[var(--color-primary)]">Procesando tu suscripción...</h2>
    <p class="mt-4 text-gray-600">Estamos configurando tu cuenta.</p>
  </div>
</template>