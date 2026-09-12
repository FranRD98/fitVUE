<script setup>
import { computed, defineAsyncComponent, onMounted } from 'vue'
import { IconChevronLeft } from '@tabler/icons-vue'
import DashboardSidebar from './DashboardSidebar.vue'
import DashboardHeader from './DashboardHeader.vue'
import BottomNavBar from './BottomNavBar.vue'
import { useDashboardMenu, isSecondaryPanel } from '@/composables/useDashboardMenu'
import { useDashboardNav } from '@/composables/useDashboardNav'
import { useSwipeBack } from '@/composables/useSwipeBack'

const { visibleMenu } = useDashboardMenu()
const { activeKey, goTo } = useDashboardNav()

const showBackLink = computed(() => isSecondaryPanel(activeKey.value))

// Gesto de deslizar desde el borde izquierdo para volver a Perfil, ya que
// las secciones secundarias no son rutas reales con "atrás" nativo.
useSwipeBack(() => {
  if (isSecondaryPanel(activeKey.value)) {
    activeKey.value = 'config'
  }
})

const ActiveComponent = computed(() => componentsMap[activeKey.value])

const componentsMap = {
  home: defineAsyncComponent(() => import('@/components/dashboard/HomePanel.vue')),
  exercises: defineAsyncComponent(() => import('@/components/dashboard/ExercisesPanel.vue')),
  routines: defineAsyncComponent(() => import('@/components/dashboard/RoutinesPanel.vue')),
  diets: defineAsyncComponent(() => import('@/components/dashboard/DietsPanel.vue')),
  plates: defineAsyncComponent(() => import('@/components/dashboard/PlatesPanel.vue')),
  ingredients: defineAsyncComponent(() => import('@/components/dashboard/IngredientsPanel.vue')),
  guides: defineAsyncComponent(() => import('@/components/dashboard/GuidesPanel.vue')),
  users: defineAsyncComponent(() => import('@/components/dashboard/UsersPanel.vue')),
  config: defineAsyncComponent(() => import('@/components/dashboard/ConfigPanel.vue')),
  stats: defineAsyncComponent(() => import('@/components/dashboard/StatsPanel.vue')),
  measurements: defineAsyncComponent(() => import('@/components/dashboard/MeasurementsPanel.vue')),
  calendar: defineAsyncComponent(() => import('@/components/dashboard/CalendarPanel.vue'))
}

// En móvil la app se abre directamente en "Entrenamiento" (estilo Hevy);
// en escritorio se mantiene el panel de control como pantalla de inicio.
onMounted(() => {
  if (window.matchMedia('(max-width: 767px)').matches) {
    activeKey.value = 'routines'
  }
})
</script>

<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100 dark:bg-[#0f172a]">
    <!-- Sidebar: solo escritorio -->
    <div class="hidden md:block md:static md:z-auto">
      <DashboardSidebar
        :menu="visibleMenu"
        v-model:activeKey="activeKey"
        class="w-64 h-screen bg-white shadow-lg"
      />
    </div>

    <!-- Contenido principal -->
    <div class="flex flex-col flex-1 h-screen overflow-hidden">
      <DashboardHeader />
      <main class="flex-1 overflow-y-auto px-4 pt-[calc(env(safe-area-inset-top)+0.75rem)] pb-24 md:px-6 md:py-6 md:pb-6 md:pt-6">
        <!-- Volver a Perfil: solo en móvil, dentro de una sección secundaria.
             Vive dentro del contenido con scroll (no es una barra fija) para
             no añadir otra capa de cabecera encima del notch. -->
        <button
          v-if="showBackLink"
          type="button"
          @click="goTo('config')"
          class="md:hidden flex items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-300 mb-3 -ml-1 px-1 py-1"
        >
          <IconChevronLeft class="w-4 h-4" /> Perfil
        </button>

        <Transition name="panel-fade" mode="out-in">
          <component :is="ActiveComponent" :key="activeKey" />
        </Transition>
      </main>
    </div>

    <BottomNavBar />
  </div>
</template>
