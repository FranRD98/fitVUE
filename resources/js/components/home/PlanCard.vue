<script setup>
  defineProps({
    title: String,
    price: String,
    buttonText: String,
    highlighted: Boolean,
    planId: Number,
    badgeText: {
      type: String,
      default: 'Recomendado'
    },
    subtitle: {
      type: String,
      default: ''
    },
    features: {
      type: Array,
      default: () => []
    }
  })

const emit = defineEmits(['select'])

</script>

<template>
  <div
    class="relative rounded-xl border-2 shadow-lg p-6 transition-all duration-300 flex flex-col justify-between h-full bg-white hover:scale-[1.03]"
    :class="highlighted ? 'border-yellow-400 shadow-2xl' : 'border-[var(--color-primary)]'"
  >
    <!-- Badge superior -->
    <div
      v-if="highlighted"
      class="absolute top-0 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-xs font-bold px-3 py-1 rounded-bl-xl text-white uppercase tracking-wider"
    >
      {{ badgeText }}
    </div>

    <!-- Título y precio -->
    <div class="text-center mt-6">
      <h3 class="text-2xl font-bold text-[var(--color-primary)]">{{ title }}</h3>
      <p class="text-sm text-gray-500 mt-1" v-if="subtitle">{{ subtitle }}</p>
      <p class="text-xl mt-4 font-semibold text-[var(--color-text)]">{{ price }}</p>
    </div>

    <!-- Divider -->
    <hr class="my-6 border-t border-gray-200" />

    <!-- Lista de características -->
    <ul class="text-left space-y-3 text-[var(--color-text)] text-sm">
      <li
        v-for="(feature, i) in features"
        :key="i"
        class="flex items-center"
        :class="{ 'text-gray-400 line-through': !feature.active }"
      >
        <svg
          v-if="feature.active"
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 mr-2 text-[var(--color-primary)]"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 mr-2 text-gray-300"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>

        {{ feature.label }}
      </li>
    </ul>

    <!-- Divider -->
    <hr class="my-6 border-t border-gray-200" />

    <!-- CTA -->
    <button
      @click="$emit('select', planId)"
      class="block w-full text-center bg-[var(--color-primary)] hover:bg-[var(--color-secondary)] text-white font-semibold py-3 rounded-full transition-colors"
    >
      {{ buttonText }}
  </button>
  </div>
</template>
