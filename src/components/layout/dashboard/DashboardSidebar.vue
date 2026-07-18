<script setup>
import { computed } from 'vue'
import { IconCrown } from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'  // Importamos el store de Pinia

const userStore = useUserStore()  // Usamos el store de usuario
const { logout } = userStore

const props = defineProps({
  menu: Array,
  activeKey: String
})
const emit = defineEmits(['update:activeKey', 'close'])

function setActive(key) {
  emit('update:activeKey', key)
  emit('close') // cerrar en móvil al seleccionar
}

const badgeLabel = computed(() => {
  switch (userStore.userData?.plan_id) {
    case 2:
      return 'Premium'
    case 3:
      return 'PRO'
    default:
      return 'Free'
  }
})

const badgeColor = computed(() => {
  switch (userStore.userData?.plan_id) {
    case 2:
      return 'bg-yellow-400 text-yellow-900'
    case 3:
      return 'bg-purple-600 text-white'
    default:
      return 'bg-gray-300 text-gray-700'
  }
})

// El client_reference_id permite al webhook de Stripe identificar a qué usuario aplicar el plan tras el pago.
const upgradeUrl = computed(() => {
  const uid = userStore.userData?.uid
  return uid
    ? `https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00?client_reference_id=${uid}`
    : 'https://buy.stripe.com/test_eVqdR95N6gMG4a19pvdfG00'
})
</script>

<template>
  <aside class="flex flex-col h-full">
    <div class="py-4 px-6 flex items-center justify-between">
        <router-link to="/" class="text-4xl font-bold flex items-center">
        <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
    <span
      v-if="badgeLabel"
      :class="[
        'text-xs font-bold px-2 py-0.5 rounded-full uppercase ml-2',
        badgeColor
      ]"
    >
      {{ badgeLabel }}
    </span>
  </router-link>
      
      <!-- Botón cerrar solo visible en móvil -->
      <button class="md:hidden text-xl" @click="$emit('close')">✕</button>
    </div>

    <nav class="flex-1 overflow-y-auto px-4">
      <ul class="space-y-1">
        <li
          v-for="item in menu"
          :key="item.key"
          @click="setActive(item.key)"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg transition cursor-pointer',
            item.key === activeKey ? 'bg-[var(--color-primary)] text-white' : 'text-gray-600 hover:bg-gray-100'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5" :stroke-width="2" />
          <span class="text-sm font-medium">{{ item.label }}</span>
        </li>
      </ul>
    </nav>


    <!-- Premium: solo visible en desktop -->
    <div v-if="userStore.userData?.plan_id === 1" class="mt-auto p-4">
      <div class="bg-gradient-to-br from-cyan-900 via-cyan-700 to-cyan-600 text-white p-4 rounded-xl shadow-lg">
        <div class="bg-white/10 rounded-full w-10 h-10 flex items-center justify-center mb-3">
          <IconCrown class="w-6 h-6"/>
        </div>
        <p class="font-semibold mb-1">Actualiza a PREMIUM</p>
        <p class="text-xs mb-4">y desbloquea nuevas funcionalidades</p>
       <a
        :href="upgradeUrl"
        class="block w-full text-center bg-white text-cyan-900 text-sm font-semibold py-1.5 rounded-full hover:bg-gray-100 transition"
      >
        Actualizar
      </a>
      </div>
    </div>

    <!-- Botón de cerrar sesión -->
    <div class="p-4 border-t border-gray-200">
      <button
        @click="logout"
        class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-gray-600 hover:text-red-600 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 002 2h3a2 2 0 002-2V7a2 2 0 00-2-2h-3a2 2 0 00-2 2v1"/>
        </svg>
        Cerrar sesión
      </button>
    </div>

  </aside>
</template>
