<script setup>
import { ref, onMounted, computed } from 'vue'
import { getPublishedGuideCategoriesInUse } from '@/api/services/guides'
import { getPublishedRoutineCategoriesInUse } from '@/api/services/routines'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const { logout } = userStore

const mobileMenuOpen = ref(false)
const toggleMobileMenu = () => (mobileMenuOpen.value = !mobileMenuOpen.value)
const closeMenu = () => (mobileMenuOpen.value = false)

const guideCategories = ref([])
const routineCategories = ref([])

onMounted(async () => {
  // Obtener categorías de guías que estén publicadas
  guideCategories.value = (await getPublishedGuideCategoriesInUse())
    .filter(cat => !!cat.icon_path)
  // Obtener categorías de rutinas
  routineCategories.value = await getPublishedRoutineCategoriesInUse()

})

// Computed seguros
const user = computed(() => userStore.user)
const userData = computed(() => userStore.userData)

const profileImage = computed(() =>
  userData.value?.profile_image ||
  '/img/default-profile.svg'
)

const userName = computed(() => userData.value?.name || 'Usuario')
</script>

<template>
  <!-- Navbar -->
  <nav class="bg-white shadow-md sticky top-0 z-50 pt-[env(safe-area-inset-top)]">
    <div class="max-w-7xl mx-auto px-6">
      
      <!-- Contenedor principal con 3 grupos -->
      <div class="flex justify-between items-center h-20">
        
        <!-- Grupo 1: Logo en escritorio -->
        <div class="hidden lg:flex items-center flex-1">
          
          <router-link to="/" class="text-4xl font-bold">
            <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
          </router-link>
        </div>

        <!-- Logo solo visible en móvil -->
         <router-link to="/" class="lg:hidden text-3xl font-bold">
            <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
          </router-link>
        
        <!-- Grupo 2: Menú central -->
        <div class="hidden lg:flex justify-center items-center space-x-6 flex-1">

         <!-- Dropdown Rutinas -->
          <div class="relative dropdown">
            <router-link to="/rutinas">
              <a class="px-1 py-2 text-[var(--color-text)] font-medium hover:text-[var(--color-primary)]">
                Rutinas
                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </a>
            </router-link>
            
            <div class="dropdown-menu bg-white shadow-lg mt-0 border-t border-gray-200">
              <div class="dropdown-content p-8">
                <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-6">Conoce nuestras rutinas</h2>
                <div class="grid grid-cols-4 gap-8">
                  <div
                    v-for="category in routineCategories"
                    :key="category.id"
                    class="flex items-center space-x-3"
                  >
                    <img
                      :src="category.icon_path"
                      alt="Icono"
                      class="w-8 h-8 object-contain"
                    />
                    <router-link
                      :to="`/rutinas/categoria/${category.title}`"
                      class="nav-link text-md font-normal text-[var(--color-text)] hover:text-[var(--color-primary)]"
                    >
                      {{ category.title }}
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Dropdown Guías -->
          <div class="relative dropdown">
            <router-link to="/guias">
              <a class="px-1 py-2 text-[var(--color-text)] font-normal hover:text-[var(--color-primary)]">
                Guías
                <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </a>
            </router-link>

            <div class="dropdown-menu bg-white shadow-lg mt-0 border-t border-gray-200">
              <div class="dropdown-content p-8">
                <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-6">Aquí encontrarás diferentes guías</h2>
                <div class="grid grid-cols-4 gap-8">
                  <div
                    v-for="category in guideCategories"
                    :key="category.id"
                    class="flex items-center space-x-3"
                  >
                    <img
                      :src="category.icon_path || '/icons/default-icon.svg'"
                      alt="Icono"
                      class="w-8 h-8 object-contain"
                    />
                    <router-link
                      :to="`/guias/${category.title}`"
                      class="nav-link text-md font-normal text-[var(--color-text)] hover:text-[var(--color-primary)]"
                    >
                      {{ category.title }}
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        
        <!-- Grupo 3: Botones de acción -->
        <div class="hidden lg:flex items-center justify-end flex-1">
          <div v-if="user">

            <!-- Dropdown User Logged -->
            <div class="relative group">
              <!-- Botón avatar y nombre -->
              <div class="flex items-center space-x-2 cursor-pointer">
                <!-- Avatar del usuario -->
                <img
                  :src="profileImage"
                  alt="profile-icon"
                  class="rounded-full w-12 h-12 object-cover"
                />

                <span class="text-sm text-[var(--color-text)] font-medium">
                  ¡Hola, {{ userName }}!
                </span>
                <svg class="w-4 h-4 text-[var(--color-text)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>

              <!-- Submenu -->
              <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <div class="p-4 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-800">{{ userData?.name }} {{ userData?.last_name }}</p>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
                <div class="flex flex-col p-4 space-y-2">
                  <router-link to="/dashboard" class="text-sm hover:text-[var(--color-primary)]">Dashboard</router-link>
                  <button @click="logout" class="text-sm text-left hover:text-[var(--color-primary)]">Cerrar sesión</button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else>
            <router-link to="/login" class="border border-[var(--color-primary)] px-6 py-2 rounded-full font-normal hover:bg-[var(--color-secondary)] hover:text-white transition-colors mr-1">Iniciar sesión</router-link>
            <router-link to="/sign-in" class="bg-[var(--color-primary)] text-white px-6 py-2 rounded-full font-normal hover:bg-[var(--color-secondary)] transition-colors">Consigue tu cambio</router-link>
          </div>

        </div>

        <!-- Botón menú móvil -->
        <button @click="toggleMobileMenu" class="lg:hidden text-[var(--color-text)] focus:outline-none">
          <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

    </div>
  </nav>

