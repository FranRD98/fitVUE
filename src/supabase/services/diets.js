import api from '@/supabase/config'

// Obtener todas las dietas de un usuario
export async function getDiets(userId) {
  try {
    const { data } = await api.get('/diets', { params: { user_id: userId } })
    return data
  } catch (error) {
    console.error('Error al obtener las dietas:', error)
    return []
  }
}

// Obtener dieta asignada por el coach
export async function getCoachAssignedDiet(uid) {
  const { data } = await api.get(`/users/${uid}/coach-assigned-diet`)
  return data
}

// Crear nueva dieta
export async function createDiet(payload) {
  const { data } = await api.post('/diets', payload)
  return data
}

// Actualizar dieta
export async function updateDiet(id, payload) {
  await api.patch(`/diets/${id}`, payload)
}

// Eliminar dieta
export async function deleteDiet(id) {
  await api.delete(`/diets/${id}`)
}

// Obtiene toda la información de una dieta en concreto, ya hidratada por el backend
export async function getFullDiet(dietId) {
  const { data } = await api.get(`/diets/${dietId}/full`)
  return withTotals(data)
}

// Hidrata una dieta ya cargada (p. ej. la dieta asignada por el coach) con los datos de sus ingredientes
export async function hydrateDietIngredients(diet) {
  const ingredientIds = new Set()

  diet.meals.forEach((meal) => {
    meal.items.forEach((plate) => {
      plate.items.forEach((item) => {
        if (item.ingredient_id) ingredientIds.add(item.ingredient_id)
      })
    })
  })

  if (!ingredientIds.size) return withTotals(diet)

  const { data: ingredients } = await api.get('/ingredients', { params: { ids: [...ingredientIds].join(',') } })
  const ingredientsMap = Object.fromEntries(ingredients.map((i) => [i.id, i]))

  diet.meals.forEach((meal) => {
    meal.items.forEach((plate) => {
      plate.items = plate.items.map((item) => ({
        ...item,
        ingredient: ingredientsMap[item.ingredient_id] || null,
      }))
    })
  })

  return withTotals(diet)
}

// Calcula los totales de macros de la dieta a partir de sus ingredientes hidratados
function withTotals(diet) {
  const totals = { calories: 0, protein: 0, carbs: 0, fats: 0 }

  diet.meals?.forEach((meal) => {
    meal.items.forEach((plate) => {
      plate.items.forEach((item) => {
        if (!item.ingredient || item.quantity == null) return
        const factor = item.quantity / 100

        totals.calories += (parseFloat(item.ingredient.calories) || 0) * factor
        totals.protein += (parseFloat(item.ingredient.protein) || 0) * factor
        totals.carbs += (parseFloat(item.ingredient.carbs) || 0) * factor
        totals.fats += (parseFloat(item.ingredient.fats) || 0) * factor
      })
    })
  })

  return {
    ...diet,
    totalCalories: totals.calories,
    totalProtein: totals.protein,
    totalCarbs: totals.carbs,
    totalFats: totals.fats,
  }
}
