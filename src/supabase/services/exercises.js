import api from '@/supabase/config'

// Obtener todos los ejercicios
export async function getExercises(userId) {
  try {
    const { data } = await api.get('/exercises', { params: { user_id: userId } })
    return data
  } catch (error) {
    console.error('Error al obtener los ejercicios:', error)
    return []
  }
}

export async function getExerciseById(id) {
  const { data } = await api.get(`/exercises/${id}`)
  return data
}

// Obtener categorías de ejercicios
export async function getExerciseCategories() {
  const { data } = await api.get('/exercises/categories')
  return data
}

// Crear ejercicio
export async function createExercise(exerciseData) {
  const { name, description, id_category, image, created_by } = exerciseData

  const { data } = await api.post('/exercises', { name, description, id_category, image, created_by })
  return data
}

// Actualizar ejercicio
export async function updateExercise(exerciseId, exerciseData) {
  const { name, description, id_category, image, image_url } = exerciseData

  const { data } = await api.patch(`/exercises/${exerciseId}`, {
    name, description, id_category, image: image ?? image_url,
  })

  return data
}

// Eliminar un ejercicio
export async function deleteExercise(exerciseId) {
  await api.delete(`/exercises/${exerciseId}`)
}

export async function getLastExerciseProgress(exerciseId, userId) {
  try {
    const { data } = await api.get('/exercises-progress/last', { params: { exercise_id: exerciseId, user_id: userId } })
    return data
  } catch (error) {
    console.error(error)
    return null
  }
}

export async function saveExerciseProgress(userId, routineId, day, exerciseInputs) {
  const exercises = exerciseInputs.filter((exercise) => exercise.exerciseId && exercise.sets?.length)

  if (!exercises.length) {
    console.warn('No hay sets válidos para guardar.')
    return
  }

  await api.post('/exercises-progress', {
    user_id: userId,
    id_routine: routineId,
    day,
    exercises,
  })
}

export async function getExerciseHistory(exerciseId, userId) {
  try {
    const { data } = await api.get('/exercises-progress/history', { params: { exercise_id: exerciseId, user_id: userId } })
    return data
  } catch (error) {
    console.error('Error al obtener el historial de ejercicios:', error)
    return []
  }
}
