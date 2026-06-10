<template>
  <div class="w-full">
    <label
      v-if="label"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
    >
      {{ label }}
    </label>
    <div class="relative">
      <div
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary-500 transition-colors"
      >
        <CalendarIcon class="w-5 h-5" />
      </div>
      <input
        :value="modelValue"
        type="date"
        :disabled="disabled"
        class="w-full pl-10 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800/50 focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-900 transition-all shadow-sm text-sm text-gray-900 dark:text-gray-100"
        @input="$emit('update:modelValue', $event.target.value)"
      >
    </div>
    <p
      v-if="error"
      class="mt-1 text-xs text-red-600"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { CalendarIcon } from "@heroicons/vue/24/outline";

defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  label: String,
  disabled: Boolean,
  error: String,
});

defineEmits(["update:modelValue"]);
</script>

<style scoped>
/* Custom styling for the native date picker icon to hide it or style it if needed */
input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: invert(0.5);
  opacity: 0.6;
  transition: all 0.2s;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}

.dark input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
}
</style>
