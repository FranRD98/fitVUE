<script setup>
import { computed, defineAsyncComponent, onMounted } from 'vue'
import DashboardSidebar from './DashboardSidebar.vue'
import DashboardHeader from './DashboardHeader.vue'
import BottomNavBar from './BottomNavBar.vue'
import { useDashboardMenu } from '@/composables/useDashboardMenu'
import { useDashboardNav } from '@/composables/useDashboardNav'

const { visibleMenu } = useDashboardMenu()
const { activeKey } = useDashboardNav()

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
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
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
      <main class="flex-1 overflow-y-auto px-4 py-4 pb-24 md:px-6 md:py-6 md:pb-6">
        <component :is="ActiveComponent" />
      </main>
    </div>

    <BottomNavBar />
  </div>
</template>
