import { computed } from 'vue'
import { useUserStore } from '@/stores/user'
import { usePlan } from '@/composables/usePlan'

import {
  IconChartBar,
  IconBarbell,
  IconTreadmill,
  IconSoup,
  IconToolsKitchen2,
  IconCarrot,
  IconUsers,
  IconSettings,
  IconWorld
} from '@tabler/icons-vue'

// Claves de las secciones que ya tienen su propio acceso directo (barra
// inferior en móvil / entradas propias en escritorio) y por tanto no se
// repiten en la lista de "otras secciones" de Perfil.
const PRIMARY_KEYS = ['home', 'routines', 'config']

// Dietas, platos e ingredientes son de Premium (y de coach/admin). El plan
// gratuito no los ve en el menú.
export const NUTRITION_KEYS = ['diets', 'plates', 'ingredients']

// Cualquier otra clave (incluidas las de histórico: stats/measurements/
// calendar, que no están en menuItems) se considera "dentro de Perfil" a
// efectos de resaltar la pestaña y mostrar la flecha de volver en móvil.
export function isSecondaryPanel(key) {
  return !PRIMARY_KEYS.includes(key)
}

export function useDashboardMenu() {
  const userStore = useUserStore()
  const { isFree } = usePlan()

  const menuItems = [
    { key: 'home', label: 'Panel de control', icon: IconChartBar, roles: ['user', 'coach', 'admin'] },
    { key: 'exercises', label: 'Ejercicios', icon: IconBarbell, roles: ['user', 'coach', 'admin'] },
    { key: 'routines', label: 'Entrenamiento', icon: IconTreadmill, roles: ['user', 'coach', 'admin'] },
    { key: 'diets', label: 'Dietas', icon: IconToolsKitchen2, roles: ['user', 'coach', 'admin'] },
    { key: 'plates', label: 'Platos', icon: IconSoup, roles: ['user', 'coach', 'admin'] },
    { key: 'ingredients', label: 'Ingredientes', icon: IconCarrot, roles: ['user', 'coach', 'admin'] },
    { key: 'web', label: 'WEB', icon: IconWorld, roles: ['admin'] },
    {
      key: 'users',
      get label() {
        return userStore.userData?.role === 'coach' ? 'Clientes' : 'Usuarios'
      },
      icon: IconUsers,
      roles: ['coach', 'admin']
    },
    { key: 'config', label: 'Configuración', icon: IconSettings, roles: ['user', 'coach', 'admin'] }
  ]

  const visibleMenu = computed(() => {
    if (!userStore.userData) return []

    return menuItems.filter((item) => {
      if (!item.roles.includes(userStore.userData.role)) return false
      if (isFree.value && NUTRITION_KEYS.includes(item.key)) return false
      return true
    })
  })

  const secondaryMenu = computed(() =>
    visibleMenu.value.filter(i => !PRIMARY_KEYS.includes(i.key))
  )

  return { menuItems, visibleMenu, secondaryMenu }
}
