<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const step = ref(0)
const userData = ref({
  uid: '',
  name: '',
  last_name: '',
  email: '',
  birthday: '',
  role: 'user',
  plan_id: 1,
  height: '',
  weight: '',
  age: '',
  activity: 'sedentario',
  profile_image: '/img/default-profile.svg',
  completedForm: true,
  goal: '',
  gender: ''
})

onMounted(() => {
  const stored = userStore.userData
  if (stored) {
    userData.value = {
      ...userData.value,
      uid: stored.uid || '',
      email: stored.email || '',
      plan_id: stored.plan_id || 1,
      role: stored.role || 'user'
    }
  }

  // Si es coach, solo necesita nombre y apellido
  if (stored?.role === 'coach') {
    step.value = 0
  }
})

const macros = ref({ carbs: 0, proteins: 0, fats: 0 })
const calorieResult = ref(null)

const nextStep = async () => {
  const isCoach = userData.value.plan_id === 3 || userData.value.role === 'coach'

  // Validación común al primer paso
  if (step.value === 0 && (!userData.value.name || !userData.value.last_name)) {
    alert('Por favor, completa este campo antes de continuar.')
    return
  }

  // Si es coach, solo necesitamos nombre y apellidos
  if (isCoach) {
    try {
      await userStore.updateUserData(userStore.userData.uid, {
        name: userData.value.name,
        last_name: userData.value.last_name,
        plan_id: userData.value.plan_id,
        role: 'coach',
        completedForm: true
      })

      await userStore.fetchUserData()
      router.push('/dashboard')
    } catch (error) {
      console.error('❌ Error guardando datos del coach:', error)
    }
    return
  }

  // Si no es coach, sigue flujo normal
  if (
    (step.value === 1 && !userData.value.gender) ||
    (step.value === 2 && !userData.value.goal) ||
    (step.value === 3 && !userData.value.birthday) ||
    (step.value === 4 && !userData.value.height) ||
    (step.value === 5 && !userData.value.weight) ||
    (step.value === 6 && !userData.value.activity)
  ) {
    alert('Por favor, completa este campo antes de continuar.')
    return
  }

  if (step.value < 6) {
    step.value++
  } else {
    calculateCalories()

    try {
      await userStore.updateUserData(userStore.userData.uid, {
        name: userData.value.name,
        last_name: userData.value.last_name,
        birthday: new Date(userData.value.birthday),
        plan_id: userData.value.plan_id,
        height: userData.value.height,
        weight: userData.value.weight,
        age: userData.value.age,
        activity: userData.value.activity,
        role: 'user',
        profile_image: userData.value.profile_image,
        completedForm: true,
        goal: userData.value.goal,
        gender: userData.value.gender
      })

      await userStore.fetchUserData()
      router.push('/dashboard')
    } catch (error) {
      console.error('❌ Error guardando datos:', error)
    }
  }
}


const previousStep = () => {
  if (step.value > 0) step.value--
}

const restart = () => {
  step.value = 0
  calorieResult.value = null
  macros.value = { carbs: 0, proteins: 0, fats: 0 }
  userData.value = {
    name: '',
    last_name: '',
    email: '',
    birthday: '',
    role: 'user',
    plan_id: 1,
    height: '',
    weight: '',
    age: '',
    activity: 'sedentario',
    profile_image: '/img/default-profile.svg',
    completedForm: true,
    goal: '',
    gender: ''
  }
}

const finalizeSetup = async () => {
  if (userStore.userData?.uid) {
    try {
      await userStore.updateUserData(userStore.userData.uid, {
        name: userData.value.name,
        last_name: userData.value.last_name,
        birthday: new Date(userData.value.birthday),
        plan_id: userData.value.plan_id,
        height: userData.value.height,
        weight: userData.value.weight,
        age: userData.value.age,
        activity: userData.value.activity,
        role: userData.value.plan_id === 3 ? 'coach' : 'user',
        profile_image: userData.value.profile_image,
        completedForm: true,
        goal: userData.value.goal,
        gender: userData.value.gender
      })

      await userStore.fetchUserData()
      router.push('/dashboard')
    } catch (error) {
      console.error('Error finalizando el setup:', error)
      alert('Ocurrió un error al guardar tus datos. Por favor, intenta de nuevo.')
    }
  }
}

