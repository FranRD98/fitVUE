import { createRouter, createWebHistory } from 'vue-router';

// Importamos las vistas
import Home from '@/views/Home.vue';
import About from '@/views/static/About.vue';
import Contact from '@/views/static/Contact.vue';
import Login from '@/views/auth/Login.vue';
import StartChange from '@/views/auth/StartChange.vue';
import NotFound from '@/views/static/NotFound.vue';
import FAQ from '@/views/static/FAQ.vue';

import RoutineList from '@/views/routines/Routine-list.vue';
import RoutineDetail from '@/views/routines/RoutineDetail.vue';

import GuideList from '@/views/guides/Guide-list.vue';
import GuideDetail from '@/views/guides/GuideDetail.vue';

import PrivacyPolicy from '@/views/legal/PrivacyPolicy.vue';
import Terms from '@/views/legal/Terms.vue';

import Dashboard from '@/components/layout/dashboard/DashboardLayout.vue';
import NewReview from '@/components/dashboard/NewReview.vue';
import ReviewDetail from '@/components/dashboard/ReviewDetail.vue'
import StartRoutine from '@/components/dashboard/StartRoutine.vue';

import Register from '@/views/auth/Register.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/nosotros', name: 'About', component: About },
  { path: '/rutinas', name: 'Rutinas', component: RoutineList },
  { path: '/rutinas/categoria/:category', name: 'Rutinas por categoría', component: RoutineList },
  { path: '/rutinas/:routine/:id', name: 'Rutina Detalle', component: RoutineDetail },
  
  // Guías
  { path: '/guias', name: 'Guia listado', component: GuideList },
  { path: '/guias/:category', name: 'Guia por categoria', component: GuideList },
  { path: '/guias/:category/:id', name: 'Guia detalle', component: GuideDetail },

  { path: '/contacto', name: 'Contact', component: Contact },
  { path: '/FAQ', name: 'Preguntas Frecuentes', component: FAQ },
  { path: '/login', name: 'Login', component: Login },
  { path: '/sign-in', name: 'Register', component: Register },
  
  // Dashboard
  { path: '/dashboard', name: 'Dashboard', component: Dashboard },
  { path: '/dashboard/newReview', name: 'New Review', component: NewReview },
  { path: '/user/:userId/:reviewId', name: 'Review Detail', component: ReviewDetail },
  { path: '/user/:userId/iniciar-rutina', name: 'Start Routine', component: StartRoutine },

  
  { path: '/empezar/:suscriptionPlan', name: 'Start Change', component: StartChange },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound }, // Página 404
  { path: '/politica-privacidad', name: 'Politica de privacidad', component: PrivacyPolicy }, // Página 'Terminos y condiciones'
  { path: '/terminos-y-condiciones', name: 'Terminos y condiciones', component: Terms }, // Página 'Terminos y condiciones'

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;