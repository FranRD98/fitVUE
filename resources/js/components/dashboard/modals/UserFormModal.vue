<script setup>
import { ref, watch, onMounted } from 'vue'
import { createUserByCoach, getAllCoaches, updateUser } from '@/api/services/users.js'
import { getRoutines } from '@/api/services/routines.js'
import { getDiets } from '@/api/services/diets.js'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const allCoaches = ref([])

const props = defineProps({ show: Boolean, initialData: Object })
const emit = defineEmits(['close', 'saved'])

const isEditing = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  last_name: '',
  email: '',
  password: '',
  role: 'user',
  plan_id: 1,
  assigned_routine: null,
  assigned_diet: null
})

const routines = ref([])
const diets = ref([])

onMounted(async () => {
  if (userStore.userData?.role === 'admin') {
    try {
      allCoaches.value = await getAllCoaches()
    } catch (err) {
      console.error('Error cargando coaches:', err)
    }
  }

  if (userStore.userData?.role === 'coach') {
    try {
      routines.value = await getRoutines()
      diets.value = await getDiets(userStore.userData.uid)
    } catch (error) {
      console.error('Error cargando rutinas o dietas:', error)
    }
  }
})

watch(
  () => props.initialData,
  (val) => {
    if (val) {
      isEditing.value = true
      form.value = {
        name: val.name || '',
        last_name: val.last_name || '',
        email: val.email || '',
        role: val.role || 'user',
        plan_id: val.plan_id || 1,
        password: '',
        assigned_routine: val.assigned_routine || null,
        assigned_diet: val.assigned_diet || null,
        coach_uid: val.coach_uid || null
      }
    } else {
      isEditing.value = false
      form.value = {
        name: '',
        last_name: '',
        email: '',
        password: '',
        role: 'user',
        plan_id: 1,
        assigned_routine: null,
        assigned_diet: null,
        coach_uid: null
      }
    }
  },
  { immediate: true }
)

const handleSubmit = async () => {
  formError.value = ''

  try {
    const payload = {
      email: form.value.email,
      name: form.value.name,
      last_name: form.value.last_name,
      coach_uid: form.value.coach_uid || userStore.userData.uid,
      assigned_diet: form.value.assigned_diet || null,
      assigned_routine_by_coach: form.value.assigned_routine || null,
    }

    if (isEditing.value) {
      await updateUser(props.initialData.uid, payload)
    } else {
      if (!form.value.password) {
        formError.value = 'La contraseña es obligatoria para crear un usuario.'
        return
      }
      payload.password = form.value.password
      await createUserByCoach(payload)
    }

    emit('saved')
    emit('close')
  } catch (err) {
    console.error(err)
    formError.value = err.response?.data?.message || 'Error inesperado al guardar el usuario'
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-[rgba(0,0,0,0.6)] backdrop-blur-sm flex justify-center items-center z-50 px-4">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full relative">
      <button @click="emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 transition" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>

      <h2 class="text-xl font-bold text-[var(--color-primary)] mb-4">
        {{ isEditing ? (userStore.userData.role === 'coach' ? 'Editar Cliente' : 'Editar Usuario') : (userStore.userData.role === 'coach' ? 'Nuevo Cliente' : 'Nuevo Usuario') }}
      </h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <label for="name" class="text-sm font-medium text-gray-700">Nombre</label>
        <input id="name" v-model="form.name" placeholder="Nombre" class="input" required />

        <label for="last_name" class="text-sm font-medium text-gray-700">Apellidos</label>
        <input id="last_name" v-model="form.last_name" placeholder="Apellidos" class="input" required />

        <label for="email" class="text-sm font-medium text-gray-700">Email</label>
        <input id="email" v-model="form.email" type="email" placeholder="Email" class="input" required />

        <label v-if="!isEditing" for="password" class="text-sm font-medium text-gray-700">Contraseña</label>
        <input v-if="!isEditing" id="password" v-model="form.password" type="password" placeholder="Contraseña" class="input" required />

        <template v-if="userStore.userData.role === 'admin'">
          <label for="role" class="text-sm font-medium text-gray-700">Rol</label>
          <select id="role" v-model="form.role" class="input">
            <option value="user">Usuario</option>
            <option value="coach">Coach</option>
            <option value="admin">Admin</option>
          </select>

          <label for="plan" class="text-sm font-medium text-gray-700">Plan</label>
          <select id="plan" v-model="form.plan_id" class="input">
            <option value="1">Free</option>
            <option value="2">Premium</option>
            <option value="3">Pro</option>
          </select>

          <label for="coach_uid" class="text-sm font-medium text-gray-700">Asignar coach</label>
          <select id="coach_uid" v-model="form.coach_uid" class="input">
            <option :value="null">Ninguno</option>
            <option
              v-for="coach in allCoaches"
              :key="coach.uid"
              :value="coach.uid"
            >
              {{ coach.name }} {{ coach.last_name }} ({{ coach.email }})
            </option>
          </select>
        </template>

        <template v-else-if="userStore.userData.role === 'coach'">
          <label for="assigned_routine" class="text-sm font-medium text-gray-700">Rutina asignada</label>
          <select id="assigned_routine" v-model="form.assigned_routine" class="input">
            <option :value="null">Ninguna</option>
            <option v-for="routine in routines" :key="routine.id" :value="routine.id">
              {{ routine.title }}
            </option>
          </select>

          <label for="assigned_diet" class="text-sm font-medium text-gray-700">Dieta asignada</label>
          <select id="assigned_diet" v-model="form.assigned_diet" class="input">
            <option :value="null">Ninguna</option>
            <option v-for="diet in diets" :key="diet.id" :value="diet.id">
              {{ diet.title || 'Dieta #' + diet.id }}
            </option>
          </select>
        </template>

        <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>

        <div class="flex justify-end mt-4">
          <button type="submit" class="bg-[var(--color-primary)] text-white px-4 py-2 rounded hover:bg-[var(--color-secondary)]">
            {{ isEditing ? 'Guardar cambios' : 'Crear usuario' }}
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
</style>
