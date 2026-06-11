<template>
  <div
    class="rounded-2xl p-4 flex gap-4"
    :class="[
      center ? 'items-center' : 'items-start',
      variant === 'info'
        ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800'
        : '',
      variant === 'warning'
        ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800'
        : '',
      variant === 'error'
        ? 'bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800'
        : '',
      variant === 'success'
        ? 'bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800'
        : '',
    ]"
  >
    <div
      class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
      :class="[
        variant === 'info'
          ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400'
          : '',
        variant === 'warning'
          ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400'
          : '',
        variant === 'error'
          ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400'
          : '',
        variant === 'success'
          ? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400'
          : '',
      ]"
    >
      <slot name="icon">
        <svg
          v-if="variant === 'info' || variant === 'warning'"
          xmlns="http://www.w3.org/2000/svg"
          class="w-6 h-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path
            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
          />
          <line
            x1="12"
            y1="9"
            x2="12"
            y2="13"
          />
          <line
            x1="12"
            y1="17"
            x2="12.01"
            y2="17"
          />
        </svg>
        <svg
          v-else-if="variant === 'error'"
          xmlns="http://www.w3.org/2000/svg"
          class="w-6 h-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle
            cx="12"
            cy="12"
            r="10"
          />
          <line
            x1="15"
            y1="9"
            x2="9"
            y2="15"
          />
          <line
            x1="9"
            y1="9"
            x2="15"
            y2="15"
          />
        </svg>
        <svg
          v-else-if="variant === 'success'"
          xmlns="http://www.w3.org/2000/svg"
          class="w-6 h-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </slot>
    </div>
    <div class="flex-1">
      <h3
        v-if="title"
        class="font-bold mb-1"
        :class="[
          variant === 'info' ? 'text-blue-900 dark:text-blue-100' : '',
          variant === 'warning' ? 'text-yellow-900 dark:text-yellow-100' : '',
          variant === 'error' ? 'text-red-900 dark:text-red-100' : '',
          variant === 'success' ? 'text-green-900 dark:text-green-100' : '',
        ]"
      >
        {{ title }}
      </h3>
      <p
        v-if="description"
        class="text-sm"
        :class="[
          variant === 'info' ? 'text-blue-700 dark:text-blue-300' : '',
          variant === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : '',
          variant === 'error' ? 'text-red-700 dark:text-red-300' : '',
          variant === 'success' ? 'text-green-700 dark:text-green-300' : '',
        ]"
      >
        {{ description }}
      </p>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: "",
  },
  description: {
    type: String,
    default: "",
  },
  variant: {
    type: String,
    default: "info", // info, warning, error, success
    validator: (value) =>
      ["info", "warning", "error", "success"].includes(value),
  },
  center: {
    type: Boolean,
    default: false,
  },
});
</script>
