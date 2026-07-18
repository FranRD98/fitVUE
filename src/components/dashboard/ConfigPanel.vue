<script setup>
import { ref, watch } from 'vue'
import { useUserStore } from '@/stores/user'
import { storeToRefs } from 'pinia' // 👈 Importante para mantener reactividad
import api from '@/supabase/config'
import { uploadProfileImage, updateUserData } from '@/supabase/services/users'

// Obtener el store y desestructurar con reactividad
const userStore = useUserStore()
const { userData } = storeToRefs(userStore)
const { fetchUserData } = userStore

// Campos del formulario
const name = ref('')
const lastName = ref('')
const email = ref('')
const password = ref('')
const profileImage = ref('')
const imageFile = ref(null)
const updating = ref(false)

const handleImageChange = async (e) => {
  const file = e.target.files[0]
  if (!file) return

  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    profileImage.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const handleSave = async () => {
  updating.value = true

  try {
    if (!userData.value?.uid) throw new Error('Falta el ID del usuario')

    let imageUrl = profileImage.value

    if (imageFile.value) {
      imageUrl = await uploadProfileImage(imageFile.value, userData.value.uid)
    }

    const updates = {
      name: name.value,
      last_name: lastName.value,
      email: email.value,
      profile_image: imageUrl
    }

    const accountChanges = {}
    if (email.value && email.value !== userData.value.email) accountChanges.email = email.value
    if (password.value) accountChanges.password = password.value

    if (Object.keys(accountChanges).length) {
      await api.patch('/me', accountChanges)
    }

    await updateUserData(userData.value.uid, updates)
    await fetchUserData()

    alert('Datos actualizados correctamente.')
  } catch (err) {
    console.error('Error al guardar los cambios:', err)
    alert(`Error al guardar los cambios: ${err.message || 'Error desconocido'}`)
  } finally {
    updating.value = false
  }
}

// Rellenar el formulario cuando se carguen los datos del usuario
watch(
  () => userData.value,
  (newVal) => {
    if (newVal) {
      name.value = newVal.name || ''
      lastName.value = newVal.last_name || ''
      email.value = newVal.email || ''
      profileImage.value = newVal.profile_image || ''
    }
  },
  { immediate: true }
)
</script>

<template>
  <section>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Configuración</h1>
    </div>

<div class="bg-white shadow rounded-xl p-6 max-w-3xl w-full mx-auto space-y-6">
      <!-- Imagen de perfil -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Imagen de perfil</label>
        <div class="image-upload-container">
          <input
            type="file"
            accept="image/*"
            @change="handleImageChange"
            class="hidden"
            id="profile-image-upload"
          />
          <label
            for="profile-image-upload"
            class="cursor-pointer border-dashed border-2 border-gray-300 p-4 w-full text-center rounded-lg hover:border-gray-400 flex flex-col items-center justify-center"
          >
            <span v-if="!profileImage" class="text-gray-600">Haz clic para subir una imagen</span>
            <div v-else class="relative w-32 h-32">
              <img :src="profileImage" alt="Imagen de perfil" class="w-full h-full object-cover rounded-full border" />
              <button
                @click.prevent="profileImage = null"
                class="absolute top-1 right-1 w-6 h-6 bg-white bg-opacity-75 rounded-full flex items-center justify-center shadow hover:bg-opacity-100"
                title="Eliminar imagen"
              >
                ✖
              </button>
            </div>
          </label>
        </div>
      </div>

      <!-- Campos -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
        <input v-model="name" type="text" class="w-full border rounded p-2 text-sm" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
        <input v-model="lastName" type="text" class="w-full border rounded p-2 text-sm" />
      </div>

      <!--<div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
        <input v-model="email" type="email" class="w-full border rounded p-2 text-sm" />
      </div>-->

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
        <input v-model="password" type="password" placeholder="Deja en blanco para no cambiarla" class="w-full border rounded p-2 text-sm" />
      </div>

      <!-- Botones -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t">
        <button
          @click="handleSave"
          :disabled="updating"
          class="bg-[var(--color-primary)] text-white px-6 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] disabled:opacity-50 transition"
        >
          {{ updating ? 'Guardando...' : 'Guardar cambios' }}
        </button>
      </div>
    </div>
  </section>
</template>
