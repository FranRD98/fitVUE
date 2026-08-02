<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { createPlate, updatePlate } from '@/api/services/plates'
import { getIngredients } from '@/api/services/ingredients'

const props = defineProps({
  show: Boolean,
  plate: Object
})
const emit = defineEmits(['close', 'saved'])

const userStore = useUserStore()
const ingredients = ref([])

const form = ref({
  name: '',
  items: []
})

const isEditing = computed(() => !!props.plate)

onMounted(async () => {
  try {
    const userId = userStore.userData?.uid
    if (!userId) return

    const fetched = await getIngredients(userId)
    ingredients.value = fetched.sort((a, b) => a.name.localeCompare(b.name))
  } catch (err) {
    console.error('Error cargando ingredientes:', err)
  }
})

// Inicializar o cargar el plato si es edición
watch(() => props.plate, (plate) => {
  if (plate) {
    form.value = {
      name: plate.name,
      items: plate.items.map(item => ({
        ingredientId: item.ingredient_id,
        quantity: item.quantity
      }))
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function resetForm() {
  form.value = { name: '', items: [{ ingredientId: '', quantity: 0 }] }
}

function addIngredientRow() {
  form.value.items.push({ ingredientId: '', quantity: 0 })
}

function removeIngredientRow(index) {
  form.value.items.splice(index, 1)
}

async function save() {
  const payload = {
    ...form.value,
    items: form.value.items.map(item => ({
      ingredient_id: item.ingredientId,
      quantity: item.quantity
    }))
  }

  if (isEditing.value) {
    await updatePlate(props.plate.id, payload)
  } else {
    await createPlate(payload, userStore.userData?.uid)
  }

  emit('saved')
  resetForm()
  emit('close')
}
</script>


<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-xl space-y-6 relative">
      <!-- Botón de cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 class="text-2xl font-bold text-[var(--color-primary)]">{{ isEditing ? 'Editar' : 'Nuevo' }} plato</h2>

      <form @submit.prevent="save" class="space-y-4">
        <!-- Nombre del plato -->
        <div>
          <label class="block text-sm font-medium mb-1">Nombre del plato</label>
          <input v-model="form.name" placeholder="Ej: Ensalada mixta" class="input" />
        </div>

        <!-- Lista de ingredientes -->
        <div v-if="ingredients.length" class="space-y-3">
          <label class="block text-lg font-medium text-gray-700">Listado de ingredientes:</label>
          <div class="grid grid-cols-12 gap-2 text-sm font-medium text-gray-500 mb-1 px-1">
            <div class="col-span-7">Ingrediente</div>
            <div class="col-span-3">Cantidad (g)</div>
          </div>

          <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-2 items-center">
            <select v-model="item.ingredientId" class="col-span-7 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
              <option disabled value="">Selecciona ingrediente</option>
              <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }}</option>
            </select>

            <input v-model.number="item.quantity" type="number" placeholder="Cantidad (g)" class="col-span-3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]" />

            <button type="button" @click="removeIngredientRow(index)" class="text-red-500 text-xs hover:underline col-span-2 text-right">Eliminar</button>
          </div>

          <button type="button" @click="addIngredientRow" class="text-sm text-blue-600 hover:underline">+ Añadir ingrediente</button>
        </div>

        <!-- Mensaje si no hay ingredientes -->
        <p v-else class="text-sm text-red-500 mt-2">
          ⚠️ No puedes crear un plato sin antes haber creado ingredientes.
        </p>
        <!-- Guardar -->
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
