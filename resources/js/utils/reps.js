// Las repeticiones se guardan como texto libre: "12" (valor exacto) o "6-12" (rango).
const RANGE_PATTERN = /^\s*(\d+)\s*-\s*(\d+)\s*$/

// Valor representativo para prellenar el registro de progreso: el mínimo del
// rango, o el propio número si es un valor exacto.
export function parseRepsTarget(reps) {
  if (reps === null || reps === undefined || reps === '') return null

  const match = String(reps).match(RANGE_PATTERN)
  if (match) return Number(match[1])

  const num = Number(reps)
  return Number.isFinite(num) ? num : null
}

export function isValidReps(reps) {
  if (reps === null || reps === undefined || reps === '') return true
  return RANGE_PATTERN.test(String(reps)) || /^\s*\d+\s*$/.test(String(reps))
}
