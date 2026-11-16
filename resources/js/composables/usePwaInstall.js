import { ref, onMounted, onUnmounted } from 'vue'

// Chrome/Android (y escritorio) disparan "beforeinstallprompt" cuando la PWA
// es instalable; iOS/Safari no tiene ese evento, así que ahí solo se puede
// guiar a "Compartir > Añadir a pantalla de inicio" a mano.
export function usePwaInstall() {
  const canInstall = ref(false)
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true
  let deferredPrompt = null

  function onBeforeInstallPrompt(event) {
    event.preventDefault()
    deferredPrompt = event
    canInstall.value = true
  }

  function onInstalled() {
    canInstall.value = false
    deferredPrompt = null
  }

  onMounted(() => {
    window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt)
    window.addEventListener('appinstalled', onInstalled)
  })

  onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt)
    window.removeEventListener('appinstalled', onInstalled)
  })

  async function promptInstall() {
    if (!deferredPrompt) return null
    deferredPrompt.prompt()
    const { outcome } = await deferredPrompt.userChoice
    deferredPrompt = null
    canInstall.value = false
    return outcome
  }

  return { canInstall, isStandalone, promptInstall }
}
