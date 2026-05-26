<template>
  <button
    :class="[
      baseClass,
      variantClass,
      sizeClass,
      disabled ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-md',
    ]"
    :disabled="disabled || loading"
    @click="$emit('click')"
  >
    <span
      v-if="loading"
      class="animate-spin mr-2"
    >
      <svg
        class="w-4 h-4 text-white"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
        />
      </svg>
    </span>
    <component
      :is="icon"
      v-if="icon && iconPosition === 'left'"
      class="mr-2 w-6 h-6"
    />
    <span><slot /></span>
    <component
      :is="icon"
      v-if="icon && iconPosition === 'right'"
      class="ml-2 w-6 h-6"
    />
  </button>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  variant: { type: String, default: "primary" },
  size: { type: String, default: "md" },
  icon: { type: [Object, Function], default: null },
  iconPosition: { type: String, default: "left" },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

const baseClass =
  "inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200";
const variantClass = computed(() => {
  switch (props.variant) {
    case "secondary":
      return "bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200";
    case "danger":
      return "bg-red-600 hover:bg-red-700 text-white";
    default:
      return "bg-indigo-600 hover:bg-indigo-700 text-white";
  }
});
const sizeClass = computed(() => {
  switch (props.size) {
    case "sm":
      return "px-3 py-1 text-sm";
    case "lg":
      return "px-6 py-3 text-lg";
    default:
      return "px-4 py-2 text-md";
  }
});
</script>
