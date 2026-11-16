import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { storeToRefs } from 'pinia'

// Vistas públicas
import Home from '@/views/Home.vue'
import Contact from '@/views/static/Contact.vue'
import Login from '@/views/auth/Login.vue'
import Register from '@/views/auth/Register.vue'
import StartChange from '@/views/auth/StartChange.vue'
import NotFound from '@/views/static/NotFound.vue'
import FAQ from '@/views/static/FAQ.vue'

// Contenido abierto
import RoutineList from '@/views/routines/Routine-list.vue'
import RoutineDetail from '@/views/routines/RoutineDetail.vue'
import GuideList from '@/views/guides/Guide-list.vue'
import GuideDetail from '@/views/guides/GuideDetail.vue'

// Legales
import PrivacyPolicy from '@/views/legal/PrivacyPolicy.vue'
import Terms from '@/views/legal/Terms.vue'

// Dashboard privado
import Dashboard from '@/components/layout/dashboard/DashboardLayout.vue'
import NewReview from '@/components/dashboard/NewReview.vue'
import ReviewDetail from '@/components/dashboard/ReviewDetail.vue'
import StartRoutine from '@/components/dashboard/StartRoutine.vue'
import SuccessPayment from '@/components/stripe/SuccessPayment.vue'
import FullReviewHistory from '@/components/dashboard/FullReviewHistory.vue'

const routes = [
  { path: '/', component: Home, meta: { public: true } },
  { path: '/contacto', component: Contact, meta: { public: true, title: 'Contacto' } },
  { path: '/FAQ', component: FAQ, meta: { public: true, title: 'Preguntas frecuentes' } },
  { path: '/login', component: Login, meta: { public: true, title: 'Iniciar sesión' } },
  { path: '/sign-in', component: Register, meta: { public: true, title: 'Crear cuenta' } },
  { path: '/empezar', component: StartChange, meta: { public: true, title: 'Empezar' } },

  // Rutinas
  { path: '/rutinas', component: RoutineList, meta: { public: true, title: 'Rutinas' } },
  { path: '/rutinas/categoria/:category', component: RoutineList, meta: { public: true, title: 'Rutinas' } },
  { path: '/rutinas/:routine/:id', component: RoutineDetail, meta: { public: true, title: 'Rutina' } },

  // Guías
  { path: '/guias', component: GuideList, meta: { public: true, title: 'Guías' } },
  { path: '/guias/:category', component: GuideList, meta: { public: true, title: 'Guías' } },
  { path: '/guias/:category/:id', component: GuideDetail, meta: { public: true, title: 'Guía' } },

  // Dashboard (protegido)
  { path: '/dashboard', component: Dashboard, meta: { title: 'Panel' } },
  { path: '/dashboard/newReview', component: NewReview, meta: { title: 'Nueva revisión' } },
  { path: '/user/:userId/:reviewId', component: ReviewDetail, meta: { title: 'Revisión' } },
  { path: '/user/:userId/iniciar-rutina', component: StartRoutine, meta: { title: 'Entrenando' } },
  { path: '/dashboard/historial', name: 'Historial', component: FullReviewHistory, meta: { title: 'Historial' } },

  // Pagamiento (stripe)
  { path: '/pago-aceptado', component: SuccessPayment, meta: { title: 'Pago confirmado' } },

  // Legales
  { path: '/politica-privacidad', component: PrivacyPolicy, meta: { public: true, title: 'Política de privacidad' } },
  { path: '/terminos-y-condiciones', component: Terms, meta: { public: true, title: 'Términos y condiciones' } },

  // 404
  { path: '/:pathMatch(.*)*', component: NotFound, meta: { public: true, title: 'Página no encontrada' } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Protección global de rutas
router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  const { user } = storeToRefs(userStore)

  if (!user.value) await userStore.fetchUserData()

  const isLoggedIn = !!user.value
  const isPublic = to.meta.public === true

  if (!isLoggedIn && !isPublic) return next('/login')

  // Si vuelve a login/registro ya autenticado (p. ej. tras cancelar el pago de
  // Stripe), que no se salte el onboarding: ahí es donde se piden nombre y
  // apellidos, y sin ellos el panel muestra "Usuario".
  if (isLoggedIn && ['/login', '/sign-in'].includes(to.path)) {
    return next(user.value.completed_form ? '/dashboard' : '/empezar')
  }

  // Cualquier otra zona protegida (p. ej. /dashboard tecleado a mano) exige
  // haber pasado antes por el onboarding de nombre y apellidos.
  if (isLoggedIn && !user.value.completed_form && !isPublic && to.path !== '/empezar') {
    return next('/empezar')
  }

  return next()
})

// Título de pestaña por ruta: sin esto, toda la SPA se queda con el título
// genérico del <head> (bueno para la primera carga, pero no distingue
// pestañas al navegar ni ayuda a quien usa el historial del navegador).
const DEFAULT_TITLE = 'fitVUE - La app para mejorar tu estilo de vida'
router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} · fitVUE` : DEFAULT_TITLE
})

export default router