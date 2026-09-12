<script setup>
import { computed, ref } from 'vue'
import {
  IconChevronRight, IconTrophy, IconBarbell, IconRuler2, IconCalendar,
  IconPencil, IconSettings, IconToolsKitchen2, IconSoup, IconCarrot
} from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'
import { storeToRefs } from 'pinia'
import { useDashboardNav } from '@/composables/useDashboardNav'
import { useDashboardMenu } from '@/composables/useDashboardMenu'
import EditProfileModal from '@/components/dashboard/modals/EditProfileModal.vue'
import SettingsModal from '@/components/dashboard/modals/SettingsModal.vue'

const userStore = useUserStore()
const { userData } = storeToRefs(userStore)
const { goTo } = useDashboardNav()
const { secondaryMenu } = useDashboardMenu()

// Dietas/Platos/Ingredientes viven como botones de "Información" (junto a
// Estadísticas/Ejercicios/Medidas/Calendario); el resto (Guías, Usuarios,
// según el rol) se queda en la lista de abajo.
const infoButtons = computed(() => [
  { key: 'stats', label: 'Estadísticas', icon: IconTrophy },
  { key: 'exercises', label: 'Ejercicios', icon: IconBarbell },
  { key: 'measurements', label: 'Medidas', icon: IconRuler2 },
  { key: 'calendar', label: 'Calendario', icon: IconCalendar },
  { key: 'diets', label: 'Dietas', icon: IconToolsKitchen2 },
  { key: 'plates', label: 'Platos', icon: IconSoup },
  { key: 'ingredients', label: 'Ingredientes', icon: IconCarrot },
])
const infoKeys = infoButtons.value.map(i => i.key)

const otherSections = computed(() => secondaryMenu.value.filter(i => !infoKeys.includes(i.key)))

const editModal = ref(null)
const settingsModal = ref(null)
</script>

<template>
  <section>
    <div class="flex items-start justify-between gap-3 mb-6">
      <h1 class="text-3xl font-bold text-[var(--color-primary)] dark:text-white">Perfil</h1>
      <div class="flex items-center gap-1 pt-1">
        <button type="button" @click="editModal.open()" class="p-2 text-gray-500 dark:text-gray-300 hover:text-[var(--color-primary)] dark:hover:text-white transition" aria-label="Editar perfil">
          <IconPencil class="w-5 h-5" />
        </button>
        <button type="button" @click="settingsModal.open()" class="p-2 text-gray-500 dark:text-gray-300 hover:text-[var(--color-primary)] dark:hover:text-white transition" aria-label="Configuración">
          <IconSettings class="w-5 h-5" />
        </button>
      </div>
    </div>

    <div class="flex items-center gap-3 bg-white dark:bg-white/5 shadow rounded-xl p-4 mb-6">
      <img
        :src="userData?.profile_image || '/img/default-profile.svg'"
        alt="Imagen de perfil"
        class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-100 dark:ring-white/10"
      />
      <div class="min-w-0">
        <p class="font-semibold text-gray-800 dark:text-white truncate">{{ userData?.name }} {{ userData?.last_name }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-300 truncate">{{ userData?.email }}</p>
      </div>
    </div>

    <!-- Información: histórico y accesos rápidos -->
    <div class="mb-6">
      <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wide mb-2">Información</h2>
      <div class="grid grid-cols-2 gap-3">
        <button
          v-for="item in infoButtons"
          :key="item.key"
          type="button"
          @click="goTo(item.key)"
          class="flex items-center gap-3 bg-white dark:bg-white/5 shadow rounded-xl px-4 py-3.5 text-left hover:shadow-md transition"
        >
          <component :is="item.icon" class="w-5 h-5 text-[var(--color-primary)] dark:text-white" :stroke-width="2" />
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-100">{{ item.label }}</span>
        </button>
      </div>
    </div>

    <!-- Accesos a las demás secciones (Guías, Usuarios...): solo en móvil, en escritorio ya están en el menú lateral -->
    <div v-if="otherSections.length" class="md:hidden bg-white dark:bg-white/5 shadow rounded-xl divide-y divide-gray-100 dark:divide-white/10 mb-6 overflow-hidden">
      <button
        v-for="item in otherSections"
        :key="item.key"
        type="button"
        @click="goTo(item.key)"
        class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition"
      >
        <component :is="item.icon" class="w-5 h-5 text-[var(--color-primary)] dark:text-white" :stroke-width="2" />
        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-100">{{ item.label }}</span>
        <IconChevronRight class="w-4 h-4 text-gray-400" />
      </button>
    </div>

    <EditProfileModal ref="editModal" />
    <SettingsModal ref="settingsModal" />
  </section>
</template>
