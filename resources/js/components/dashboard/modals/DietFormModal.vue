<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { getPlates, getMacros } from '@/api/services/plates'
import { createDiet, updateDiet } from '@/api/services/diets'
import { getIngredients } from '@/api/services/ingredients'
import { useUserStore } from '@/stores/user'

const props = defineProps({ show: Boolean, initialData: Object })
const emit = defineEmits(['close', 'saved'])

const plates = ref([])
const customMealName = ref('')
const showCustomMealInput = ref(false)
const userStore = useUserStore()

// Dieta
const diet = ref({
  title: '',
  description: '',
  user_id: '',
  meals: []
})

// Comidas básicas
const selectedMeals = ref([
  { name: 'Desayuno', enabled: false, items: [] },
  { name: 'Almuerzo', enabled: false, items: [] },
  { name: 'Comida', enabled: false, items: [] },
  { name: 'Merienda', enabled: false, items: [] },
  { name: 'Cena', enabled: false, items: [] },
])

// Función de reset
function resetForm() {
  diet.value = {
    title: '',
    description: '',
    user_id: '',
    meals: []
  }

  selectedMeals.value = [
    { name: 'Desayuno', enabled: false, items: [] },
    { name: 'Almuerzo', enabled: false, items: [] },
    { name: 'Comida', enabled: false, items: [] },
    { name: 'Merienda', enabled: false, items: [] },
    { name: 'Cena', enabled: false, items: [] }
  ]
}

watch(() => props.initialData, async (newData) => {
  if (newData) {
    const uid = userStore.userData?.uid;
    diet.value = {
      title: newData.title,
      description: newData.description,
      user_id: newData.user_id || uid,
      id: newData.id,
      meals: []
    };

    // Asegúrate de que plates.value esté actualizado
    if (!plates.value.length) {
      plates.value = await getPlates(uid);
    }

    selectedMeals.value = [
      { name: 'Desayuno', enabled: false, items: [] },
      { name: 'Almuerzo', enabled: false, items: [] },
      { name: 'Comida', enabled: false, items: [] },
      { name: 'Merienda', enabled: false, items: [] },
      { name: 'Cena', enabled: false, items: [] }
    ];

    newData.meals?.forEach((meal) => {
      const target = selectedMeals.value.find(m => m.name === meal.name);

      const enrichedPlates = (meal.items || []).map(plate => {
        // Buscar el plato completo en plates.value
        const fullPlate = plates.value.find(p => p.id === plate.id);
        return fullPlate || plate; // Usar el plato completo si se encuentra, de lo contrario, usar el original
      });

      if (target) {
        target.enabled = true;
        target.items = enrichedPlates;
      } else {
        selectedMeals.value.push({
          name: meal.name,
          enabled: true,
          items: enrichedPlates
        });
      }
    });
  } else {
    resetForm();
  }
}, { immediate: true });




// Carga inicial
onMounted(async () => {
  const uid = userStore.userData?.uid
    if (!uid) return


  plates.value = await getPlates(userStore.userData.uid)
  
  if (!props.initialData) {
    diet.value.user_id = uid
  }

  if (props.initialData) {
  diet.value = { ...props.initialData }

  props.initialData.meals?.forEach(meal => {
    const match = selectedMeals.value.find(m => m.name === meal.name)
    if (match) {
      match.enabled = true
      match.items = meal.items
    } else {
      selectedMeals.value.push({ ...meal, enabled: true })
    }
  })
}

})

// Añadir plato
function addPlateToMeal(meal, plate) {
  if (!plate) return;

  // Asegúrate de que 'items' es un array vacío si no está definido
  if (!Array.isArray(meal.items)) {
    meal.items = [];
  }

  if (!meal.items.find(p => p.id === plate.id)) {
    meal.items.push(plate);
    console.log("Plato añadido:", plate, "a la comida:", meal.name);
  }
}

