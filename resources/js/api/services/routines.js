import api from '@/api/client'

// Laravel devuelve {} (no null) cuando una relación "belongsTo" está vacía;
// lo normalizamos aquí para que el resto del código pueda seguir comprobando
// simplemente `if (resultado)`.
function nullIfEmpty(value) {
  return value && Object.keys(value).length ? value : null
}

// Crear nueva rutina
export async function createRoutine(routineData) {
  const { data } = await api.post('/routines', routineData)
  return data
}

// Obtener todas las rutinas, opcionalmente filtradas (solo admin: incluye el usuario asignado)
export async function getRoutines({ category, userId } = {}) {
  const params = {}
  if (category) params.category = category
  if (userId) params.user_id = userId

  const { data } = await api.get('/routines', { params })
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

// Obtener rutina asignada del coach
export async function getCoachAssignedRoutine(uid) {
  const { data } = await api.get(`/users/${uid}/coach-assigned-routine`)
  return nullIfEmpty(data)
}

// Transferir la propiedad de una rutina propia a un usuario (a diferencia de
// "asignar", que solo enlaza en modo lectura, aquí la rutina pasa a ser suya
// y deja de aparecer en la cuenta de quien la transfiere)
export async function transferRoutineToUser(uid, routineId) {
  const { data } = await api.post(`/users/${uid}/transfer-routine`, { routine_id: routineId })
  return data
}

// Actualizar una rutina existente
export async function updateRoutine(id, routineData) {
  const { data } = await api.patch(`/routines/${id}`, {
    title: routineData.title,
    description: routineData.description,
    id_category: routineData.id_category,
    exercises: routineData.exercises,
    published: routineData.published,
  })

  return data
}

// Duplicar una rutina existente
export async function duplicateRoutine(id) {
  const { data } = await api.post(`/routines/${id}/duplicate`)
  return data
}

// Crear nueva categoría de rutina (evita duplicados)
export async function createRoutineCategory(title) {
  const { data } = await api.post('/routines/categories', { title })
  return data
}

// Eliminar rutina
export async function deleteRoutine(id) {
  await api.delete(`/routines/${id}`)
}
