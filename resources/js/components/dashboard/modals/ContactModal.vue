<script setup>
import { ref } from 'vue'
import { useUserStore } from '@/stores/user'

const CONTACT_EMAIL = 'fran@franriera.es'

const userStore = useUserStore()
const show = ref(false)
const type = ref('sugerencia') // 'sugerencia' | 'error'
const message = ref('')

function open() { show.value = true }
function close() { show.value = false }
defineExpose({ open, close })

function send() {
  if (!message.value.trim()) return

  const subject = type.value === 'error' ? 'Informe de error — fitVUE' : 'Sugerencia — fitVUE'
  const body = `${message.value}\n\n---\nUsuario: ${userStore.userData?.name || ''} ${userStore.userData?.last_name || ''} (${userStore.userData?.email || ''})`

  window.location.href = `mailto:${CONTACT_EMAIL}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`

  message.value = ''
  close()
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white dark:bg-[#0f172a] md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-lg md:max-h-[85vh] bg-white dark:bg-[#1e293b] md:rounded-xl shadow-xl flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b dark:border-white/10 pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="close" class="text-[var(--color-primary)] font-medium">Cancelar</button>
        <h2 class="font-semibold text-[var(--color-primary)]">Contáctanos</h2>
        <button type="button" @click="send" class="text-[var(--color-primary)] font-semibold">Enviar</button>
      </header>

      <div class="p-4 overflow-y-auto flex-1 space-y-4">
        <div class="flex gap-2">
          <button
            type="button"
            @click="type = 'sugerencia'"
            class="flex-1 border-2 rounded-xl px-3 py-2.5 text-sm font-semibold"
            :class="type === 'sugerencia' ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300'"
          >
            Sugerencia
          </button>
          <button
            type="button"
            @click="type = 'error'"
            class="flex-1 border-2 rounded-xl px-3 py-2.5 text-sm font-semibold"
            :class="type === 'error' ? 'border-[var(--color-primary)] text-[var(--color-primary)]' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300'"
          >
            Informe de error
          </button>
        </div>

        <textarea
          v-model="message"
          rows="8"
          placeholder="Cuéntanos qué se te ha ocurrido o qué ha fallado..."
          class="w-full border border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 rounded-lg p-3 text-sm resize-none"
        ></textarea>

        <p class="text-xs text-gray-400">
          Se abrirá tu app de correo para enviarlo a {{ CONTACT_EMAIL }}.
        </p>
      </div>
    </div>
  </div>
</template>
