<script setup>
import { ref, onMounted, computed } from 'vue'
import { getUsers, deleteUser } from '@/supabase/services/users.js'
import UserFormModal from '@/components/dashboard/modals/UserFormModal.vue'

const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const selectedUser = ref(null)

const searchQuery = ref('')
const selectedPlan = ref('')
const selectedRole = ref('')

import {
  IconPlus,
  IconLayoutGrid,
  IconLayoutList,
  IconTrash
} from '@tabler/icons-vue'


const loadUsers = async () => {
  loading.value = true
  try {
    users.value = await getUsers()
  } catch (error) {
    console.error('Error al obtener usuarios:', error)
  } finally {
    loading.value = false
  }
}

onMounted(loadUsers)

const openCreateModal = () => {
  selectedUser.value = null
  showModal.value = true
}

const openEditModal = (user) => {
  selectedUser.value = user
  showModal.value = true
}

const handleDelete = async (user) => {
  if (confirm(`¿Seguro que quieres eliminar a ${user.name}?`)) {
    await deleteUser(user.id)
    await loadUsers()
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch =
      user.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.last_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email?.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesPlan = selectedPlan.value ? user.suscriptionPlan === selectedPlan.value : true
    const matchesRole = selectedRole.value ? user.role === selectedRole.value : true

    return matchesSearch && matchesPlan && matchesRole
  })
})
</script>

<template>
  <section>
    <!-- Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
      <h1 class="text-3xl font-bold text-[var(--color-primary)]">Usuarios</h1>
      <button @click="openCreateModal" class="flex items-center gap-2 bg-[var(--color-primary)] text-white px-4 py-2 rounded-lg shadow hover:bg-[var(--color-secondary)] transition">
        <IconPlus class="w-5 h-5"/>
        Nuevo usuario
      </button>
    </div>

    <!-- Modal -->
    <UserFormModal
      :show="showModal"
      :initialData="selectedUser"
      @close="showModal = false"
      @saved="loadUsers"
    />

    <!-- Filtros -->
<div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4 w-full">
    
      <!-- Buscador -->
    <div class="flex-1">
      <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Buscar</label>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Correo, nombre..."
        class="w-full border border-gray-300 rounded p-2 text-sm text-gray-700 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
      />
    </div>

    <!-- Plan -->
    <div>
      <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Plan</label>
      <select
        v-model="selectedPlan"
        class="w-full border border-gray-300 p-2 rounded text-sm text-gray-700 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
      >
        <option value="">Todos</option>
        <option value="free">Free</option>
        <option value="premium">Premium</option>
      </select>
    </div>

    <!-- Rol -->
    <div>
      <label class="block text-sm font-medium text-[var(--color-primary)] mb-1">Rol</label>
      <select
        v-model="selectedRole"
        class="w-full border border-gray-300 p-2 rounded text-sm text-gray-700 focus:outline-none focus:border-[var(--color-primary)] focus:ring-1 focus:ring-[var(--color-primary)] transition-all"
      >
        <option value="">Todos</option>
        <option value="user">Usuario</option>
        <option value="coach">Coach</option>
        <option value="admin">Admin</option>
      </select>
    </div>
</div>


    <!-- Tabla -->
     <div v-if="filteredUsers.length" class="overflow-x-auto">

      <table class="min-w-[800px] w-full text-left text-sm">
        <thead class="bg-gray-200 text-gray-600 font-medium">
          <tr>
            <th class="py-3 px-2">Nombre</th>
            <th class="px-2">Email</th>
            <th class="px-2">Creado</th>
            <th class="px-2">Plan</th>
            <th class="px-2">Rol</th>
            <th class="px-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="user in filteredUsers"
            :key="user.id"
            class="border-t border-gray-200 hover:bg-gray-100 transition"
            @click="openEditModal(user)"
          >
            <td class="py-3 px-2 font-semibold text-[var(--color-primary)] whitespace-nowrap">
              {{ user.name }} {{ user.last_name }}
            </td>
            <td class="py-3 px-2 whitespace-nowrap">{{ user.email }}</td>
            <td class="py-3 px-2 whitespace-nowrap">{{ new Date(user.created_at).toLocaleDateString() || '—' }}</td>
            <td class="py-3 px-2 capitalize">{{ user.plan_id || '—' }}</td>
            <td class="py-3 px-2 capitalize">{{ user.role || '—' }}</td>
            <td class="py-3 px-2 text-right">
              <button
                @click.prevent.stop="handleDelete(user)"
                class="text-red-600 hover:bg-red-600 hover:text-white p-2 rounded-full transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-300"
                title="Eliminar"
              >
                <IconTrash class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
     </div>

    <!-- Sin resultados -->
    <div v-else class="text-center text-gray-500 mt-10 px-4">No se encontraron usuarios con esos criterios.</div>
  </section>
</template>