// Eliminar comida personalizada
function removeCustomMeal(mealName) {
  const index = selectedMeals.value.findIndex(meal => meal.name === mealName);
  if (index !== -1) {
    selectedMeals.value.splice(index, 1);
  }
}

// Añadir comida personalizada
function addCustomMeal() {
  if (!customMealName.value.trim()) return
  selectedMeals.value.push({
    name: customMealName.value.trim(),
    enabled: true,
    items: []
  })
  customMealName.value = ''
  showCustomMealInput.value = false
}

// Calcular macros por comida
function calculateMacros(meal) {
  return meal.items.reduce(
    (acc, plate) => {
      const factor = 1 // Ya está ajustado por plato
      acc.protein += plate.protein * factor
      acc.carbs += plate.carbs * factor
      acc.fat += plate.fat * factor
      acc.calories += plate.calories * factor
      return acc
    },
    { protein: 0, carbs: 0, fat: 0, calories: 0 }
  )
}

// Función para formatear los valores de los macros
function formatMacroValue(value) {
  return isNaN(value) || value === null ? 0 : value.toFixed(1);
}

// Total del día
const totalMacros = computed(() => {
  return selectedMeals.value.reduce((acc, meal) => {
    if (meal.enabled) {
      meal.items.forEach(plate => {
        const macros = getMacros(plate);
        acc.protein += parseFloat(macros.protein);
        acc.carbs += parseFloat(macros.carbs);
        acc.fat += parseFloat(macros.fats);
        acc.calories += parseFloat(macros.calories);
      });
    }
    return acc;
  }, { protein: 0, carbs: 0, fat: 0, calories: 0 });
});

// Guardar
async function submitForm() {
  const uid = userStore.userData?.uid

  if (!diet.value.user_id && uid) {
    diet.value.user_id = uid
  }

  const cleanedDiet = { ...diet.value }

  delete cleanedDiet.totalCalories
  delete cleanedDiet.totalProtein
  delete cleanedDiet.totalCarbs
  delete cleanedDiet.totalFats

  cleanedDiet.meals = selectedMeals.value.filter(m => m.enabled && m.items.length > 0)

  if (!cleanedDiet.user_id || typeof cleanedDiet.user_id !== 'string') {
    alert('Error: no se ha podido determinar el usuario. Intenta recargar la página.')
    return
  }

  if (cleanedDiet.id) {
    await updateDiet(cleanedDiet.id, cleanedDiet)
  } else {
    await createDiet(cleanedDiet)
  }

  emit('saved')
  emit('close')
}



// Cerrar modal
function close() {
  emit('close')
}

