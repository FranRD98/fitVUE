import { ref } from 'vue'

// Estado de navegación del dashboard compartido entre el sidebar de escritorio,
// la barra inferior móvil y los enlaces de "Perfil" — así los tres pueden leer
// y cambiar la sección activa sin pasar props por componentes intermedios.
const activeKey = ref('home')

export function useDashboardNav() {
  function goTo(key) {
    activeKey.value = key
  }

  return { activeKey, goTo }
}
