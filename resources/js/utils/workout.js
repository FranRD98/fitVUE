export function formatDuration(seconds) {
  if (seconds == null || seconds === '') return '—'
  const total = Math.max(0, Math.round(Number(seconds)))
  const minutes = Math.floor(total / 60)
  const hours = Math.floor(minutes / 60)
  if (hours > 0) return `${hours}h ${minutes % 60}min`
  if (minutes > 0) return `${minutes}min`
  return `${total}s`
}

export function formatClock(totalSeconds) {
  const total = Math.max(0, Math.floor(Number(totalSeconds) || 0))
  const minutes = Math.floor(total / 60)
  const seconds = total % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
}

export function formatSet(set) {
  const weight = set?.weight
  const reps = set?.reps
  const hasWeight = weight !== null && weight !== '' && Number(weight) > 0
  if (hasWeight) return `${weight} kg × ${reps ?? '—'}`
  if (reps !== null && reps !== '') return `${reps} reps`
  return '—'
}

export function setsVolume(sets) {
  return (sets || []).reduce((sum, set) => {
    const weight = Number(set?.weight) || 0
    const reps = Number(set?.reps) || 0
    return sum + weight * reps
  }, 0)
}

export function formatVolume(kg) {
  const value = new Intl.NumberFormat('es-ES', { maximumFractionDigits: 1 }).format(kg || 0)
  return `${value} kg`
}
