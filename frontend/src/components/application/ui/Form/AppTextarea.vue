<template>
  <div class="w-full">
    <label
      v-if="label"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
    >
      {{ label }}
    </label>
    <div class="relative">
      <textarea
        :value="modelValue"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-900 transition-colors shadow-sm font-inherit"
        :class="[
          error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '',
          inputClass,
        ]"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>
    <p
      v-if="error"
      class="mt-1 text-sm text-red-600"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: "",
  },
  label: String,
  rows: {
    type: [String, Number],
    default: 3,
  },
  placeholder: String,
  disabled: Boolean,
  error: String,
  inputClass: String,
});

defineEmits(["update:modelValue"]);
</script>
