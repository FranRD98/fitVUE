import api from '@/api/client'

// Obtener todos los ingredientes
export async function getIngredients(userId) {
  try {
    const { data } = await api.get('/ingredients', { params: { user_id: userId } })
    return data
  } catch (error) {
    console.error('Error al obtener ingredientes:', error)
    return []
  }
}

// Obtener datos de un ingrediente
export async function getIngredientById(id) {
  try {
    const { data } = await api.get(`/ingredients/${id}`)
    return data
  } catch (error) {
    console.error('Error al obtener ingrediente:', error)
    return []
  }
}

// Crear nuevo ingrediente
export async function createIngredient(data) {
  await api.post('/ingredients', data)
}

// Actualizar ingrediente
export async function updateIngredient(id, data) {
  await api.patch(`/ingredients/${id}`, data)
}

// Eliminar ingrediente
export async function deleteIngredient(id) {
  await api.delete(`/ingredients/${id}`)
}

// Verifica si un ingrediente está siendo usado en algún plato
export async function isIngredientUsed(ingredientId) {
  const { data } = await api.get(`/ingredients/${ingredientId}/used`)
  return data.used
}
