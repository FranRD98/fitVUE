import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { TOKEN_KEY } from '@/api/client'

export const useUserStore = defineStore('user', () => {
  const user = ref(null)
  const userData = ref(null)
  const authError = ref('')
  const router = useRouter()

  // Registrar
  const register = async ({ email, password, ...profile }) => {
    authError.value = ''

    try {
      const { data } = await api.post('/register', { email, password, ...profile })
      localStorage.setItem(TOKEN_KEY, data.token)
      user.value = data.user
      userData.value = data.user

      return { success: true, user: data.user }
    } catch (err) {
      authError.value = err.response?.data?.message || 'Error al registrarse'
      return { error: err }
    }
  }

  // Login
  const login = async ({ email, password }) => {
    authError.value = ''

    try {
      const { data } = await api.post('/login', { email, password })
      localStorage.setItem(TOKEN_KEY, data.token)
      user.value = data.user
      userData.value = data.user
      router.push('/dashboard')
    } catch (err) {
      authError.value = err.response?.data?.message || 'Credenciales incorrectas'
    }
  }

  // Logout
  const logout = async () => {
    try {
      await api.post('/logout')
    } catch (err) {
      // El token ya podría ser inválido: continuamos con el logout local.
    }

    localStorage.removeItem(TOKEN_KEY)
    user.value = null
    userData.value = null
    router.push('/')
  }

  // Obtener usuario actual y datos extendidos
  const fetchUserData = async () => {
    if (!localStorage.getItem(TOKEN_KEY)) {
      user.value = null
      userData.value = null
      return
    }

    try {
      const { data } = await api.get('/me')
      user.value = data
      userData.value = data
    } catch (err) {
      localStorage.removeItem(TOKEN_KEY)
      user.value = null
      userData.value = null
    }
  }

  // Actualizar plan una vez pagado
  const updatePlan = async (newPlanId) => {
    if (!user.value) return
    await api.patch(`/users/${user.value.uid}`, { plan_id: newPlanId })
    await fetchUserData()
  }

  const updateUserData = async (uid, updates) => {
    await api.patch(`/users/${uid}`, updates)
  }

  // No hay un listener de sesión en tiempo real con auth por token; se mantiene por compatibilidad.
  const initAuthListener = () => {}

  return {
    user,
    userData,
    authError,
    register,
    login,
    logout,
    fetchUserData,
    updatePlan,
    updateUserData,
    initAuthListener
  }
})
