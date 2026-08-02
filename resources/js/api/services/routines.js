import api from '@/api/client'

// Crear nueva rutina
export async function createRoutine(routineData) {
  const { data } = await api.post('/routines', routineData)
  return data
}

// Obtener todas las rutinas, opcionalmente filtradas
export async function getRoutines(category) {
  const { data } = await api.get('/routines', { params: category ? { category } : {} })
  return data
}

// Obtener solo rutinas publicadas
export async function getPublishedRoutines() {
  try {
    const { data } = await api.get('/routines/published')
    return data
  } catch (error) {
    console.error('Error al obtener rutinas publicadas:', error)
    return []
  }
}

// Obtener categorias de rutinas publicadas
export async function getPublishedRoutineCategoriesInUse() {
  try {
    const { data } = await api.get('/routines/categories/in-use')
    return data
  } catch (error) {
    console.error('Error al obtener categorías en uso:', error)
    return []
  }
}

export async function getRoutinesByUser(uid) {
  const { data } = await api.get(`/users/${uid}/routines`)
  return data
}

// Obtener categorías de rutinas
export async function getRoutineCategories() {
  const { data } = await api.get('/routines/categories')
  return data
}

export async function getRoutineById(id) {
  const { data } = await api.get(`/routines/${id}`)
  return data
}

// Obtener rutina asignada actual
export async function getAssignedRoutine(uid) {
  const { data } = await api.get(`/users/${uid}/assigned-routine`)
  return data
}

// Obtener rutina asignada del coach
export async function getCoachAssignedRoutine(uid) {
  const { data } = await api.get(`/users/${uid}/coach-assigned-routine`)
  return data
}

// Actualizar una rutina existente
export async function updateRoutine(id, routineData) {
  const { data } = await api.patch(`/routines/${id}`, {
    title: routineData.title,
    description: routineData.description,
    id_category: routineData.id_category,
    days: routineData.days,
    published: routineData.published,
  })

  return data
}

// Crear nueva categoría de rutina (evita duplicados)
export async function createRoutineCategory(title) {
  const { data } = await api.post('/routines/categories', { title })
  return data
}

// Asignar rutina a usuario
export async function assignRoutineToUser(uid, routineId) {
  await api.post(`/users/${uid}/assign-routine`, { routine_id: routineId })
}

// Quitar rutina asignada
export async function unassignRoutineFromUser(uid) {
  await api.delete(`/users/${uid}/assign-routine`)
}

// Eliminar rutina
export async function deleteRoutine(id) {
  await api.delete(`/routines/${id}`)
}
