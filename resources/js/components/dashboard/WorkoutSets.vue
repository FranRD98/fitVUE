<script setup>
import { formatSet } from '@/utils/workout'

defineProps({
  exercises: { type: Array, default: () => [] }
})
</script>

<template>
  <div class="space-y-3">
    <div v-for="(exercise, i) in exercises" :key="i">
      <p class="text-sm font-semibold text-gray-800">{{ exercise.name }}</p>
      <ul class="mt-1 space-y-0.5">
        <li
          v-for="(set, j) in exercise.sets"
          :key="j"
          class="text-sm text-gray-500 flex items-center justify-between gap-3"
        >
          <span>Serie {{ j + 1 }}</span>
          <span class="tabular-nums">{{ formatSet(set) }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>
