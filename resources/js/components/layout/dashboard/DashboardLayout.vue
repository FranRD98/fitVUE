<script setup>
import { ref, computed, defineAsyncComponent } from 'vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia
import DashboardSidebar from './DashboardSidebar.vue'
import DashboardHeader from './DashboardHeader.vue'

// Icons
import {
  IconChartBar,
  IconBarbell,
  IconTreadmill,
  IconSoup,
  IconToolsKitchen2,
  IconCarrot,
  IconBook,
  IconUsers,
  IconSettings
} from '@tabler/icons-vue'

// Obtener el store de usuario
const userStore = useUserStore()  // Usamos el store de usuario

const menuItems = [
  { key: 'home', label: 'Panel de control', icon: IconChartBar, roles: ['user', 'coach', 'admin'] },
  { key: 'exercises', label: 'Ejercicios', icon: IconBarbell, roles: ['user', 'coach', 'admin'] },
  { key: 'routines', label: 'Rutinas', icon: IconTreadmill, roles: ['user', 'coach', 'admin'] },
  { key: 'diets', label: 'Dietas', icon: IconToolsKitchen2, roles: ['user', 'coach', 'admin'] },
  { key: 'plates', label: 'Platos', icon: IconSoup, roles: ['user', 'coach', 'admin'] },
  { key: 'ingredients', label: 'Ingredientes', icon: IconCarrot, roles: ['user', 'coach', 'admin'] },
  { key: 'guides', label: 'Guías', icon: IconBook, roles: ['admin'] },
  {
    key: 'users',
    get label() {
      const role = userStore.userData?.role
      return role === 'coach' ? 'Clientes' : 'Usuarios'
    },
    icon: IconUsers,
    roles: ['coach', 'admin']
  },
  { key: 'config', label: 'Configuración', icon: IconSettings, roles: ['user', 'coach', 'admin'] }
]

const visibleMenu = computed(() =>
  userStore.userData ? menuItems.filter(i => i.roles.includes(userStore.userData.role)) : []
)

const activeKey = ref('home')
const showSidebar = ref(false)

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
  config: defineAsyncComponent(() => import('@/components/dashboard/ConfigPanel.vue'))
}
</script>

<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <!-- Overlay oscuro en móvil cuando sidebar está abierto -->
    <div
      v-if="showSidebar"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden transition-opacity"
      @click="showSidebar = false"
    />

    <!-- Sidebar fijo en desktop, drawer en móvil -->
    <div
      class="fixed z-50 md:static md:z-auto transition-transform duration-300 md:translate-x-0"
      :class="showSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
      <DashboardSidebar
        :menu="visibleMenu"
        v-model:activeKey="activeKey"
        @close="showSidebar = false"
        class="w-64 h-screen bg-white shadow-lg"
      />
    </div>

    <!-- Contenido principal -->
    <div class="flex flex-col flex-1 h-screen overflow-hidden">
      <DashboardHeader @toggleSidebar="showSidebar = !showSidebar" />
      <main class="flex-1 overflow-y-auto px-4 py-4 md:px-6 md:py-6">
        <component :is="ActiveComponent" />
      </main>
    </div>
  </div>
</template>
