import { ref } from 'vue'

const welcomeMessages = [
  'Estás haciendo un gran progreso hoy, ¡sigue así! 💪',
  '¡Hoy es un gran día para avanzar en tus metas! 🚀',
  '¡Vamos con todo, estás imparable! 🔥',
  'No te detengas, cada paso cuenta 🏃‍♂️',
  '¡Excelente trabajo, sigue construyendo tu mejor versión! 🛠️',
  'Hoy entrenas el cuerpo… y la disciplina 🧠💪',
  '¡Eres más constante que el WiFi del gimnasio! 📶',
  '¡A romperla! 💥 Tu constancia es tu superpoder.'
]

// Se elige una sola vez por carga de página y se comparte entre la
// cabecera de escritorio y el saludo de "Entrenamiento" en móvil, para
// que no aparezcan dos frases distintas a la vez.
const randomMessage = ref(welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)])

export function useGreeting() {
  return { randomMessage }
}
