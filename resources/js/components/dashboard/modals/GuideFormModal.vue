<script setup>
import { ref, onMounted, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import { createGuide, updateGuide, getCategories, createCategory } from '@/api/services/guides'
import api from '@/api/client'

const userStore = useUserStore()

const props = defineProps({
  show: Boolean,
  initialData: Object
})

const emit = defineEmits(['close', 'saved'])

const guide = ref({
  title: '',
  description: '',
  content: '',
  author: '',
  id_category: '',
  header_image: '',
  created_at: new Date().toISOString()
})

const categories = ref([])
const loading = ref(true)
const showCategoryModal = ref(false)
const newCategoryTitle = ref('')
const imageFile = ref(null)

// Cargar categorías al montar
onMounted(async () => {
  try {
    categories.value = await getCategories()
    if (!userStore.userData) {
      await userStore.fetchUserData()
    }
  } catch (err) {
    console.error('Error cargando categorías o usuario:', err)
  } finally {
    loading.value = false
  }
})

// Ver si es edición
watch(
  () => props.initialData,
  (newVal) => {
    if (newVal) {
      const id = typeof newVal.id_category === 'object' ? newVal.id_category.id : newVal.id_category
      guide.value = {
        ...newVal,
        id_category: id || '',
        header_image: newVal.header_image || ''
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

// Establecer autor al abrir si es nueva
watch(
  () => props.show,
  async (val) => {
    if (val && !props.initialData) {
      if (!userStore.userData) {
        await userStore.fetchUserData()
      }
      if (userStore.userData?.name) {
        guide.value.author = userStore.userData.name + ' ' + userStore.userData.last_name
      }
    }
  }
)

// Mostrar modal si elige "+ Crear nueva categoría"
watch(
  () => guide.value.id_category,
  (val) => {
    if (val === '__new') {
      showCategoryModal.value = true
    }
  }
)

// Subida de imagen
const handleImageChange = async (e) => {
  let file = e.target.files[0]
  if (!file) return

  file = await resizeImage(file, 800)
  const formData = new FormData()
  formData.append('image', file)

  try {
    const { data } = await api.post('/uploads/guides', formData)
    guide.value.header_image = data.url
  } catch (error) {
    console.error('Error al subir imagen:', error)
  }
}

// Eliminar imagen
const deleteImage = () => {
  guide.value.header_image = ''
}

// Crear nueva categoría
async function createNewCategory() {
  if (!newCategoryTitle.value.trim()) return
  try {
    const newCat = await createCategory(newCategoryTitle.value.trim())
    categories.value.push(newCat)
    guide.value.id_category = newCat.id
    newCategoryTitle.value = ''
    showCategoryModal.value = false
  } catch (err) {
    console.error('Error al crear categoría:', err)
  }
}

// Guardar guía
async function submitForm() {
  try {
    if (!guide.value.id && userStore.userData?.name) {
      guide.value.author = userStore.userData.name + ' ' + userStore.userData.last_name
    }

    if (guide.value.id) {
      await updateGuide(guide.value.id, guide.value)
    } else {
      await createGuide(guide.value)
    }

    emit('saved')
    close()
  } catch (err) {
    console.error('Error al guardar guía:', err)
  }
}

// Reset
function resetForm() {
  guide.value = {
    title: '',
    description: '',
    content: '',
    author: '',
    id_category: '',
    header_image: '',
    created_at: new Date().toISOString()
  }
}

// Cerrar
function close() {
  resetForm()
  emit('close')
}

// Redimensionar imagen antes de subir
const resizeImage = (file, maxWidth = 800) => {
  return new Promise((resolve) => {
    const img = new Image()
    const reader = new FileReader()

    reader.onload = (e) => {
      img.src = e.target.result
    }

    img.onload = () => {
      const canvas = document.createElement('canvas')
      const scaleFactor = maxWidth / img.width
      canvas.width = maxWidth
      canvas.height = img.height * scaleFactor

      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

      canvas.toBlob((blob) => {
        const resizedFile = new File([blob], file.name, { type: file.type })
        resolve(resizedFile)
      }, file.type, 0.8)
    }

    reader.readAsDataURL(file)
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-[rgba(0,0,0,0.6)] backdrop-blur-sm flex justify-center items-center z-50 px-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-xl relative">
      <!-- Botón de cerrar -->
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 class="text-xl font-semibold mb-4 text-[var(--color-primary)]">
        {{ guide.id ? 'Editar guía' : 'Crear nueva guía' }}
      </h2>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Título</label>
          <input v-model="guide.title" placeholder="Título de la guía" class="input" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Descripción</label>
          <input v-model="guide.description" placeholder="Descripción corta" class="input" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Contenido</label>
          <textarea v-model="guide.content" placeholder="Contenido de la guía" class="input textarea" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Categoría</label>
          <select v-model="guide.id_category" class="w-full border border-gray-300 p-2 rounded" required>
            <option disabled value="">Selecciona una categoría</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.title }}
            </option>
            <option value="__new">+ Crear nueva categoría</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Imagen de portada</label>
          <div class="image-upload-container mt-1">
            <input
              type="file"
              accept="image/*"
              @change="handleImageChange"
              class="hidden"
              id="image-upload-input"
            />
            <label
              for="image-upload-input"
              class="cursor-pointer border-dashed border-2 border-gray-300 p-6 text-center rounded-lg hover:border-gray-400 block"
            >
              <span v-if="!guide.header_image" class="text-gray-600">Haz clic para subir una imagen</span>
              <div v-if="guide.header_image" class="relative mt-4">
                <img :src="guide.header_image" alt="Imagen" class="w-full h-40 object-cover rounded" />
                <button
                  @click.prevent="deleteImage"
                  class="absolute top-2 right-2 w-6 h-6 bg-white bg-opacity-75 rounded-full flex items-center justify-center shadow hover:bg-opacity-100"
                  title="Eliminar imagen"
                >✖</button>
              </div>
            </label>
          </div>
        </div>

        <button
          type="submit"
          class="bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg hover:bg-[var(--color-secondary)]"
        >
          {{ guide.id ? 'Guardar cambios' : 'Crear guía' }}
        </button>
      </form>
    </div>
  </div>

  <!-- Modal nueva categoría -->
    <div v-if="showCategoryModal" class="fixed inset-0 bg-[rgba(0,0,0,0.6)] backdrop-blur-sm flex justify-center items-center z-50 px-4">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full relative">
        <!-- Botón de cerrar -->
        <button @click="showCategoryModal = false; guide.id_category = ''" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>

        <h3 class="text-lg font-semibold text-[var(--color-primary)] mb-4">Nueva categoría</h3>
        <input
          v-model="newCategoryTitle"
          type="text"
          placeholder="Nombre de la categoría"
          class="w-full border border-gray-300 p-2 rounded mb-4"
        />
        <button
          @click="createNewCategory"
          class="w-full bg-[var(--color-primary)] text-white py-2 rounded hover:bg-[var(--color-secondary)]"
        >
          Guardar categoría
        </button>
      </div>
    </div>
</template>

<style scoped>
  .input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    line-height: 1.5rem;
    outline: none;
    transition: border-color 0.2s ease;
  }
  .input:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 1px var(--color-primary);
  }
  .textarea {
    min-height: 6rem;
    resize: vertical;
  }
</style>
