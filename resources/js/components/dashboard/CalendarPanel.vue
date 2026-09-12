<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { getTrainingCalendar } from '@/api/services/exercises'
import { useDelayedSkeleton } from '@/composables/useDelayedSkeleton'
import { IconChevronLeft, IconChevronRight, IconFlame } from '@tabler/icons-vue'

const userStore = useUserStore()
const { loading, showSkeleton, start, finish } = useDelayedSkeleton(200)

const trainedByDate = ref({}) // { 'YYYY-MM-DD': ['Pierna 1', ...] }
const cursor = ref(new Date()) // mes que se está viendo
const selectedDate = ref(null)

const MONTH_NAMES = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]
const WEEKDAY_LABELS = ['L', 'M', 'X', 'J', 'V', 'S', 'D']

// OJO: no usar toISOString() aquí — convierte a UTC y en zonas horarias
// adelantadas a UTC (como España) desplaza la fecha un día hacia atrás.
function toKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const monthLabel = computed(() => `${MONTH_NAMES[cursor.value.getMonth()]} ${cursor.value.getFullYear()}`)

const calendarDays = computed(() => {
  const year = cursor.value.getFullYear()
  const month = cursor.value.getMonth()
  const firstDay = new Date(year, month, 1)
  // Lunes = 0 ... Domingo = 6
  const leadingBlanks = (firstDay.getDay() + 6) % 7
  const daysInMonth = new Date(year, month + 1, 0).getDate()

  const days = []
  for (let i = 0; i < leadingBlanks; i++) days.push(null)
  for (let d = 1; d <= daysInMonth; d++) days.push(new Date(year, month, d))
  return days
})

function isToday(date) {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

function isFuture(date) {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return date > today
}

function trainedOn(date) {
  return trainedByDate.value[toKey(date)] || null
}

function selectDay(date) {
  if (!date || isFuture(date)) return
  selectedDate.value = date
}

function changeMonth(delta) {
  cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + delta, 1)
  selectedDate.value = null
}

// Racha actual: días consecutivos entrenados contando hacia atrás desde hoy
// (se permite que "hoy" aún no tenga entrenamiento sin romper la racha).
const currentStreak = computed(() => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  let streak = 0
  let cur = new Date(today)

  if (!trainedOn(cur)) {
    cur.setDate(cur.getDate() - 1)
  }

  while (trainedOn(cur)) {
    streak++
    cur.setDate(cur.getDate() - 1)
  }

  return streak
})

const trainedDaysCount = computed(() => Object.keys(trainedByDate.value).length)

const selectedDateLabel = computed(() => {
  if (!selectedDate.value) return null
  return selectedDate.value.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })
})
const selectedDateRoutines = computed(() => selectedDate.value ? trainedOn(selectedDate.value) : null)

onMounted(async () => {
  start()
  try {
    const data = await getTrainingCalendar(userStore.userData?.uid)
    trainedByDate.value = Object.fromEntries(data.map(d => [d.date, d.routines]))
  } finally {
    finish()
  }
})
</script>

<template>
  <section>
    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-primary)] mb-1">Calendario</h1>
    <p class="text-sm text-gray-500 mb-6">Tus días de entrenamiento y descanso.</p>

    <div v-if="loading && !showSkeleton" />
    <div v-else-if="loading && showSkeleton" class="bg-gray-100 h-96 rounded-xl animate-pulse" />

    <div v-else class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-100 md:bg-white shadow rounded-xl p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
            <IconFlame class="w-6 h-6 text-orange-500" />
          </div>
          <div>
            <p class="text-xl font-bold text-gray-800">{{ currentStreak }}</p>
            <p class="text-xs text-gray-500">Racha actual (días)</p>
          </div>
        </div>
        <div class="bg-gray-100 md:bg-white shadow rounded-xl p-4">
          <p class="text-xl font-bold text-gray-800">{{ trainedDaysCount }}</p>
          <p class="text-xs text-gray-500">Días entrenados en total</p>
        </div>
      </div>

      <div class="bg-gray-100 md:bg-white shadow rounded-xl p-4">
        <div class="flex items-center justify-between mb-4">
          <button type="button" @click="changeMonth(-1)" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500">
            <IconChevronLeft class="w-5 h-5" />
          </button>
          <p class="font-semibold text-[var(--color-primary)] capitalize">{{ monthLabel }}</p>
          <button type="button" @click="changeMonth(1)" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500">
            <IconChevronRight class="w-5 h-5" />
          </button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-400 mb-2">
          <span v-for="d in WEEKDAY_LABELS" :key="d">{{ d }}</span>
        </div>

        <div class="grid grid-cols-7 gap-1">
          <div v-for="(day, i) in calendarDays" :key="i" class="aspect-square flex items-center justify-center">
            <button
              v-if="day"
              type="button"
              @click="selectDay(day)"
              :disabled="isFuture(day)"
              class="w-full h-full flex flex-col items-center justify-center rounded-lg text-sm gap-0.5 transition"
              :class="[
                isFuture(day) ? 'text-gray-300 cursor-default' : 'text-gray-700 hover:bg-gray-100',
                isToday(day) ? 'ring-1 ring-[var(--color-primary)]' : '',
                selectedDate && selectedDate.getTime() === day.getTime() ? 'bg-[rgba(var(--color-primary-rgb),0.12)]' : '',
              ]"
            >
              {{ day.getDate() }}
              <span
                class="w-1.5 h-1.5 rounded-full"
                :class="trainedOn(day) ? 'bg-[var(--color-primary)]' : 'bg-transparent'"
              ></span>
            </button>
          </div>
        </div>
      </div>

      <div v-if="selectedDate" class="bg-gray-100 md:bg-white shadow rounded-xl p-4">
        <p class="text-sm font-semibold text-[var(--color-primary)] capitalize mb-1">{{ selectedDateLabel }}</p>
        <p v-if="selectedDateRoutines" class="text-sm text-gray-600">
          Entrenaste: {{ selectedDateRoutines.join(', ') }}
        </p>
        <p v-else class="text-sm text-gray-400">Día de descanso.</p>
      </div>
    </div>
  </section>
</template>
