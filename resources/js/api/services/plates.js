import api from '@/api/client'

// Obtener todos los platos con los ingredientes completos (el backend ya los hidrata)
export async function getPlates(uid = null) {
  try {
    const { data } = await api.get('/plates', { params: uid ? { created_by: uid } : {} })
    return data
  } catch (error) {
    console.error('Error al obtener platos:', error)
    return []
  }
}

export function getMacros(plate) {
  // Si es un plato con ingredientes
  if (plate?.items?.length > 0 && plate.items[0]?.ingredient) {
    return {
      calories: plate.items.reduce((total, item) => {
        if (!item.ingredient || item.quantity == null) return total
        return total + (parseFloat(item.ingredient.calories || 0) * item.quantity) / 100
      }, 0).toFixed(1),

      protein: plate.items.reduce((total, item) => {
        if (!item.ingredient || item.quantity == null) return total
        return total + (parseFloat(item.ingredient.protein || 0) * item.quantity) / 100
      }, 0).toFixed(1),

      carbs: plate.items.reduce((total, item) => {
        if (!item.ingredient || item.quantity == null) return total
        return total + (parseFloat(item.ingredient.carbs || 0) * item.quantity) / 100
      }, 0).toFixed(1),

      fats: plate.items.reduce((total, item) => {
        if (!item.ingredient || item.quantity == null) return total
        return total + (parseFloat(item.ingredient.fats || 0) * item.quantity) / 100
      }, 0).toFixed(1)
    }
  }

  // Si es un plato simple directo (por macros planos)
  return {
    calories: parseFloat(plate.calories) || 0,
    protein: parseFloat(plate.protein) || 0,
    carbs: parseFloat(plate.carbs) || 0,
    fats: parseFloat(plate.fats) || 0
  }
}

// Crear nuevo plato
export async function createPlate(plateData) {
  await api.post('/plates', plateData)
}

// Actualizar plato
export async function updatePlate(id, plateData) {
  await api.patch(`/plates/${id}`, plateData)
}

// Eliminar plato
export async function deletePlate(id) {
  await api.delete(`/plates/${id}`)
}
