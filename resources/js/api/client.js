import axios from 'axios'

export const TOKEN_KEY = 'fitvue_token'

// Cliente HTTP hacia la API Laravel. Al vivir en el mismo proyecto/origen, basta
// con una ruta relativa; VITE_API_URL solo hace falta si algún día se separan.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
