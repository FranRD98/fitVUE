// RIR (Repeticiones en Reserva): cuántas repeticiones más podrías hacer antes
// de fallar. "F" es ir al fallo muscular (0 repeticiones en reserva, pero se
// distingue de "0" porque implica fallo técnico, no solo estar muy cerca).
export const RIR_OPTIONS = [
  { value: 'F', label: 'RIR F (Fallo)' },
  { value: '0', label: 'RIR 0' },
  { value: '1', label: 'RIR 1' },
  { value: '2', label: 'RIR 2' },
  { value: '3', label: 'RIR 3' },
  { value: '4', label: 'RIR 4' },
]

export function formatRir(rir) {
  if (rir === null || rir === undefined || rir === '') return null
  return rir === 'F' ? 'RIR F (Fallo)' : `RIR ${rir}`
}
