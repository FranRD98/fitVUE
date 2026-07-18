import api from '@/supabase/config'

// Crear una revisión
export async function createReview(data) {
  await api.post('/reviews', data)
}

// Actualizar una revisión
export async function updateReview(id, updatedData) {
  await api.patch(`/reviews/${id}`, updatedData)
}

// Eliminar una revisión
export async function deleteReview(id) {
  await api.delete(`/reviews/${id}`)
}

// Obtener todas las revisiones por uid
export async function getReviewsById(userId) {
  const { data } = await api.get('/reviews', { params: { user_id: userId } })
  return data
}

// Obtener una revisión por uid y id
export async function getReviewById(uid, reviewId) {
  try {
    const { data } = await api.get(`/reviews/${reviewId}`, { params: { user_id: uid } })
    return data
  } catch (error) {
    console.error('Error al obtener revisión por UID y fecha:', error)
    return null
  }
}