const selectGoal = (goal) => {
  userData.value.goal = goal
}
const selectActivity = (activity) => {
  userData.value.activity = activity
}
const selectGenre = (gender) => {
  userData.value.gender = gender
}

const calculateCalories = () => {
  // Calcular edad en base a la fecha de nacimiento
  if (userData.value.birthday) {
    const today = new Date()
    const birthDate = new Date(userData.value.birthday)
    let age = today.getFullYear() - birthDate.getFullYear()
    const m = today.getMonth() - birthDate.getMonth()
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--
    }
    userData.value.age = age
  }

  // Resto igual
  const { weight, height, age, activity } = userData.value
  let bmr = 10 * weight + 6.25 * height - 5 * age + 5

  const activityLevels = {
    sedentario: 1.2,
    ligero: 1.375,
    moderado: 1.55,
    activo: 1.725
  }

  const total = Math.round(bmr * activityLevels[activity])
  calorieResult.value = total

  macros.value = {
    carbs: Math.round((total * 0.5) / 4),
    proteins: Math.round((total * 0.2) / 4),
    fats: Math.round((total * 0.3) / 9)
  }

}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-[var(--color-primary)] p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
      <transition name="fade" mode="out-in">
        <div v-if="calorieResult === null">

          <!-- STEP 1 - NOMBRE -->
          <div v-if="step === 0" key="step0">
            <h2 class="text-4xl md:text-5xl font-bold text-center text-[var(--color-primary)] mb-4">Bienvenido a FitVUE</h2>
            <p class="text-center text-gray-600 text-lg md:text-xl mb-6">Empecemos por conocerte un poco mejor para personalizar tu experiencia.</p>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
            <input v-model="userData.name" type="text" placeholder="Tu nombre" class="w-full border px-4 py-2 rounded-lg mb-4" />
            <label class="block text-sm font-semibold text-gray-700 mb-2">Apellidos</label>
            <input v-model="userData.last_name" type="text" placeholder="Tus apellidos" class="w-full border px-4 py-2 rounded-lg" />
          </div>

          <!-- STEP 2 - GÉNERO -->
          <div v-else-if="step === 1" key="step1">
            <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">¿Con qué género te identificas?</h2>
            <p class="text-center text-gray-500 mb-6">
              Este dato nos ayuda a calcular tus necesidades con mayor precisión.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Hombre -->
              <div
                @click="selectGenre('male')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border transition-all duration-200 hover:scale-105',
                  userData.gender === 'male' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-200'
                ]"
              >
                <img src="/img/icons/gender_man.svg" class="mx-auto h-14 mb-4" />
                <p class="text-lg font-semibold text-center text-[var(--color-primary)]">Hombre</p>
              </div>

              <!-- Mujer -->
              <div
                @click="selectGenre('female')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border transition-all duration-200 hover:scale-105',
                  userData.gender === 'female' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-200'
                ]"
              >
                <img src="/img/icons/gender_woman.svg" class="mx-auto h-14 mb-4" />
                <p class="text-lg font-semibold text-center text-[var(--color-primary)]">Mujer</p>
              </div>

              <!-- Otro -->
              <div
                @click="selectGenre('other')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border transition-all duration-200 hover:scale-105',
                  userData.gender === 'other' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-200'
                ]"
              >
                <img src="/img/icons/gender_other.svg" class="mx-auto h-14 mb-4" />
                <p class="text-lg font-semibold text-center text-[var(--color-primary)]">Otro</p>
              </div>
            </div>
          </div>

           <!-- STEP 3 - OBJETIVO -->
          <div v-else-if="step === 2" key="step2">
            <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">¿Cuál es tu objetivo principal?</h2>
            <p class="text-center text-gray-500 mb-6">
              Esto nos permitirá ajustar tus calorías y recomendaciones personales.
            </p>

            <div class="grid grid-cols-1 gap-6">
              <!-- Perder grasa -->
              <div
                @click="selectGoal('perder_grasa')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border flex items-center gap-6 transition-all hover:scale-105',
                  userData.goal === 'perder_grasa' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'
                ]"
              >
                <img src="/img/icons/Perder_grasa.webp" class="h-14 w-auto" />
                <div>
                  <h4 class="text-xl font-bold text-[var(--color-text)]">Perder grasa</h4>
                  <p class="text-gray-500 text-sm">Perder peso y mejorar tu salud metabólica.</p>
                </div>
              </div>

              <!-- Ganar músculo -->
              <div
                @click="selectGoal('ganar_musculo')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border flex items-center gap-6 transition-all hover:scale-105',
                  userData.goal === 'ganar_musculo' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'
                ]"
              >
                <img src="/img/icons/Ganar_musculo.webp" class="h-14 w-auto" />
                <div>
                  <h4 class="text-xl font-bold text-[var(--color-text)]">Ganar músculo</h4>
                  <p class="text-gray-500 text-sm">Aumentar tu masa muscular y tu fuerza física.</p>
                </div>
              </div>

              <!-- Mantener peso -->
              <div
                @click="selectGoal('mantener_peso')"
                :class="[
                  'cursor-pointer p-6 rounded-xl shadow-md border flex items-center gap-6 transition-all hover:scale-105',
                  userData.goal === 'mantener_peso' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'
                ]"
              >
                <img src="/img/icons/Mantener_peso.webp" class="h-14 w-auto" />
                <div>
                  <h4 class="text-xl font-bold text-[var(--color-text)]">Mantener peso</h4>
                  <p class="text-gray-500 text-sm">Estabilizar tu peso y continuar mejorando tu salud.</p>
                </div>
              </div>
            </div>
          </div>

         <!-- STEP 4 - EDAD -->
        <div v-else-if="step === 3" key="step3">
          <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">¿Qué edad tienes?</h2>
          <p class="text-center text-gray-500 mb-6">
            Esto nos ayuda a calcular tu metabolismo basal. Tranquilo, no lo vamos a contar 😉
          </p>

          <div class="flex justify-center">
            <input
              v-model="userData.birthday"
              type="date"
              :max="new Date().toISOString().split('T')[0]"
              class="w-full border p-2 rounded-lg"
            />
          </div>
        </div>

         <!-- STEP 5 - ALTURA -->
        <div v-else-if="step === 4" key="step4">
          <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">¿Cuál es tu altura?</h2>
          <p class="text-center text-gray-500 mb-6">Introduce tu estatura en centímetros para afinar tu cálculo calórico.</p>

          <div class="flex justify-center">
            <div class="relative">
              <input
                v-model.number="userData.height"
                type="number"
                min="100"
                max="250"
                placeholder="Ej. 175"
                class="w-32 text-center text-xl border-2 border-gray-300 focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)] focus:ring-2 transition rounded-lg px-4 py-2"
              />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">cm</span>
            </div>
          </div>
        </div>

          <!-- STEP 6 - PESO -->
          <div v-else-if="step === 5" key="step5">
            <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">¿Cuál es tu peso actual?</h2>
            <p class="text-center text-gray-500 mb-6">Introduce tu peso en kilogramos para estimar correctamente tus necesidades energéticas.</p>

            <div class="flex justify-center">
              <div class="relative">
                <input
                  v-model.number="userData.weight"
                  type="number"
                  min="30"
                  max="250"
                  placeholder="Ej. 68"
                  class="w-32 text-center text-xl border-2 border-gray-300 focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)] focus:ring-2 transition rounded-lg px-4 py-2"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">kg</span>
              </div>
            </div>
          </div>

          <!-- STEP 7 - ACTIVIDAD -->
          <div v-else-if="step === 6" key="step6">
            <h2 class="text-2xl font-bold text-center text-[var(--color-primary)] mb-2">Nivel de actividad física</h2>
            <p class="text-center text-gray-500 mb-6">Selecciona el nivel que más se ajuste a tu rutina semanal.</p>

            <div class="flex flex-col gap-4">
              <!-- Sedentario -->
              <div
                @click="selectActivity('sedentario')"
                :class="userData.activity === 'sedentario' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'"
                class="p-4 border rounded-lg shadow-sm flex items-center gap-4 cursor-pointer transition"
              >
                <img src="/img/icons/ic_low_activity.webp" class="w-10 h-auto" />
                <div>
                  <h4 class="text-lg font-semibold text-[var(--color-text)]">Sedentario</h4>
                  <p class="text-sm text-gray-500">Poca o ninguna actividad física</p>
                </div>
              </div>

              <!-- Ligero -->
              <div
                @click="selectActivity('ligero')"
                :class="userData.activity === 'ligero' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'"
                class="p-4 border rounded-lg shadow-sm flex items-center gap-4 cursor-pointer transition"
              >
                <img src="/img/icons/ic_moderate_activity.webp" class="w-10 h-auto" />
                <div>
                  <h4 class="text-lg font-semibold text-[var(--color-text)]">Ligero</h4>
                  <p class="text-sm text-gray-500">Ejercicio 1-3 veces por semana</p>
                </div>
              </div>

              <!-- Moderado -->
              <div
                @click="selectActivity('moderado')"
                :class="userData.activity === 'moderado' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'"
                class="p-4 border rounded-lg shadow-sm flex items-center gap-4 cursor-pointer transition"
              >
                <img src="/img/icons/ic_high_activity.webp" class="w-10 h-auto" />
                <div>
                  <h4 class="text-lg font-semibold text-[var(--color-text)]">Moderado</h4>
                  <p class="text-sm text-gray-500">Ejercicio 3-5 veces por semana</p>
                </div>
              </div>

              <!-- Activo -->
              <div
                @click="selectActivity('activo')"
                :class="userData.activity === 'activo' ? 'border-[var(--color-primary)] bg-[var(--color-bg-light)]' : 'border-gray-300'"
                class="p-4 border rounded-lg shadow-sm flex items-center gap-4 cursor-pointer transition"
              >
                <img src="/img/icons/ic_athletic_activity.webp" class="w-10 h-auto" />
                <div>
                  <h4 class="text-lg font-semibold text-[var(--color-text)]">Activo</h4>
                  <p class="text-sm text-gray-500">Ejercicio 6-7 veces por semana</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-between mt-6">
            <button v-if="step > 0" @click="previousStep" class="bg-gray-300 px-4 py-2 rounded-lg">
              Atrás
            </button>
            <button @click="nextStep" class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-full">
              {{ step < 7 ? 'Siguiente' : 'Calcular' }}
            </button>
          </div>
                
        </div>

        <div v-else key="result" class="text-center space-y-6">
          <h2 class="text-3xl font-bold text-[var(--color-primary)]">¡Todo listo!</h2>
          <p class="text-gray-600 text-lg">Estas son tus necesidades diarias estimadas:</p>

          <!-- Total Calorías -->
          <div class="text-5xl font-extrabold text-[var(--color-primary)]">
            {{ calorieResult }} kcal / día
          </div>

          <!-- Macronutrientes -->
          <div class="text-left space-y-6 max-w-md mx-auto bg-[var(--color-bg-light)] p-6 rounded-lg shadow-sm">
            <!-- Carbs -->
            <div>
              <div class="flex justify-between font-medium text-[var(--color-primary)] mb-1">
                <span>🥗 Carbohidratos (50%)</span>
                <span>{{ macros.carbs }}g</span>
              </div>
              <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="bg-yellow-400 h-full" style="width: 50%"></div>
              </div>
            </div>

            <!-- Proteins -->
            <div>
              <div class="flex justify-between font-medium text-[var(--color-primary)] mb-1">
                <span>🍗 Proteínas (20%)</span>
                <span>{{ macros.proteins }}g</span>
              </div>
              <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="bg-purple-500 h-full" style="width: 20%"></div>
              </div>
            </div>

            <!-- Fats -->
            <div>
              <div class="flex justify-between font-medium text-[var(--color-primary)] mb-1">
                <span>🥑 Grasas (30%)</span>
                <span>{{ macros.fats }}g</span>
              </div>
              <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="bg-orange-500 h-full" style="width: 30%"></div>
              </div>
            </div>
          </div>

          <p class="text-sm text-gray-500 max-w-md mx-auto">
            Estos valores son una estimación. En el dashboard podrás ajustar tus objetivos y seguir tu progreso diario.
          </p>

          <!-- Acciones -->
          <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
            <button
              @click="finalizeSetup"
              class="bg-[var(--color-primary)] text-white px-6 py-3 rounded-full hover:bg-[var(--color-secondary)] transition"
            >
              Empezar mi cambio
            </button>

            <button
              @click="restart"
              class="bg-gray-200 text-gray-800 px-6 py-3 rounded-full hover:bg-gray-300 transition"
            >
              Reiniciar
            </button>
          </div>
        </div>

      </transition>
    </div>
  </div>
</template>

<style scoped>
.bg-primary {
  background-color: var(--color-primary);
}

.text-primary {
  color: var(--color-primary);
}

.material-icons {
  font-family: 'Material Icons';
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
