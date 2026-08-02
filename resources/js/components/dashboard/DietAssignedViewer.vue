<script setup>
import { ref, onMounted } from 'vue'
import { hydrateDietIngredients } from '@/api/services/diets' // Asegúrate de tener esta función exportada

const props = defineProps({
  show: Boolean,
  diet: Object
})

const emit = defineEmits(['close'])

const diet = ref(null)

onMounted(async () => {
  if (props.diet) {
    try {
      diet.value = await hydrateDietIngredients(props.diet)
    } catch (error) {
      console.error('Error hidratando dieta:', error)
    }
  }
})
</script>


<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center px-4"
  >
    <div
      class="bg-white rounded-2xl shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col relative overflow-hidden"
    >
      <!-- Botón cerrar -->
      <button
        @click="$emit('close')"
        class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition"
        aria-label="Cerrar"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>

      <div class="p-6 overflow-y-auto flex-1 space-y-6">
        <header class="border-b pb-4 space-y-2">
          <h1 class="text-3xl font-bold text-[var(--color-primary)]">
            {{ diet?.title }}
          </h1>
          <p class="text-gray-600">{{ diet?.description }}</p>

          <div class="bg-gray-50 rounded-xl px-6 py-4 mt-3">
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-y-3 gap-x-6 text-sm text-gray-700 text-center">
              <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Proteína
                </dt>
                <dd class="text-lg font-medium text-gray-800">
                  {{ diet?.totalProtein.toFixed(1) }} g
                </dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Carbohidratos
                </dt>
                <dd class="text-lg font-medium text-gray-800">
                  {{ diet?.totalCarbs.toFixed(1) }} g
                </dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Grasas
                </dt>
                <dd class="text-lg font-medium text-gray-800">
                  {{ diet?.totalFats.toFixed(1) }} g
                </dd>
              </div>
              <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  Total kcal
                </dt>
                <dd class="text-lg font-bold text-[var(--color-primary)]">
                  {{ diet?.totalCalories.toFixed(0) }} kcal
                </dd>
              </div>
            </dl>
          </div>
        </header>



        <!-- Meals -->
        <section
          v-for="(meal, index) in diet?.meals"
          :key="index"
          class="space-y-4"
        >
          <h2 class="text-2xl font-semibold text-[var(--color-secondary)]">
            {{ meal.name }}
          </h2>

          <div
            v-for="(plate, pIndex) in meal.items"
            :key="pIndex"
            class="bg-gray-100 p-4 rounded-xl shadow-sm"
          >
            <h3 class="text-xl font-medium text-gray-800 mb-2">{{ plate.name }}</h3>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-700">
              <template v-for="(item, iIndex) in plate.items" :key="iIndex">
                <dt class="font-medium">
                  {{
                    item.ingredient
                      ? item.ingredient.name
                      : `Ingrediente desconocido (ID: ${item.ingredient_id})`
                  }}
                </dt>
                <dd class="text-right">{{ item.quantity }} g</dd>
              </template>
            </dl>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>


<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
