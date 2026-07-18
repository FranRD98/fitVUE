import api from '@/supabase/config'

// Obtener todos los usuarios (el backend filtra automáticamente por rol: coach ve solo sus clientes)
export async function getUsers(coachUid = null) {
  const { data } = await api.get('/users', { params: coachUid ? { coach_uid: coachUid } : {} })
  return data
}

// Obtener todos los usuarios con rol "coach"
export async function getAllCoaches() {
  const { data } = await api.get('/users/coaches')
  return data
}

// Crear usuario desde panel de coach
export async function createUserByCoach(userData) {
  const { data } = await api.post('/users', userData)
  return data
}

// Actualizar datos de users
export async function updateUser(uid, userData) {
  const payload = { ...userData }
  if (!payload.password) delete payload.password

  await api.patch(`/users/${uid}`, payload)
  return { success: true }
}

// Eliminar usuario
export async function deleteUser(uid) {
  await api.delete(`/users/${uid}`)
}

/* USERS PROFILE*/
export async function uploadProfileImage(file, userId) {
  const formData = new FormData()
  formData.append('image', file)

  const { data } = await api.post(`/users/${userId}/profile-image`, formData)
  return data.url
}

export async function updateUserData(userId, updates) {
  const payload = { ...updates }
  if ('completedForm' in payload) {
    payload.completed_form = payload.completedForm
    delete payload.completedForm
  }

  await api.patch(`/users/${userId}`, payload)
}
