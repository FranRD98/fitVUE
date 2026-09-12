<script setup>
import { ref, watch } from 'vue'
import { IconX } from '@tabler/icons-vue'
import MuscleIcon from '@/components/dashboard/pickers/MuscleIcon.vue'

const props = defineProps({
  show: Boolean,
  title: String,
  groups: { type: Array, default: () => [] }, // [{ region, label, items: [{value/id, label/category_name, icon}] }]
  modelValue: { type: Array, default: () => [] }, // selected values
  resultsCount: { type: Number, default: 0 }
})
const emit = defineEmits(['close', 'update:modelValue'])

const draft = ref([...props.modelValue])

watch(() => props.show, (open) => {
  if (open) draft.value = [...props.modelValue]
})

function toggle(value) {
  draft.value = draft.value.includes(value)
    ? draft.value.filter(v => v !== value)
    : [...draft.value, value]
  emit('update:modelValue', draft.value)
}

function clearFilters() {
  draft.value = []
  emit('update:modelValue', [])
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-[60] bg-white md:bg-black/60 md:backdrop-blur-sm md:flex md:justify-center md:items-center md:px-4">
    <div class="w-full h-full md:h-auto md:max-h-[85vh] md:max-w-lg md:rounded-2xl bg-white flex flex-col overflow-hidden">
      <header class="flex items-center justify-between px-4 py-3 border-b pt-[calc(env(safe-area-inset-top)+0.75rem)] md:pt-3 shrink-0">
        <button type="button" @click="emit('close')" class="text-gray-500 hover:text-gray-700" aria-label="Cerrar">
          <IconX class="w-6 h-6" />
        </button>
        <h2 class="font-semibold text-[var(--color-primary)]">{{ title }}</h2>
        <span class="w-6"></span>
      </header>

      <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
        <div v-for="group in groups" :key="group.region ?? 'equipment'">
          <h3 v-if="group.label" class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">
            {{ group.label }}
          </h3>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="item in group.items"
              :key="item.value ?? item.id"
              type="button"
              @click="toggle(item.value ?? item.id)"
              class="flex items-center gap-3 border rounded-xl px-3 py-3 text-left transition"
              :class="draft.includes(item.value ?? item.id)
                ? 'border-[var(--color-primary)] bg-[rgba(var(--color-primary-rgb),0.08)]'
                : 'border-gray-200 hover:border-gray-300'"
            >
              <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                <component :is="item.icon" v-if="item.icon" class="w-5 h-5 text-gray-600" />
                <MuscleIcon v-else :name="item.category_name" :size="28" />
              </div>
              <span class="text-sm font-medium text-gray-700 flex-1">{{ item.label || item.category_name }}</span>
            </button>
          </div>
        </div>
      </div>

      <footer class="border-t px-4 py-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] shrink-0 flex items-center justify-between gap-3">
        <button type="button" @click="clearFilters" class="text-sm font-semibold text-gray-500 hover:text-gray-700">
          Eliminar filtros
        </button>
        <button
          type="button"
          @click="emit('close')"
          class="flex-1 bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-2.5 rounded-lg"
        >
          Mostrar {{ resultsCount }} resultados
        </button>
      </footer>
    </div>
  </div>
</template>
