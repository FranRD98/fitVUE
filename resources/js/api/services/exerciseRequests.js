import api from '@/api/client'

// Solicitar un ejercicio que no existe en el catálogo (cualquier usuario)
export async function requestExercise(name, description) {
  const { data } = await api.post('/exercise-requests', { name, description })
  return data
}

// Listado de solicitudes (solo admin)
export async function getExerciseRequests() {
  const { data } = await api.get('/exercise-requests')
  return data
}

// Aprobar una solicitud: crea el ejercicio real en el catálogo (solo admin)
export async function approveExerciseRequest(id, exerciseData) {
  const { data } = await api.post(`/exercise-requests/${id}/approve`, exerciseData)
  return data
}

// Rechazar una solicitud (solo admin)
export async function rejectExerciseRequest(id) {
  const { data } = await api.post(`/exercise-requests/${id}/reject`)
  return data
}