<transition name="fade">
  <div
    v-if="mobileMenuOpen"
    class="fixed top-0 left-0 w-full h-full overflow-y-auto bg-white lg:hidden z-[999] px-6 pb-28"
    style="padding-top: calc(env(safe-area-inset-top) + 7rem)"
  >
    <!-- Botón cerrar -->
    <button
      @click="closeMenu"
      class="absolute right-4 text-gray-500 hover:text-[var(--color-primary)]"
      style="top: calc(env(safe-area-inset-top) + 1rem)"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Logo -->
    <div class="text-center mb-10">
      <router-link to="/" class="text-5xl font-bold" @click="closeMenu">
        <span>fit</span><span class="text-[var(--color-primary)]">VUE</span>
      </router-link>
    </div>

    <!-- Navegación -->
    <nav class="flex flex-col gap-5 text-[var(--color-text)] text-lg">
      <router-link
        @click="closeMenu"
        to="/rutinas"
        class="hover:text-[var(--color-primary)] transition"
      >
        Rutinas
      </router-link>
      <router-link
        @click="closeMenu"
        to="/guias"
        class="hover:text-[var(--color-primary)] transition"
      >
        Guías
      </router-link>
    </nav>

    <!-- Línea divisoria -->
    <div class="my-8 border-t border-gray-200"></div>

<!-- Acciones -->
<div class="flex flex-col items-center gap-4">
  <template v-if="user">
    <!-- Perfil del usuario logueado -->
    <div class="flex flex-col items-center gap-2 mb-4">
      <img
        :src="profileImage"
        alt="Foto de perfil"
        class="w-16 h-16 rounded-full object-cover border border-gray-300"
      />
      <p class="text-[var(--color-text)] font-semibold text-base">
        ¡Hola, {{ userName }}!
      </p>
    </div>

    <router-link
      @click="closeMenu"
      to="/dashboard"
      class="text-[var(--color-text)] text-base hover:text-[var(--color-primary)]"
    >
      Dashboard
    </router-link>
    <button
      @click="() => { logout(); closeMenu(); }"
      class="text-[var(--color-text)] text-base hover:text-[var(--color-primary)]"
    >
      Cerrar sesión
    </button>
  </template>

  <template v-else>
    <router-link
      @click="closeMenu"
      to="/login"
      class="text-[var(--color-text)] text-base hover:text-[var(--color-primary)]"
    >
      Iniciar sesión
    </router-link>

    <router-link
      @click="closeMenu"
      to="/empezar"
      class="bg-[var(--color-primary)] text-white px-6 py-3 rounded-full font-semibold hover:bg-[var(--color-secondary)] transition"
    >
      Consigue tu cambio
    </router-link>
  </template>
</div>

  </div>
</transition>



</template>

<style>
    .dropdown-menu {
      position: fixed;
      top: 80px; /* Ajusta según la altura del navbar */
      left: 0;
      width: 100vw;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .dropdown-user {
      position: absolute; /* Cambiado de fixed a absolute */
      top: 50px;           /* Justo debajo del trigger */
      right: 0;
      width: 16rem;        /* O ajusta a lo que necesites */
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      border-radius: 0.5rem;
      min-width: 180px;
      z-index: 50;
    }

    .dropdown:hover .dropdown-user {
      opacity: 1;
      visibility: visible;
    }

    .dropdown:hover .dropdown-menu{
      opacity: 1;
      visibility: visible;
    }

    .dropdown-content {
      max-width: 1200px;
      margin: 0 auto;
    }

    .nav-link {
      position: relative;
    }

    .nav-link:after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -6px;
      left: 0;
      background-color: #155e75;
      transition: width 0.3s ease;
    }

    .nav-link:hover:after {
      width: 100%;
    }

    .nav-link {
      position: relative;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -6px;
      left: 0;
      background-color: #155e75;
      transition: width 0.3s ease;
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .fade-enter-active, .fade-leave-active {
      transition: opacity 0.3s ease;
    }
    
    .fade-enter-from, .fade-leave-to {
      opacity: 0;
    }
  </style>