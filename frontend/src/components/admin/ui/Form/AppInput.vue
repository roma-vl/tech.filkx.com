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
        v-if="$slots.prepend"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <slot name="prepend" />
      </div>
      <input
        :value="modelValue"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-900 transition-colors shadow-sm text-gray-800 dark:text-gray-200"
        :class="[
          error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '',
          $slots.prepend ? 'pl-10' : '',
          $slots.append ? 'pr-10' : '',
          inputClass,
        ]"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
      >
      <div
        v-if="$slots.append"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <slot name="append" />
      </div>
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
  type: {
    type: String,
    default: "text",
  },
  placeholder: String,
  disabled: Boolean,
  error: String,
  inputClass: String,
});

defineEmits(["update:modelValue"]);
</script>
