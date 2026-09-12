<script setup>
import { computed } from 'vue'
import { IconHome, IconBarbell, IconUserCircle } from '@tabler/icons-vue'
import { useDashboardNav } from '@/composables/useDashboardNav'
import { isSecondaryPanel } from '@/composables/useDashboardMenu'

const { activeKey, goTo } = useDashboardNav()

// Las secciones "secundarias" (Ejercicios, Dietas, Estadísticas...) viven
// dentro de Perfil en móvil, así que la pestaña Perfil se marca activa
// también cuando se está viendo una de ellas.
const isProfileActive = computed(() => isSecondaryPanel(activeKey.value))

const tabs = [
  { key: 'home', label: 'Inicio', icon: IconHome },
  { key: 'routines', label: 'Entrenamiento', icon: IconBarbell },
  { key: 'config', label: 'Perfil', icon: IconUserCircle }
]
</script>

<template>
  <nav
    class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 flex pb-[env(safe-area-inset-bottom)]"
  >
    <button
      v-for="tab in tabs"
      :key="tab.key"
      type="button"
      @click="goTo(tab.key)"
      class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 transition-colors"
      :class="(tab.key === 'config' ? isProfileActive : activeKey === tab.key)
        ? 'text-[var(--color-primary)]'
        : 'text-gray-400'"
    >
      <component :is="tab.icon" class="w-6 h-6" :stroke-width="2" />
      <span class="text-[11px] font-medium">{{ tab.label }}</span>
    </button>
  </nav>
</template>
