import { onMounted, onUnmounted } from 'vue'

// Gesto estilo iOS: deslizar desde el borde izquierdo de la pantalla hacia
// la derecha para "volver atrás". Como la navegación entre paneles del
// dashboard no usa rutas reales, no hay gesto nativo del sistema para esto;
// se detecta el toque a mano y se llama a onBack().
//
// Escucha también "touchmove" (no solo touchstart/touchend) y llama a
// preventDefault() en cuanto detecta un arrastre horizontal desde el borde:
// sin esto, en Safari/PWA el propio navegador puede interpretar el mismo
// gesto como "atrás" en el historial o como rebote de scroll, y nuestro
// gesto nunca llega a dispararse.
export function useSwipeBack(onBack, options = {}) {
  const edgeWidth = options.edgeWidth ?? 24
  const minDistance = options.minDistance ?? 60
  const maxVerticalDrift = options.maxVerticalDrift ?? 60

  let startX = 0
  let startY = 0
  let tracking = false
  let triggered = false

  function onTouchStart(e) {
    const t = e.touches[0]
    tracking = t.clientX <= edgeWidth
    triggered = false
    startX = t.clientX
    startY = t.clientY
  }

  function onTouchMove(e) {
    if (!tracking || triggered) return

    const t = e.touches[0]
    const dx = t.clientX - startX
    const dy = Math.abs(t.clientY - startY)

    if (dy >= maxVerticalDrift) {
      tracking = false
      return
    }

    if (dx > 10) {
      // Ya es claramente un arrastre horizontal desde el borde: evita que
      // el navegador lo use para su propio gesto de "atrás" o de rebote.
      e.preventDefault()
    }

    if (dx > minDistance) {
      triggered = true
      tracking = false
      onBack()
    }
  }

  function onTouchEnd() {
    tracking = false
  }

  onMounted(() => {
    window.addEventListener('touchstart', onTouchStart, { passive: true })
    window.addEventListener('touchmove', onTouchMove, { passive: false })
    window.addEventListener('touchend', onTouchEnd, { passive: true })
  })

  onUnmounted(() => {
    window.removeEventListener('touchstart', onTouchStart)
    window.removeEventListener('touchmove', onTouchMove)
    window.removeEventListener('touchend', onTouchEnd)
  })
}
