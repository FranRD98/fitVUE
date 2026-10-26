<script setup>
import { computed } from 'vue'
import { IconHome, IconBarbell, IconUserCircle } from '@tabler/icons-vue'
import { useDashboardNav } from '@/composables/useDashboardNav'
import { isSecondaryPanel } from '@/composables/useDashboardMenu'
import { useUserStore } from '@/stores/user'

const { activeKey, goTo } = useDashboardNav()
const userStore = useUserStore()

// Las secciones "secundarias" (Ejercicios, Dietas, Estadísticas...) viven
// dentro de Perfil en móvil, así que la pestaña Perfil se marca activa
// también cuando se está viendo una de ellas, y por supuesto cuando se está
// en el propio Perfil (isSecondaryPanel excluye 'config' porque tiene su
// propio acceso directo, así que hay que comprobarlo aparte aquí).
const isProfileActive = computed(() => activeKey.value === 'config' || isSecondaryPanel(activeKey.value))

const tabs = [
  { key: 'home', label: 'Inicio', icon: IconHome },
  { key: 'routines', label: 'Entrenamiento', icon: IconBarbell },
  { key: 'config', label: 'Perfil', icon: IconUserCircle }
]
</script>

<template>
  <nav
    class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-[#0f172a] border-t border-gray-200 dark:border-white/10 flex pt-1"
    style="padding-bottom: max(0.4rem, calc(env(safe-area-inset-bottom) - 14px))"
  >
    <button
      v-for="tab in tabs"
      :key="tab.key"
      type="button"
      @click="goTo(tab.key)"
      class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1 transition-colors"
      :class="(tab.key === 'config' ? isProfileActive : activeKey === tab.key)
        ? 'text-[var(--color-primary)] dark:text-white'
        : 'text-gray-400 dark:text-gray-300'"
    >
      <img
        v-if="tab.key === 'config' && userStore.userData?.profile_image"
        :src="userStore.userData.profile_image"
        alt=""
        class="w-6 h-6 rounded-full object-cover ring-2"
        :class="isProfileActive ? 'ring-[var(--color-primary)] dark:ring-white' : 'ring-transparent'"
      />
      <component v-else :is="tab.icon" class="w-6 h-6" :stroke-width="2" />
      <span class="text-[11px] font-medium">{{ tab.label }}</span>
    </button>
  </nav>
</template>
