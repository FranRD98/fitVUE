<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  initialData: {
    type: Object,
    default: () => ({ name: '', calories: '', protein: '', carbs: '', fats: '' })
  }
})
const emit = defineEmits(['close', 'save'])

const form = ref({
  name: '',
  calories: '',
  protein: '',
  carbs: '',
  fats: ''
})

// Para resetear el formulario cuando los datos cambian
watch(() => props.initialData, (val) => {
  form.value = { ...val }
}, { immediate: true })

const resetForm = () => {
  form.value = {
    name: '',
    calories: '',
    protein: '',
    carbs: '',
    fats: ''
  }
}

const handleSubmit = () => {
  if (!form.value.name) return
  emit('save', form.value) // Emitir los datos al componente padre
  emit('close') // Cerrar el modal
}

</script>
<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md space-y-6 relative">
      <!-- Botón de cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 class="text-2xl font-bold text-[var(--color-primary)]">{{ initialData.id ? 'Editar' : 'Nuevo' }} ingrediente</h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-1">
          <label for="name" class="text-sm font-medium text-gray-700">Nombre del ingrediente</label>
          <input id="name" v-model="form.name" placeholder="Ej: Pollo" class="input" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-1">
            <label for="calories" class="text-sm font-medium text-gray-700">Calorías (100g)</label>
            <input id="calories" v-model="form.calories" step="any" type="number" class="input" />
          </div>

          <div class="space-y-1">
            <label for="protein" class="text-sm font-medium text-gray-700">Proteínas</label>
            <input id="protein" v-model="form.protein" step="any" type="number" class="input" />

          </div>

          <div class="space-y-1">
            <label for="carbs" class="text-sm font-medium text-gray-700">Carbohidratos</label>
            <input id="carbs" v-model="form.carbs" step="any" type="number" class="input" />
          </div>

          <div class="space-y-1">
            <label for="fats" class="text-sm font-medium text-gray-700">Grasas</label>
            <input id="fats" v-model="form.fats" step="any" type="number" class="input" />
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <button type="submit" class="bg-[var(--color-primary)] text-white px-5 py-2 rounded-lg hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>


<style scoped>
.input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  background-color: white;
  color: #374151;
  transition: all 0.2s ease;
}

.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary);
  outline: none;
}

/* Slide down para abrir caja de comida */
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

/* Drop-fade para cada plato */
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