<script setup>
import { ref, computed } from 'vue'
import {
  IconChevronRight, IconLogout, IconTrophy, IconBarbell, IconRuler2, IconCalendar,
  IconPencil, IconSettings, IconMail, IconInfoCircle
} from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'
import { storeToRefs } from 'pinia'
import { useDashboardNav } from '@/composables/useDashboardNav'
import { useDashboardMenu } from '@/composables/useDashboardMenu'
import EditProfileModal from '@/components/dashboard/modals/EditProfileModal.vue'
import SettingsModal from '@/components/dashboard/modals/SettingsModal.vue'
import ContactModal from '@/components/dashboard/modals/ContactModal.vue'
import AboutModal from '@/components/dashboard/modals/AboutModal.vue'

const userStore = useUserStore()
const { userData } = storeToRefs(userStore)
const { logout } = userStore
const { goTo } = useDashboardNav()
const { secondaryMenu } = useDashboardMenu()

const infoButtons = [
  { key: 'stats', label: 'Estadísticas', icon: IconTrophy },
  { key: 'exercises', label: 'Ejercicios', icon: IconBarbell },
  { key: 'measurements', label: 'Medidas', icon: IconRuler2 },
  { key: 'calendar', label: 'Calendario', icon: IconCalendar },
]

// "Ejercicios" ya vive en el grid de Información, así que no se repite
// en la lista de otras secciones (Dietas, Platos...).
const otherSections = computed(() => secondaryMenu.value.filter(i => i.key !== 'exercises'))

const showEditProfile = ref(false)
const showSettings = ref(false)
const showContact = ref(false)
const showAbout = ref(false)
</script>

<template>
  <section>
    <div class="flex items-start justify-between gap-3 mb-6">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Perfil</h1>
      <div class="flex items-center gap-1 pt-1">
        <button type="button" @click="showEditProfile = true" class="p-2 text-gray-500 hover:text-[var(--color-primary)] transition" aria-label="Editar perfil">
          <IconPencil class="w-5 h-5" />
        </button>
        <button type="button" @click="showSettings = true" class="p-2 text-gray-500 hover:text-[var(--color-primary)] transition" aria-label="Configuración">
          <IconSettings class="w-5 h-5" />
        </button>
        <button type="button" @click="showContact = true" class="p-2 text-gray-500 hover:text-[var(--color-primary)] transition" aria-label="Contáctanos">
          <IconMail class="w-5 h-5" />
        </button>
        <button type="button" @click="showAbout = true" class="p-2 text-gray-500 hover:text-[var(--color-primary)] transition" aria-label="Acerca de">
          <IconInfoCircle class="w-5 h-5" />
        </button>
      </div>
    </div>

    <div class="flex items-center gap-3 mb-6">
      <img
        :src="userData?.profile_image || '/img/default-profile.svg'"
        alt="Imagen de perfil"
        class="w-14 h-14 rounded-full object-cover"
      />
      <div class="min-w-0">
        <p class="font-semibold text-gray-800 truncate">{{ userData?.name }} {{ userData?.last_name }}</p>
        <p class="text-sm text-gray-500 truncate">{{ userData?.email }}</p>
      </div>
    </div>

    <!-- Información: histórico de estadísticas, ejercicios, medidas y calendario -->
    <div class="mb-6">
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Información</h2>
      <div class="grid grid-cols-2 gap-3">
        <button
          v-for="item in infoButtons"
          :key="item.key"
          type="button"
          @click="goTo(item.key)"
          class="flex items-center gap-3 bg-white shadow rounded-xl px-4 py-3.5 text-left hover:shadow-md transition"
        >
          <component :is="item.icon" class="w-5 h-5 text-[var(--color-primary)]" :stroke-width="2" />
          <span class="text-sm font-semibold text-gray-700">{{ item.label }}</span>
        </button>
      </div>
    </div>

    <!-- Accesos a las demás secciones: solo en móvil, en escritorio ya están en el menú lateral -->
    <div v-if="otherSections.length" class="md:hidden bg-white shadow rounded-xl divide-y divide-gray-100 mb-6 overflow-hidden">
      <button
        v-for="item in otherSections"
        :key="item.key"
        type="button"
        @click="goTo(item.key)"
        class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition"
      >
        <component :is="item.icon" class="w-5 h-5 text-[var(--color-primary)]" :stroke-width="2" />
        <span class="flex-1 text-sm font-medium text-gray-700">{{ item.label }}</span>
        <IconChevronRight class="w-4 h-4 text-gray-400" />
      </button>
    </div>

    <!-- Cerrar sesión: siempre lo último de la página (en escritorio ya está en el menú lateral) -->
    <button
      type="button"
      @click="logout"
      class="md:hidden w-full flex items-center justify-center gap-2 bg-white shadow rounded-xl px-4 py-3 text-red-500 font-medium hover:bg-red-50 transition"
    >
      <IconLogout class="w-5 h-5" :stroke-width="2" />
      Cerrar sesión
    </button>

    <EditProfileModal :show="showEditProfile" @close="showEditProfile = false" />
    <SettingsModal :show="showSettings" @close="showSettings = false" />
    <ContactModal :show="showContact" @close="showContact = false" />
    <AboutModal :show="showAbout" @close="showAbout = false" />
  </section>
</template>
