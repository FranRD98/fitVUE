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
  { path: '/contacto', component: Contact, meta: { public: true } },
  { path: '/FAQ', component: FAQ, meta: { public: true } },
  { path: '/login', component: Login, meta: { public: true } },
  { path: '/sign-in', component: Register, meta: { public: true } },
  { path: '/empezar', component: StartChange, meta: { public: true } },

  // Rutinas
  { path: '/rutinas', component: RoutineList, meta: { public: true } },
  { path: '/rutinas/categoria/:category', component: RoutineList, meta: { public: true } },
  { path: '/rutinas/:routine/:id', component: RoutineDetail, meta: { public: true } },

  // Guías
  { path: '/guias', component: GuideList, meta: { public: true } },
  { path: '/guias/:category', component: GuideList, meta: { public: true } },
  { path: '/guias/:category/:id', component: GuideDetail, meta: { public: true } },

  // Dashboard (protegido)
  { path: '/dashboard', component: Dashboard },
  { path: '/dashboard/newReview', component: NewReview },
  { path: '/user/:userId/:reviewId', component: ReviewDetail },
  { path: '/user/:userId/iniciar-rutina', component: StartRoutine },
  { path: '/dashboard/historial', name: 'Historial', component: FullReviewHistory},

  // Pagamiento (stripe)
  { path: '/pago-aceptado', component: SuccessPayment },

  // Legales
  { path: '/politica-privacidad', component: PrivacyPolicy, meta: { public: true } },
  { path: '/terminos-y-condiciones', component: Terms, meta: { public: true } },

  // 404
  { path: '/:pathMatch(.*)*', component: NotFound, meta: { public: true } }
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
  if (isLoggedIn && ['/login', '/sign-in'].includes(to.path)) return next('/dashboard')

  return next()
})

export default router