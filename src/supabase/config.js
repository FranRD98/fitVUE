import axios from 'axios'

export const TOKEN_KEY = 'fitvue_token'

// Cliente HTTP hacia la API Laravel (sustituye al cliente de Supabase).
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
