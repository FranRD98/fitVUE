<script setup>
import { ref } from 'vue'
import { IconX } from '@tabler/icons-vue'
import { useUserStore } from '@/stores/user'
import { storeToRefs } from 'pinia'
import api from '@/api/client'
import { uploadProfileImage, updateUserData } from '@/api/services/users'

const userStore = useUserStore()
const { userData } = storeToRefs(userStore)
const { fetchUserData } = userStore

const show = ref(false)

function fillForm() {
  const newVal = userData.value
  if (!newVal) return
  name.value = newVal.name || ''
  lastName.value = newVal.last_name || ''
  email.value = newVal.email || ''
  profileImage.value = newVal.profile_image || ''
  password.value = ''
  imageFile.value = null
}

function open() {
  fillForm()
  show.value = true
}

function close() {
  show.value = false
}

defineExpose({ open, close })

const name = ref('')
const lastName = ref('')
const email = ref('')
const password = ref('')
const profileImage = ref('')
const imageFile = ref(null)
const updating = ref(false)

const handleImageChange = (e) => {
  const file = e.target.files[0]
  if (!file) return

  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { profileImage.value = ev.target.result }
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

    close()
  } catch (err) {
    console.error('Error al guardar los cambios:', err)
    alert(`Error al guardar los cambios: ${err.message || 'Error desconocido'}`)
  } finally {
    updating.value = false
  }
}

</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white dark:bg-[#0f172a] md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-w-lg md:max-h-[85vh] bg-white dark:bg-[#1e293b] md:rounded-xl shadow-xl flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b dark:border-white/10 pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="close" class="text-[var(--color-primary)] font-medium">Cancelar</button>
        <h2 class="font-semibold text-[var(--color-primary)]">Editar perfil</h2>
        <button type="button" @click="handleSave" :disabled="updating" class="text-[var(--color-primary)] font-semibold disabled:opacity-50">
          {{ updating ? 'Guardando...' : 'Guardar' }}
        </button>
      </header>

      <div class="p-6 overflow-y-auto flex-1 space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Imagen de perfil</label>
          <div class="relative">
            <input type="file" accept="image/*" @change="handleImageChange" class="hidden" id="profile-image-upload" />
            <label
              for="profile-image-upload"
              class="cursor-pointer border-dashed border-2 border-gray-300 dark:border-white/20 p-4 w-full text-center rounded-lg hover:border-gray-400 dark:hover:border-white/40 flex flex-col items-center justify-center"
            >
              <span v-if="!profileImage" class="text-gray-600 dark:text-gray-300">Haz clic para subir una imagen</span>
              <div v-else class="relative w-32 h-32">
                <img :src="profileImage" alt="Imagen de perfil" class="w-full h-full object-cover rounded-full border border-gray-200 dark:border-white/10" />
                <button
                  @click.prevent="profileImage = null"
                  class="absolute top-1 right-1 w-6 h-6 bg-white bg-opacity-75 rounded-full flex items-center justify-center shadow hover:bg-opacity-100"
                  title="Eliminar imagen"
                >
                  <IconX class="w-3.5 h-3.5" />
                </button>
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nombre</label>
          <input v-model="name" type="text" class="w-full border border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white rounded p-2 text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Apellidos</label>
          <input v-model="lastName" type="text" class="w-full border border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white rounded p-2 text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nueva contraseña</label>
          <input v-model="password" type="password" placeholder="Deja en blanco para no cambiarla" class="w-full border border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 rounded p-2 text-sm" />
        </div>
      </div>
    </div>
  </div>
</template>
