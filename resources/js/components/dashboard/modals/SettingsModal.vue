<script setup>
import { ref, computed } from 'vue'
import { IconCheck, IconChevronRight, IconLogout } from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'
import { usePlan } from '@/composables/usePlan'
import { useTheme } from '@/composables/useTheme'
import ContactModal from '@/components/dashboard/modals/ContactModal.vue'
import AboutModal from '@/components/dashboard/modals/AboutModal.vue'

const userStore = useUserStore()
const { isPro } = usePlan()
const { theme, setTheme } = useTheme()

const show = ref(false)
const contactModal = ref(null)
const aboutModal = ref(null)

function open() { show.value = true }
function close() { show.value = false }
defineExpose({ open, close })

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

function handleLogout() {
  close()
  userStore.logout()
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white dark:bg-[#0f172a] md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-lg md:max-h-[85vh] bg-white dark:bg-[#1e293b] md:rounded-xl shadow-xl flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b dark:border-white/10 pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <span class="w-6"></span>
        <h2 class="font-semibold text-[var(--color-primary)]">Configuración</h2>
        <button type="button" @click="close" class="text-[var(--color-primary)] font-medium">Hecho</button>
      </header>

      <div class="p-4 overflow-y-auto flex-1 space-y-6">
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Suscripción</h3>
          <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-4 flex items-center justify-between gap-3">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Plan actual</p>
              <p class="font-semibold text-gray-800 dark:text-white">{{ planLabel }}</p>
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
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Apariencia</h3>
          <div class="bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl divide-y divide-gray-100 dark:divide-white/10 overflow-hidden">
            <button
              v-for="option in themeOptions"
              :key="option.value"
              type="button"
              @click="setTheme(option.value)"
              class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition"
            >
              <span class="text-sm font-medium text-gray-700 dark:text-gray-100">{{ option.label }}</span>
              <IconCheck v-if="theme === option.value" class="w-4 h-4 text-[var(--color-primary)]" />
            </button>
          </div>
        </div>

        <div>
          <h3 class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Más</h3>
          <div class="bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl divide-y divide-gray-100 dark:divide-white/10 overflow-hidden">
            <button type="button" @click="contactModal.open()" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-100">Contáctanos</span>
              <IconChevronRight class="w-4 h-4 text-gray-400" />
            </button>
            <button type="button" @click="aboutModal.open()" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-100">Acerca de</span>
              <IconChevronRight class="w-4 h-4 text-gray-400" />
            </button>
          </div>
        </div>

        <button
          type="button"
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 text-red-500 font-medium hover:bg-red-50 dark:hover:bg-red-500/10 transition"
        >
          <IconLogout class="w-5 h-5" :stroke-width="2" />
          Cerrar sesión
        </button>
      </div>
    </div>

    <ContactModal ref="contactModal" />
    <AboutModal ref="aboutModal" />
  </div>
</template>
