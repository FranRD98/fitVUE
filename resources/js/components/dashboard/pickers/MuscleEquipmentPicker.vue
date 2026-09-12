<script setup>
import { computed } from 'vue'
import { IconX, IconCheck } from '@tabler/icons-vue'
import MuscleIcon from '@/components/dashboard/pickers/MuscleIcon.vue'

const props = defineProps({
  show: Boolean,
  title: String,
  multiple: { type: Boolean, default: false },
  // Equipamiento: [{ value, label, icon }]. Músculos: [{ region, label, items:[{id, category_name}] }]
  groups: { type: Array, default: () => [] },
  modelValue: { type: [String, Number, Array], default: null }
})
const emit = defineEmits(['close', 'update:modelValue'])

const selected = computed(() =>
  props.multiple ? (Array.isArray(props.modelValue) ? props.modelValue : []) : props.modelValue
)

function isSelected(value) {
  return props.multiple ? selected.value.includes(value) : selected.value === value
}

function choose(value) {
  if (props.multiple) {
    const current = selected.value
    const next = current.includes(value) ? current.filter(v => v !== value) : [...current, value]
    emit('update:modelValue', next)
    return
  }

  emit('update:modelValue', value)
  emit('close')
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
              @click="choose(item.value ?? item.id)"
              class="flex items-center gap-3 border rounded-xl px-3 py-3 text-left transition"
              :class="isSelected(item.value ?? item.id)
                ? 'border-[var(--color-primary)] bg-[rgba(var(--color-primary-rgb),0.08)]'
                : 'border-gray-200 hover:border-gray-300'"
            >
              <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                <component :is="item.icon" v-if="item.icon" class="w-5 h-5 text-gray-600" />
                <MuscleIcon v-else :name="item.category_name" :size="28" />
              </div>
              <span class="text-sm font-medium text-gray-700 flex-1">{{ item.label || item.category_name }}</span>
              <IconCheck v-if="isSelected(item.value ?? item.id)" class="w-4 h-4 text-[var(--color-primary)] shrink-0" />
            </button>
          </div>
        </div>
      </div>

      <footer v-if="multiple" class="border-t px-4 py-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] shrink-0">
        <button
          type="button"
          @click="emit('close')"
          class="w-full bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-2.5 rounded-lg"
        >
          Hecho
        </button>
      </footer>
    </div>
  </div>
</template>
