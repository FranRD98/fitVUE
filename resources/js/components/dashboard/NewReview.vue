<script setup>
import { ref, computed } from 'vue'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'
import { createReview } from '@/api/services/progress'
import { IconArrowLeft, IconWeight, IconRuler2, IconCheck } from '@tabler/icons-vue'

const router = useRouter()
const userStore = useUserStore()

const saving = ref(false)
const error = ref('')

const fields = ref({
  weight: '',
  neck: '',
  shoulders: '',
  chest: '',
  biceps_relaxed: '',
  biceps_flexed: '',
  forearm: '',
  wrist: '',
  waist: '',
  abdomen: '',
  hips: '',
  quadriceps: '',
  calves: '',
})

// Un único formulario con todos los campos agrupados: nada de pasos ni
// autofocus saltando de un input a otro (eso, combinado con el teclado del
// móvil, era lo que desplazaba todo el contenido). Todas las medidas son
// opcionales, igual que ya asumía el histórico (MeasurementsPanel.vue).
const sections = [
  {
    title: 'Peso',
    icon: IconWeight,
    fields: [{ model: 'weight', label: 'Peso corporal', unit: 'kg' }],
  },
  {
    title: 'Torso',
    icon: IconRuler2,
    fields: [
      { model: 'neck', label: 'Cuello', unit: 'cm' },
      { model: 'shoulders', label: 'Hombros', unit: 'cm' },
      { model: 'chest', label: 'Pecho', unit: 'cm' },
    ],
  },
  {
    title: 'Brazos',
    icon: IconRuler2,
    fields: [
      { model: 'biceps_relaxed', label: 'Bíceps (relajado)', unit: 'cm' },
      { model: 'biceps_flexed', label: 'Bíceps (flexionado)', unit: 'cm' },
      { model: 'forearm', label: 'Antebrazo', unit: 'cm' },
      { model: 'wrist', label: 'Muñeca', unit: 'cm' },
    ],
  },
  {
    title: 'Core',
    icon: IconRuler2,
    fields: [
      { model: 'waist', label: 'Cintura', unit: 'cm' },
      { model: 'abdomen', label: 'Abdomen', unit: 'cm' },
      { model: 'hips', label: 'Cadera', unit: 'cm' },
    ],
  },
  {
    title: 'Piernas',
    icon: IconRuler2,
    fields: [
      { model: 'quadriceps', label: 'Cuádriceps', unit: 'cm' },
      { model: 'calves', label: 'Gemelos', unit: 'cm' },
    ],
  },
]

const filledCount = computed(() =>
  Object.values(fields.value).filter(v => v !== '' && v !== null).length
)

function goBack() {
  router.back()
}

async function handleSave() {
  error.value = ''

  const hasInvalid = Object.values(fields.value).some(v => v !== '' && (isNaN(v) || v < 0))
  if (hasInvalid) {
    error.value = 'Alguna medida no es un número válido.'
    return
  }

  if (filledCount.value === 0) {
    error.value = 'Introduce al menos una medida.'
    return
  }

  saving.value = true
  try {
    const payload = { user_id: userStore.userData?.uid }
    for (const [key, value] of Object.entries(fields.value)) {
      payload[key] = value === '' ? null : value
    }

    await createReview(payload)
    router.push({ path: '/dashboard', query: { refresh: 'true' } })
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo guardar la medición. Inténtalo de nuevo.'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-gray-50 z-50 flex flex-col">
    <!-- Cabecera -->
    <header class="bg-white border-b shrink-0 pt-[calc(env(safe-area-inset-top)+0.75rem)] pb-3 px-4 flex items-center gap-3">
      <button type="button" @click="goBack" class="text-gray-500 hover:text-gray-700 shrink-0" aria-label="Volver">
        <IconArrowLeft class="w-5 h-5" />
      </button>
      <div class="flex-1 min-w-0">
        <h1 class="font-bold text-[var(--color-primary)] truncate">Nueva medición</h1>
        <p class="text-xs text-gray-400">Rellena solo lo que hayas medido hoy.</p>
      </div>
    </header>

    <!-- Formulario -->
    <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-4 space-y-4">
      <div v-for="section in sections" :key="section.title" class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center gap-2">
          <component :is="section.icon" class="w-4 h-4 text-[var(--color-primary)]" />
          <h2 class="text-sm font-semibold text-gray-700">{{ section.title }}</h2>
        </div>

        <div class="p-4 grid grid-cols-2 gap-3">
          <div v-for="field in section.fields" :key="field.model" :class="section.fields.length === 1 ? 'col-span-2' : ''">
            <label class="block text-xs text-gray-500 mb-1">{{ field.label }}</label>
            <div class="relative">
              <input
                v-model.number="fields[field.model]"
                type="number"
                inputmode="decimal"
                min="0"
                step="0.1"
                placeholder="0"
                class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)]"
              />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">{{ field.unit }}</span>
            </div>
          </div>
        </div>
      </div>

      <p v-if="error" class="text-sm text-red-600 text-center">{{ error }}</p>
      <div class="h-2"></div>
    </div>

    <!-- Guardar -->
    <div class="shrink-0 border-t bg-white px-4 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)]">
      <button
        type="button"
        @click="handleSave"
        :disabled="saving"
        class="w-full flex items-center justify-center gap-2 bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition"
      >
        <IconCheck class="w-5 h-5" />
        {{ saving ? 'Guardando...' : 'Guardar medición' }}
      </button>
    </div>
  </div>
</template>
