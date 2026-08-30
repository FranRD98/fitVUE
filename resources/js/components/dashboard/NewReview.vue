<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'
import { createReview } from '@/api/services/progress'
import { IconArrowLeft } from '@tabler/icons-vue'

const router = useRouter()
const userStore = useUserStore()

const step = ref(0)
const direction = ref('forward')
const inputRef = ref(null)

const userData = ref({
  user_id: '',
  created_at: new Date().toISOString(),
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
  calves: ''
})

function goBack() {
  router.back()
}

onMounted(() => {
  if (userStore.userData?.uid) {
    userData.value.user_id = userStore.userData.uid
  }

  // Espera al DOM y enfoca el input
  nextTick(() => {
    const waitForInput = () => {
      if (inputRef.value) {
        inputRef.value.focus()
      } else {
        requestAnimationFrame(waitForInput)
      }
    }
    waitForInput()
  })
})


watch(step, async () => {
  await nextTick()
  inputRef.value?.focus()
})

const steps = [
  { model: 'weight', label: '¿Cuál es tu peso corporal actual?', image: '/img/icons/reviews/weight.png' },
  { model: 'neck', label: '¿Cuánto mide tu cuello?', image: '/img/icons/reviews/neck.png' },
  { model: 'shoulders', label: '¿Cuánto miden tus hombros?', image: '/img/icons/reviews/shoulders.png' },
  { model: 'chest', label: '¿Cuánto mide tu pecho?', image: '/img/icons/reviews/chest.png' },
  { model: 'biceps_relaxed', label: '¿Cuánto mide tu bíceps relajado?', image: '/img/icons/reviews/biceps-relaxed.png' },
  { model: 'biceps_flexed', label: '¿Y tu bíceps contraído?', image: '/img/icons/reviews/biceps-flexed.png' },
  { model: 'forearm', label: '¿Medida del antebrazo?', image: '/img/icons/reviews/forearm.png' },
  { model: 'wrist', label: '¿Tamaño de la muñeca?', image: '/img/icons/reviews/wrist.png' },
  { model: 'waist', label: '¿Cuánto mide tu cintura?', image: '/img/icons/reviews/waist.png' },
  { model: 'abdomen', label: '¿Medida del abdomen?', image: '/img/icons/reviews/abs.png' },
  { model: 'hips', label: '¿Cuánto mide tu cadera?', image: '/img/icons/reviews/hips.png' },
  { model: 'quadriceps', label: '¿Tamaño de los cuádriceps?', image: '/img/icons/reviews/quad.png' },
  { model: 'calves', label: '¿Y tus gemelos?', image: '/img/icons/reviews/calve.png' }
]

const currentStep = computed(() => steps[step.value])
const totalSteps = computed(() => steps.length)

function handleNext() {
  const val = userData.value[currentStep.value.model]
  if (val === '' || val === null || isNaN(val) || val < 0) {
    alert('Introduce un valor válido antes de continuar.')

    return
  }

  if (step.value < steps.length - 1) {
    direction.value = 'forward'
    step.value++
  }
 else {
    createReview(userData.value)
    router.push({ path: '/dashboard', query: { refresh: 'true' } })  }
}

function handleBack() {
  if (step.value > 0) {
    direction.value = 'backward'
    step.value--
  }
}

watch(step, async () => {
  await nextTick()
  // Espera repetidamente hasta que el input exista y luego hace focus
  const waitForInput = () => {
    if (inputRef.value) {
      inputRef.value.focus()
    } else {
      requestAnimationFrame(waitForInput)
    }
  }
  waitForInput()
})

</script>

<template>
<div class="fixed inset-0 bg-[var(--color-primary)] bg-opacity-60 backdrop-blur-sm flex items-start justify-center overflow-y-auto py-6 px-4 z-50">
<div class="bg-white rounded-xl shadow-xl w-full max-w-[90vw] sm:max-w-xl md:max-w-2xl max-h-[90vh] h-auto flex flex-col relative overflow-hidden">
            <!-- Volver atrás -->
    <button
      @click="goBack"
      class="flex items-center gap-2 text-sm text-[var(--color-primary)] hover:underline mt-4 ml-4"
    >
      <IconArrowLeft class="w-4 h-4" />
      Volver
    </button>
      <!-- Encabezado -->
      <div class="text-center py-6 border-b">
        <h2 class="text-xl font-bold text-[var(--color-primary)]">Paso {{ step + 1 }} de {{ totalSteps }}</h2>

        <!-- Barra de progreso -->
        <div class="w-full px-6 mt-2">
          <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-[var(--color-primary)] transition-all duration-300 ease-in-out"
              :style="{ width: `${((step + 1) / totalSteps) * 100}%` }"
            ></div>
          </div>
        </div>

      </div>

      <!-- Contenido -->
      <transition name="fade" mode="out-in">
        <div :key="step" class="flex-1 relative flex flex-col items-center justify-center text-center p-6 gap-6">
          <h3 class="text-xl font-semibold text-gray-700">{{ currentStep.label }}</h3>

          <img
            :src="currentStep.image"
            alt="Imagen del paso"
            class="w-full max-w-xs sm:max-w-sm h-40 sm:h-56 object-contain"
            loading="lazy"
          />

          <input
            ref="inputRef"
            v-model.number="userData[currentStep.model]"
            type="number"
            class="input mt-4 text-center w-full max-w-xs"
            placeholder="Introduce el valor en cm"
            required
            @keyup.enter="handleNext"
          />
        </div>
      </transition>

      <!-- Navegación -->
      <div class="p-6 flex justify-between items-center border-t">
        <button
          v-if="step > 0"
          @click="handleBack"
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700"
        >
          Atrás
        </button>

        <button
          @click="handleNext"
          class="ml-auto px-6 py-2 bg-[var(--color-primary)] text-white rounded-lg hover:bg-[var(--color-secondary)]"
        >
          {{ step === totalSteps - 1 ? 'Finalizar' : 'Siguiente' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  max-width: 300px;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  line-height: 1.5rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.input:hover {
  border-color: #9ca3af; /* gris medio */
}

</style>
