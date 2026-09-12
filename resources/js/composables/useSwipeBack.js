import { onMounted, onUnmounted } from 'vue'

// Gesto estilo iOS: deslizar desde el borde izquierdo de la pantalla hacia
// la derecha para "volver atrás". Como la navegación entre paneles del
// dashboard no usa rutas reales, no hay gesto nativo del sistema para esto;
// se detecta el toque a mano y se llama a onBack().
export function useSwipeBack(onBack, options = {}) {
  const edgeWidth = options.edgeWidth ?? 28
  const minDistance = options.minDistance ?? 70
  const maxVerticalDrift = options.maxVerticalDrift ?? 60

  let startX = 0
  let startY = 0
  let tracking = false

  function onTouchStart(e) {
    const t = e.touches[0]
    tracking = t.clientX <= edgeWidth
    startX = t.clientX
    startY = t.clientY
  }

  function onTouchEnd(e) {
    if (!tracking) return
    tracking = false

    const t = e.changedTouches[0]
    const dx = t.clientX - startX
    const dy = Math.abs(t.clientY - startY)

    if (dx > minDistance && dy < maxVerticalDrift) {
      onBack()
    }
  }

  onMounted(() => {
    window.addEventListener('touchstart', onTouchStart, { passive: true })
    window.addEventListener('touchend', onTouchEnd, { passive: true })
  })

  onUnmounted(() => {
    window.removeEventListener('touchstart', onTouchStart)
    window.removeEventListener('touchend', onTouchEnd)
  })
}
