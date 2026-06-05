<template>
  <label class="inline-flex items-center cursor-pointer group">
    <input
      type="radio"
      :name="name"
      :value="value"
      :checked="modelValue === value"
      :disabled="disabled"
      class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
      @change="$emit('update:modelValue', value)"
    >
    <span
      v-if="label || $slots.default"
      class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors"
      :class="{ 'opacity-50': disabled }"
    >
      <slot>{{ label }}</slot>
    </span>
  </label>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: "",
  },
  value: {
    type: [String, Number, Boolean],
    required: true,
  },
  name: {
    type: String,
    default: "",
  },
  label: String,
  disabled: Boolean,
});

defineEmits(["update:modelValue"]);
</script>
