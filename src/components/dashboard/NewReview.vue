<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'
import { createReview } from '@/supabase/services/progress'

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

onMounted(() => {
  if (userStore.userData?.uid) {
    userData.value.user_id = userStore.userData.uid
  }
})

watch(step, async () => {
  await nextTick()
  inputRef.value?.focus()
})

const steps = [
  { model: 'weight', label: '¿Cuál es tu peso corporal actual?', image: 'https://images.pexels.com/photos/6975463/pexels-photo-6975463.jpeg?auto=compress&cs=tinysrgb&w=800' },
  { model: 'neck', label: '¿Cuánto mide tu cuello?', image: '/images/neck.png' },
  { model: 'shoulders', label: '¿Cuánto miden tus hombros?', image: '/images/shoulders.png' },
  { model: 'chest', label: '¿Cuánto mide tu pecho?', image: '/images/chest.png' },
  { model: 'biceps_relaxed', label: '¿Cuánto mide tu bíceps relajado?', image: '/images/biceps_relaxed.png' },
  { model: 'biceps_flexed', label: '¿Y tu bíceps contraído?', image: '/images/biceps_flexed.png' },
  { model: 'forearm', label: '¿Medida del antebrazo?', image: '/images/forearm.png' },
  { model: 'wrist', label: '¿Tamaño de la muñeca?', image: '/images/wrist.png' },
  { model: 'waist', label: '¿Cuánto mide tu cintura?', image: '/images/waist.png' },
  { model: 'abdomen', label: '¿Medida del abdomen?', image: '/images/abdomen.png' },
  { model: 'hips', label: '¿Cuánto mide tu cadera?', image: '/images/hips.png' },
  { model: 'quadriceps', label: '¿Tamaño de los cuádriceps?', image: '/images/quadriceps.png' },
  { model: 'calves', label: '¿Y tus gemelos?', image: '/images/calves.png' }
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
    router.push('/dashboard')
  }
}

function handleBack() {
  if (step.value > 0) {
    direction.value = 'backward'
    step.value--
  }
}
</script>

<template>
<div class="fixed inset-0 bg-[var(--color-primary)] bg-opacity-60 backdrop-blur-sm flex items-start justify-center overflow-y-auto py-6 z-50">
<div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] h-auto flex flex-col relative overflow-hidden">
      
      <!-- Encabezado -->
      <div class="text-center py-6 border-b">
        <h2 class="text-xl font-bold text-[var(--color-primary)]">Paso {{ step + 1 }} de {{ totalSteps }}</h2>

        <!-- Barra de progreso -->
        <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden mt-2 mx-6">
          <div
            class="absolute top-0 left-0 h-full bg-[var(--color-primary)] transition-all duration-500"
            :style="{ width: ((step + 1) / totalSteps * 100) + '%' }"
          ></div>
        </div>
      </div>

      <!-- Contenido -->
      <transition :name="direction === 'forward' ? 'slide-left' : 'slide-right'" mode="out-in">
        <div :key="step" class="flex-1 relative flex flex-col items-center justify-center text-center p-6 gap-6">
          <h3 class="text-xl font-semibold text-gray-700">{{ currentStep.label }}</h3>

          <img
            :src="currentStep.image"
            alt="Imagen del paso"
            class="w-full max-w-sm h-64 object-cover rounded-xl shadow-md"
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
  transition: border-color 0.2s ease;
}

.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
}

.slide-left-enter-active, .slide-left-leave-active,
.slide-right-enter-active, .slide-right-leave-active {
  transition: transform 0.4s ease, opacity 0.4s ease;
  position: absolute;
  width: 100%;
}

.slide-left-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}
.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