</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex justify-center items-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-3xl relative overflow-y-auto max-h-[90vh]">

      <!-- Botón de cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <!-- Título -->
      <h2 class="text-2xl font-bold text-[var(--color-primary)] mb-4">
        {{ diet.id ? 'Editar dieta' : 'Crear nueva dieta' }}
      </h2>

      <!-- Formulario -->
      <form @submit.prevent="submitForm" class="space-y-6">

        <!-- Inputs generales -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label for="diet-title" class="block text-sm text-gray-700 font-medium mb-1">Título de la dieta</label>
            <input id="diet-title" v-model="diet.title" placeholder="Ej: Volumen 2300 kcal"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 transition-all focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] outline-none" required />
          </div>
          <div>
            <label for="diet-description" class="block text-sm text-gray-700 font-medium mb-1">Descripción</label>
            <input id="diet-description" v-model="diet.description" placeholder="Opcional..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 transition-all focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] outline-none" />
          </div>
        </div>

        <!-- Comidas -->
        <div class="space-y-6">

          <p v-if="!plates.length" class="text-sm text-red-500 mt-1 col-span-12">
            ⚠️ Necesitas crear al menos un plato antes de crear una dieta.
          </p>

          <div
            v-for="meal in selectedMeals"
            :key="meal.name"
            class="border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white hover:border-gray-400 transition-all duration-300"
          >
            <label class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <input type="checkbox" v-model="meal.enabled" />
                <span class="font-semibold text-[var(--color-primary)] text-base">{{ meal.name }}</span>
              </div>
              <button
                v-if="!['Desayuno', 'Almuerzo', 'Comida', 'Merienda', 'Cena'].includes(meal.name)"
                @click="removeCustomMeal(meal.name)"
                class="text-red-500 text-xs hover:underline"
              >
                Eliminar
              </button>
            </label>

            <Transition name="slide-fade">
              <div v-if="meal.enabled" class="space-y-2">
                <!-- Select platos -->
                <select
                  @change="e => addPlateToMeal(meal, plates.find(p => p.id === Number(e.target.value)))"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 transition-all focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] outline-none"
                >
                  <option disabled selected>Selecciona un plato</option>
                  <option v-for="plate in plates" :key="plate.id" :value="plate.id">{{ plate.name }}</option>
                </select>

                <!-- Lista platos -->
                <TransitionGroup name="drop-fade" tag="div">
                  <div class="overflow-x-auto mt-2">
                    <div class="flex space-x-4">
                      <div
                        v-for="(plate, index) in meal.items"
                        :key="plate.id"
                        class="bg-white border p-4 rounded-lg shadow-md min-w-[150px]"
                      >
                        <div class="flex justify-between items-center">
                          <div class="text-sm">
                            {{ plate.name }} —
                            <span class="text-xs text-gray-500">
                              {{ getMacros(plate).calories }} kcal
                            </span>
                          </div>
                          <button
                            @click="meal.items.splice(index, 1)"
                            class="text-red-500 hover:underline text-xs"
                          >
                            Quitar
                          </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                          <strong>Macros totales:</strong><br>
                          Proteína: {{ getMacros(plate).protein }}g •
                          Carbohidratos: {{ getMacros(plate).carbs }}g •
                          Grasa: {{ getMacros(plate).fats }}g
                        </div>
                      </div>
                    </div>
                  </div>
                </TransitionGroup>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Añadir comida personalizada -->
        <div class="text-sm mt-4">
          <button
            @click="showCustomMealInput = true"
            v-if="!showCustomMealInput"
            type="button"
            class="text-[var(--color-primary)] hover:underline"
          >
            + Añadir comida personalizada
          </button>
          <div v-if="showCustomMealInput" class="flex gap-2 mt-2">
            <input v-model="customMealName" placeholder="Nombre (Ej. Tentempié)"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 transition-all focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] outline-none" />
            <button @click="addCustomMeal" type="button"
              class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white px-4 py-2 rounded-lg text-sm font-medium">
              Agregar
            </button>
          </div>
        </div>

        <!-- Botón guardar -->
        <div class="flex justify-end pt-4 border-t mt-6">
          <button type="submit"
            class="bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white px-6 py-2 rounded-lg font-semibold">
            Guardar dieta
          </button>
        </div>
      </form>

      <!-- Totales del día -->
      <div class="mt-8 p-4 bg-gray-100 border rounded-xl text-sm">
        <h3 class="text-lg font-semibold text-[var(--color-primary)] mb-2">Totales del día</h3>
        <p>
          <span class="font-medium">{{ totalMacros.calories.toFixed(0) }} kcal</span> •
          Proteínas: {{ totalMacros.protein.toFixed(1) }}g •
          Carbohidratos: {{ totalMacros.carbs.toFixed(1) }}g •
          Grasas: {{ totalMacros.fat.toFixed(1) }}g
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animaciones personalizadas (no reemplazables por Tailwind) */
.slide-fade-enter-active {
  transition: all 0.3s ease;
}
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.drop-fade-enter-active {
  transition: all 0.3s ease;
}
.drop-fade-leave-active {
  transition: all 0.2s ease;
  position: absolute;
}
.drop-fade-enter-from {
  opacity: 0;
  transform: translateY(-15px);
}
.drop-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
