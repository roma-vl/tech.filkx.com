<template>
  <div
    class="rounded-lg p-4 mb-4 text-sm flex gap-3"
    :class="[classes]"
    role="alert"
  >
    <div
      v-if="$slots.icon || autoIcon"
      class="shrink-0"
    >
      <slot name="icon">
        <svg
          v-if="variant === 'warning'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else-if="variant === 'danger'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else-if="variant === 'success'"
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
            clip-rule="evenodd"
          />
        </svg>
      </slot>
    </div>
    <div class="flex-1">
      <h3
        v-if="title"
        class="font-medium mb-1"
      >
        {{ title }}
      </h3>
      <div v-if="$slots.default || message">
        <slot>{{ message }}</slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  variant: {
    type: String,
    default: "info",
    validator: (val) => ["info", "success", "warning", "danger"].includes(val),
  },
  title: String,
  message: String,
  autoIcon: {
    type: Boolean,
    default: true,
  },
});

const classes = computed(() => {
  const map = {
    info: "bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300",
    success:
      "bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300",
    warning:
      "bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300",
    danger: "bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300",
  };
  return map[props.variant];
});
</script>
