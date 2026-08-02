<script setup>
  import { IconBrandInstagram, IconBrandX, IconBrandLinkedin } from '@tabler/icons-vue'

</script>

<template>
  <footer class="bg-[#1f2937] text-gray-300 text-sm pt-12">
      <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10 pb-12 text-center md:text-left items-center md:items-start justify-center md:justify-start">

      <!-- Social + descripción -->
      <div class="space-y-4">
        <div class="flex gap-3">
          <a href="#" class="hover:text-white transition">
            <IconBrandInstagram class="w-6 h-6" />
          </a>
          <a href="#" class="hover:text-white transition">
            <IconBrandX class="w-6 h-6" />
          </a>
          <a href="#" class="hover:text-white transition">
            <IconBrandLinkedin class="w-6 h-6" />
          </a>
        </div>
        <p class="text-xs leading-snug max-w-sm">
          *FitVue no ofrece asesoramiento médico ni diagnóstico. Su uso es informativo.
        </p>
      </div>

      <!-- Navegación principal -->
      <div class="grid grid-cols-2 gap-6">
        <div>
          <h4 class="text-base font-semibold text-white mb-2">fitVUE</h4>
          <ul class="space-y-1">
            <li><router-link to="/rutinas" class="nav-link">Rutinas</router-link></li>
            <li><router-link to="/guias" class="nav-link">Guías</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-base font-semibold text-white mb-2">Soporte</h4>
          <ul class="space-y-1">
            <li><router-link to="/FAQ" class="nav-link">FAQ</router-link></li>
            <li><router-link to="/contacto" class="nav-link">Contacto</router-link></li>
          </ul>
        </div>
        
      </div>

      <!-- Info legal y contacto -->
      <div class="space-y-2">
        <h4 class="text-base font-semibold text-white">Contacto</h4>
        <p>Email: <span class="font-medium">support@fitvue.com</span></p>
        <p>Teléfono: <span class="font-medium">+34 567 890 012</span></p>
      </div>
    </div>

    <!-- Legal links -->
    <div class="bg-[#111827] text-gray-400 py-6">
      <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-xs gap-3">
        <p>© 2025 FitVue · Fran Riera</p>
        <div class="flex gap-4">
          <router-link to="/politica-privacidad" class="hover:underline">Política de privacidad</router-link>
          <router-link to="/terminos-y-condiciones" class="hover:underline">Términos y condiciones</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.nav-link {
  position: relative;
  display: inline-block;
  transition: color 0.3s ease;
  color: #d1d5db; /* text-gray-300 */
}
.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -2px;
  left: 0;
  background-color: white;
  transition: width 0.3s ease;
}
.nav-link:hover::after {
  width: 100%;
}
</style>
