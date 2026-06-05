<template>
  <span
    :class="[
      'px-2 py-1 text-xs rounded-full font-medium inline-flex items-center gap-1.5',
      variantClasses[variant] || variantClasses.info,
      customClass,
    ]"
  >
    <slot name="icon" />
    <slot />
  </span>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: "info",
    validator: (value) =>
      ["success", "warning", "error", "info", "primary", "neutral"].includes(
        value,
      ),
  },
  customClass: {
    type: String,
    default: "",
  },
});

const variantClasses = {
  success:
    "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
  warning:
    "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
  error: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  info: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  primary:
    "bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400",
  neutral: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
};
</script>
