<template>
  <div
    class="rounded-2xl border border-white/60 dark:border-white/5 bg-white/60 dark:bg-gray-800/40 backdrop-blur-md p-6 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5"
  >
    <div
      class="text-xs sm:text-sm font-bold text-gray-500 uppercase tracking-widest mb-2"
    >
      {{ label }}
    </div>

    <div
      class="mt-1 text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight"
    >
      {{ formattedValue }}
    </div>

    <div
      v-if="hint"
      class="mt-2 text-xs font-bold text-gray-400 uppercase tracking-widest"
    >
      {{ hint }}
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
    required: true,
  },

  value: {
    type: [Number, String],
    required: true,
  },

  currency: {
    type: String,
    default: null, // "USD", "EUR" або null
  },

  hint: {
    type: String,
    default: null,
  },
});

const formattedValue = computed(() => {
  if (props.currency && typeof props.value === "number") {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: props.currency,
    }).format(props.value);
  }

  return props.value;
});
</script>
