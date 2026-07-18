import api from '@/supabase/config'

// Crear guía
export async function createGuide(guideData) {
  await api.post('/guides', guideData)
}

// Obtener todas las guías
export async function getGuides() {
  try {
    const { data } = await api.get('/guides')
    return data
  } catch (error) {
    console.error('Error al obtener guías:', error)
    return []
  }
}

// Obtener todas las guías publicadas
export async function getPublishedGuides() {
  try {
    const { data } = await api.get('/guides/published')
    return data
  } catch (error) {
    console.error('Error al obtener guías:', error)
    return []
  }
}

// Obtener guía por id
export async function getGuidesById(guideId) {
  try {
    const { data } = await api.get(`/guides/${guideId}`)
    return data
  } catch (error) {
    console.error('Error al obtener la guía:', error)
    return []
  }
}

// Crear nueva categoría
export async function createCategory(title) {
  const { data } = await api.post('/guides/categories', { title })
  return data
}

// Obtener categorías
export async function getCategories() {
  try {
    const { data } = await api.get('/guides/categories')
    return data
  } catch (error) {
    console.error('Error al obtener categorías:', error)
    return []
  }
}

// Obtener categorías en uso por guías publicadas
export async function getPublishedGuideCategoriesInUse() {
  try {
    const { data } = await api.get('/guides/categories/in-use')
    return data
  } catch (error) {
    console.error('Error al obtener categorías usadas por guías publicadas:', error)
    return []
  }
}

// Actualizar guía
export async function updateGuide(id, guideData) {
  const { id: _, created, ...cleanData } = guideData

  if (cleanData.id_category && typeof cleanData.id_category === 'string') {
    cleanData.id_category = parseInt(cleanData.id_category)
  }

  await api.patch(`/guides/${id}`, cleanData)
}

// Eliminar guía
export async function deleteGuide(id) {
  await api.delete(`/guides/${id}`)
}

// Actualizar el icono de una categoría
export async function updateGuideCategoryIcon(id, iconFile) {
  const formData = new FormData()
  formData.append('icon', iconFile)

  const { data } = await api.post(`/guides/categories/${id}/icon`, formData)
  return data
}
