import { ref, watchEffect } from 'vue'

const STORAGE_KEY = 'fitvue_theme'

// 'light' | 'dark' | 'system'. Por defecto "light": el modo oscuro real
// (colores de cada pantalla) todavía no está terminado, así que no debe
// activarse solo por la preferencia del sistema sin que el usuario lo pida
// explícitamente desde Configuración.
const theme = ref(localStorage.getItem(STORAGE_KEY) || 'light')

function applyTheme(value) {
  const root = document.documentElement
  const isDark = value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
  root.classList.toggle('dark', isDark)
  root.setAttribute('data-theme', value)
}

watchEffect(() => {
  applyTheme(theme.value)
  try { localStorage.setItem(STORAGE_KEY, theme.value) } catch { /* almacenamiento no disponible */ }
})

// Si el usuario elige "Dispositivo", seguir los cambios de tema del sistema en vivo.
if (typeof window !== 'undefined' && window.matchMedia) {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (theme.value === 'system') applyTheme('system')
  })
}

export function useTheme() {
  function setTheme(value) {
    theme.value = value
  }

  return { theme, setTheme }
}
