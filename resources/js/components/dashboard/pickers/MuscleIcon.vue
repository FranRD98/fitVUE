<script setup>
import { computed } from 'vue'
import { IconHeartbeat, IconRun, IconDots } from '@tabler/icons-vue'

const props = defineProps({
  name: String,
  size: { type: Number, default: 22 }
})

// Siluetas muy simplificadas (vista frontal/trasera) usadas como "mapa corporal"
// para cada grupo muscular, al estilo de apps como Hevy. Cada parte del cuerpo
// tiene una forma fija; el músculo activo se resalta encima en color primario.
const FRONT_PARTS = {
  head: { shape: 'circle', cx: 50, cy: 20, r: 13 },
  neck: { shape: 'rect', x: 44, y: 31, w: 12, h: 9, rx: 2 },
  shoulder_l: { shape: 'circle', cx: 24, cy: 46, r: 9 },
  shoulder_r: { shape: 'circle', cx: 76, cy: 46, r: 9 },
  chest: { shape: 'rect', x: 34, y: 42, w: 32, h: 26, rx: 4 },
  bicep_l: { shape: 'rect', x: 12, y: 48, w: 13, h: 30, rx: 5 },
  bicep_r: { shape: 'rect', x: 75, y: 48, w: 13, h: 30, rx: 5 },
  forearm_l: { shape: 'rect', x: 10, y: 80, w: 13, h: 32, rx: 5 },
  forearm_r: { shape: 'rect', x: 77, y: 80, w: 13, h: 32, rx: 5 },
  abs: { shape: 'rect', x: 38, y: 70, w: 24, h: 30, rx: 4 },
  hip: { shape: 'rect', x: 34, y: 100, w: 32, h: 16, rx: 4 },
  abductor_l: { shape: 'rect', x: 26, y: 112, w: 10, h: 26, rx: 4 },
  abductor_r: { shape: 'rect', x: 64, y: 112, w: 10, h: 26, rx: 4 },
  adductor: { shape: 'rect', x: 46, y: 118, w: 8, h: 46, rx: 3 },
  quad_l: { shape: 'rect', x: 32, y: 120, w: 16, h: 48, rx: 5 },
  quad_r: { shape: 'rect', x: 52, y: 120, w: 16, h: 48, rx: 5 },
  shin_l: { shape: 'rect', x: 33, y: 170, w: 14, h: 40, rx: 5 },
  shin_r: { shape: 'rect', x: 53, y: 170, w: 14, h: 40, rx: 5 },
}

const BACK_PARTS = {
  head: FRONT_PARTS.head,
  neck: FRONT_PARTS.neck,
  shoulder_l: FRONT_PARTS.shoulder_l,
  shoulder_r: FRONT_PARTS.shoulder_r,
  trap: { shape: 'rect', x: 36, y: 34, w: 28, h: 16, rx: 4 },
  upperback: { shape: 'rect', x: 34, y: 48, w: 32, h: 22, rx: 4 },
  lat_l: { shape: 'rect', x: 24, y: 52, w: 12, h: 32, rx: 5 },
  lat_r: { shape: 'rect', x: 64, y: 52, w: 12, h: 32, rx: 5 },
  lowerback: { shape: 'rect', x: 36, y: 70, w: 28, h: 20, rx: 4 },
  tricep_l: { shape: 'rect', x: 12, y: 48, w: 13, h: 30, rx: 5 },
  tricep_r: { shape: 'rect', x: 75, y: 48, w: 13, h: 30, rx: 5 },
  forearm_l: FRONT_PARTS.forearm_l,
  forearm_r: FRONT_PARTS.forearm_r,
  glutes: { shape: 'rect', x: 32, y: 96, w: 36, h: 22, rx: 6 },
  hamstring_l: { shape: 'rect', x: 32, y: 120, w: 16, h: 48, rx: 5 },
  hamstring_r: { shape: 'rect', x: 52, y: 120, w: 16, h: 48, rx: 5 },
  calf_l: FRONT_PARTS.shin_l,
  calf_r: FRONT_PARTS.shin_r,
}

const MUSCLE_MAP = {
  'Abdominales': { view: 'front', keys: ['abs'] },
  'Antebrazos': { view: 'front', keys: ['forearm_l', 'forearm_r'] },
  'Bíceps': { view: 'front', keys: ['bicep_l', 'bicep_r'] },
  'Cuello': { view: 'front', keys: ['neck'] },
  'Dorsales': { view: 'back', keys: ['lat_l', 'lat_r'] },
  'Espalda baja': { view: 'back', keys: ['lowerback'] },
  'Espalda alta': { view: 'back', keys: ['upperback'] },
  'Hombros': { view: 'front', keys: ['shoulder_l', 'shoulder_r'] },
  'Pecho': { view: 'front', keys: ['chest'] },
  'Trapecio': { view: 'back', keys: ['trap'] },
  'Tríceps': { view: 'back', keys: ['tricep_l', 'tricep_r'] },
  'Abductores': { view: 'front', keys: ['abductor_l', 'abductor_r'] },
  'Aductores': { view: 'front', keys: ['adductor'] },
  'Cuádriceps': { view: 'front', keys: ['quad_l', 'quad_r'] },
  'Glúteos': { view: 'back', keys: ['glutes'] },
  'Isquiotibiales': { view: 'back', keys: ['hamstring_l', 'hamstring_r'] },
  'Gemelos': { view: 'back', keys: ['calf_l', 'calf_r'] },
}

const SPECIAL_ICONS = {
  'Cardio': IconHeartbeat,
  'Cuerpo entero': IconRun,
  'Otro': IconDots,
}

const specialIcon = computed(() => SPECIAL_ICONS[props.name])

const entry = computed(() => MUSCLE_MAP[props.name])
const parts = computed(() => (entry.value?.view === 'back' ? BACK_PARTS : FRONT_PARTS))
const baseList = computed(() => Object.entries(parts.value))
const highlightList = computed(() =>
  (entry.value?.keys || []).map(k => parts.value[k]).filter(Boolean)
)
</script>

<template>
  <component :is="specialIcon" v-if="specialIcon" :size="size" :stroke-width="1.75" />
  <svg v-else-if="entry" :width="size" :height="size" viewBox="0 0 100 220">
    <template v-for="([key, part]) in baseList" :key="key">
      <circle v-if="part.shape === 'circle'" :cx="part.cx" :cy="part.cy" :r="part.r" fill="#d1d5db" />
      <rect v-else :x="part.x" :y="part.y" :width="part.w" :height="part.h" :rx="part.rx" fill="#d1d5db" />
    </template>
    <template v-for="(part, i) in highlightList" :key="'hl-'+i">
      <circle v-if="part.shape === 'circle'" :cx="part.cx" :cy="part.cy" :r="part.r" fill="var(--color-primary)" />
      <rect v-else :x="part.x" :y="part.y" :width="part.w" :height="part.h" :rx="part.rx" fill="var(--color-primary)" />
    </template>
    <!-- Detalle de cara/nuca para distinguir de un vistazo la vista frontal de la trasera -->
    <template v-if="entry.view === 'front'">
      <circle cx="45" cy="18" r="1.6" fill="white" />
      <circle cx="55" cy="18" r="1.6" fill="white" />
    </template>
    <line v-else x1="50" y1="10" x2="50" y2="30" stroke="white" stroke-width="2" />
  </svg>
  <!-- Nombre sin icono dedicado (categorías legacy no incluidas en el listado canónico) -->
  <span v-else class="text-sm font-bold text-gray-500">{{ (name || '?')[0] }}</span>
</template>
