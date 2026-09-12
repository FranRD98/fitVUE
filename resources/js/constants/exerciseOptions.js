import {
  IconBodyScan, IconStretching, IconStretching2, IconBarbell, IconDisc,
  IconWeight, IconBuildingWarehouse, IconBallAmericanFootball, IconDots
} from '@tabler/icons-vue'

// Lista fija de equipamiento (coincide con el enum `equipment` de `exercises`).
export const EQUIPMENT_OPTIONS = [
  { value: 'ninguno', label: 'Ninguno', icon: IconBodyScan },
  { value: 'banda_resistencia', label: 'Banda de Resistencia', icon: IconStretching },
  { value: 'banda_suspension', label: 'Banda de Suspensión', icon: IconStretching2 },
  { value: 'barra', label: 'Barra', icon: IconBarbell },
  { value: 'disco', label: 'Disco', icon: IconDisc },
  { value: 'mancuerna', label: 'Mancuerna', icon: IconWeight },
  { value: 'maquina', label: 'Máquina', icon: IconBuildingWarehouse },
  { value: 'pesa_rusa', label: 'Pesa Rusa', icon: IconBallAmericanFootball },
  { value: 'otro', label: 'Otro', icon: IconDots }
]

export function equipmentLabel(value) {
  return EQUIPMENT_OPTIONS.find(o => o.value === value)?.label || 'Sin equipamiento'
}

export const MUSCLE_REGION_LABELS = {
  superior: 'Cuerpo Superior',
  inferior: 'Cuerpo Inferior',
  otros: 'Otros'
}

export const MUSCLE_REGION_ORDER = ['superior', 'inferior', 'otros']

// Agrupa las categorías (grupos musculares) devueltas por la API por región,
// en el orden Cuerpo Superior / Cuerpo Inferior / Otros.
export function groupMusclesByRegion(categories) {
  return MUSCLE_REGION_ORDER
    .map(region => ({
      region,
      label: MUSCLE_REGION_LABELS[region],
      items: categories.filter(c => (c.region || 'otros') === region)
    }))
    .filter(group => group.items.length)
}
