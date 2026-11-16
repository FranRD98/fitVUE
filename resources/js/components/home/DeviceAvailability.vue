<script setup>
import { ref } from 'vue'
import { usePwaInstall } from '@/composables/usePwaInstall'
import { IconBrandAndroid, IconBrandApple, IconDeviceLaptop, IconShare2, IconSquarePlus } from '@tabler/icons-vue'

const { canInstall, isStandalone, promptInstall } = usePwaInstall()
const showIosHelp = ref(false)

async function installAndroid() {
  if (canInstall.value) {
    await promptInstall()
    return
  }
  // Sin el evento beforeinstallprompt (ya instalada, requisitos no cumplidos
  // o navegador sin soporte): igual se puede entrar desde el menú del navegador.
  alert('Abre el menú de tu navegador y elige "Instalar aplicación" o "Añadir a pantalla de inicio".')
}
</script>

<template>
  <section class="relative overflow-hidden bg-[var(--color-bg-light)] py-20 px-6">
    <div
      aria-hidden="true"
      class="blob-float absolute -top-24 -left-24 w-72 h-72 rounded-full bg-[var(--color-primary)]/10 blur-2xl"
    ></div>
    <div
      aria-hidden="true"
      class="blob-float blob-float-delay absolute -bottom-24 -right-16 w-80 h-80 rounded-full bg-[var(--color-primary)]/10 blur-2xl"
    ></div>

    <div class="relative max-w-4xl mx-auto text-center">
      <h2 v-reveal class="text-3xl sm:text-4xl font-bold text-[var(--color-primary)] mb-4">
        Disponible en todos tus dispositivos
      </h2>
      <p v-reveal class="text-lg text-gray-600 mb-10 max-w-2xl mx-auto">
        FitVue funciona desde el navegador y también se instala como app, sin
        pasar por ninguna tienda: en tu móvil, tablet u ordenador, siempre con
        tus datos sincronizados.
      </p>

      <div v-if="!isStandalone" v-reveal class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <button type="button" @click="installAndroid" class="btn-fill btn-fill--solid px-6 py-3 rounded-full font-semibold">
          <span class="btn-fill__label inline-flex items-center gap-2">
            <IconBrandAndroid class="w-5 h-5" /> Instalar en Android / PC
          </span>
          <span class="btn-fill__sweep" aria-hidden="true">
            <span class="btn-fill__label inline-flex items-center gap-2">
              <IconBrandAndroid class="w-5 h-5" /> Instalar en Android / PC
            </span>
          </span>
        </button>

        <button type="button" @click="showIosHelp = true" class="btn-fill btn-fill--outline px-6 py-3 rounded-full font-semibold">
          <span class="btn-fill__label inline-flex items-center gap-2">
            <IconBrandApple class="w-5 h-5" /> Instalar en iPhone / iPad
          </span>
          <span class="btn-fill__sweep" aria-hidden="true">
            <span class="btn-fill__label inline-flex items-center gap-2">
              <IconBrandApple class="w-5 h-5" /> Instalar en iPhone / iPad
            </span>
          </span>
        </button>
      </div>
      <p v-else v-reveal class="text-sm text-gray-500 font-medium">Ya la tienes instalada en este dispositivo 🎉</p>

      <div v-reveal class="mt-10 flex items-center justify-center gap-2 text-gray-500 text-sm">
        <IconDeviceLaptop class="w-5 h-5" />
        <span>También disponible directamente desde cualquier navegador, sin instalar nada.</span>
      </div>
    </div>

    <!-- Instrucciones iOS: Safari no tiene instalación con un clic -->
    <div
      v-if="showIosHelp"
      class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4"
      @click.self="showIosHelp = false"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
        <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Añadir a pantalla de inicio</h3>
        <ol class="space-y-3 text-sm text-gray-600 list-decimal list-inside">
          <li class="flex items-start gap-2">
            <IconShare2 class="w-5 h-5 text-[var(--color-primary)] shrink-0 mt-0.5" />
            Pulsa el icono de <strong>Compartir</strong> en Safari.
          </li>
          <li class="flex items-start gap-2">
            <IconSquarePlus class="w-5 h-5 text-[var(--color-primary)] shrink-0 mt-0.5" />
            Elige <strong>"Añadir a pantalla de inicio"</strong>.
          </li>
        </ol>
        <button
          type="button"
          @click="showIosHelp = false"
          class="mt-6 w-full bg-[var(--color-primary)] text-white font-semibold py-2.5 rounded-full"
        >
          Entendido
        </button>
      </div>
    </div>
  </section>
</template>
