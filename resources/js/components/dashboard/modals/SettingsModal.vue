<script setup>
import { computed } from 'vue'
import { IconCheck } from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'
import { usePlan } from '@/composables/usePlan'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close'])

const userStore = useUserStore()
const { isPro } = usePlan()
const { theme, setTheme } = useTheme()

const planLabel = computed(() => {
  switch (userStore.userData?.plan_id) {
    case 2: return 'Premium'
    case 3: return 'Pro'
    default: return 'Free'
  }
})

// El client_reference_id permite al webhook de Stripe identificar a qué usuario aplicar el plan tras el pago.
const upgradeUrl = computed(() => {
  const uid = userStore.userData?.uid
  return uid
    ? `https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00?client_reference_id=${uid}`
    : 'https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00'
})

const themeOptions = [
  { value: 'light', label: 'Claro' },
  { value: 'dark', label: 'Oscuro' },
  { value: 'system', label: 'Dispositivo' },
]
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-lg md:max-h-[85vh] bg-white md:rounded-xl shadow-xl flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <span class="w-6"></span>
        <h2 class="font-semibold text-[var(--color-primary)]">Configuración</h2>
        <button type="button" @click="emit('close')" class="text-[var(--color-primary)] font-medium">Hecho</button>
      </header>

      <div class="p-4 overflow-y-auto flex-1 space-y-6">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Suscripción</h3>
          <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center justify-between gap-3">
            <div>
              <p class="text-sm text-gray-500">Plan actual</p>
              <p class="font-semibold text-gray-800">{{ planLabel }}</p>
            </div>
            <a
              v-if="!isPro"
              :href="upgradeUrl"
              class="bg-[var(--color-primary)] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-[var(--color-secondary)] transition"
            >
              Actualizar plan
            </a>
          </div>
        </div>

        <div>
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Apariencia</h3>
          <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden">
            <button
              v-for="option in themeOptions"
              :key="option.value"
              type="button"
              @click="setTheme(option.value)"
              class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition"
            >
              <span class="text-sm font-medium text-gray-700">{{ option.label }}</span>
              <IconCheck v-if="theme === option.value" class="w-4 h-4 text-[var(--color-primary)]" />
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-2">
            El ajuste ya se guarda; el rediseño visual completo del modo oscuro para todas las pantallas llegará en una actualización aparte.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
