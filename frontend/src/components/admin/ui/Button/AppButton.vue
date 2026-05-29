<template>
  <component
    :is="tag"
    :type="isButton ? type : undefined"
    :disabled="disabled || loading"
    :to="to"
    :href="href"
    class="inline-flex items-center justify-center font-medium transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] active:brightness-95"
    :class="[variantClasses, sizeClasses, classAttr]"
    v-bind="filteredAttrs"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
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
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>

    <slot name="prefix"/>
    <slot/>
    <slot name="suffix"/>
  </component>
</template>

<script setup>
import {computed, useAttrs} from "vue";

const props = defineProps({
  variant: {
    type: String,
    default: "primary",
    validator: (val) =>
      ["primary", "secondary", "white", "success", "danger", "ghost", "text"].includes(
        val,
      ),
  },
  size: {
    type: String,
    default: "md",
    validator: (val) => ["sm", "md", "lg"].includes(val),
  },
  type: {
    type: String,
    default: "button",
  },
  disabled: Boolean,
  loading: Boolean,
  to: [String, Object],
  href: String,
});

const attrs = useAttrs();
const classAttr = attrs.class;

const isButton = computed(() => !props.to && !props.href);
const tag = computed(() => {
  if (props.to) return "router-link";
  if (props.href) return "a";
  return "button";
});

const filteredAttrs = computed(() => {
  const {class: _, ...rest} = attrs;
  return rest;
});

const variantClasses = computed(() => {
  const map = {
    primary:
      "bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 shadow-sm hover:shadow-lg hover:shadow-primary/30 focus:ring-primary-500",
    secondary:
      "bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300 hover:shadow-md focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:active:bg-gray-500",
    white:
      "bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 active:bg-gray-100 shadow-sm hover:shadow-lg focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:active:bg-gray-600",
    success:
      "bg-emerald-600 text-white hover:bg-emerald-700 active:bg-emerald-800 shadow-sm hover:shadow-lg hover:shadow-emerald-500/30 focus:ring-emerald-500",
    danger:
      "bg-red-600 text-white hover:bg-red-700 active:bg-red-800 shadow-sm hover:shadow-lg hover:shadow-red-500/30 focus:ring-red-500",
    ghost:
      "text-primary-600 hover:bg-primary-50 active:bg-primary-100 hover:shadow-sm dark:text-primary-400 dark:hover:bg-primary-900/20 dark:active:bg-primary-900/40",
    text: "text-primary-600 hover:text-primary-700 active:text-primary-800 underline-offset-4 hover:underline p-0 shadow-none dark:text-primary-400 dark:hover:text-primary-300 dark:active:text-primary-200",
  };
  return map[props.variant];
});

const sizeClasses = computed(() => {
  if (props.variant === "text") return "";

  const map = {
    sm: "px-3 py-1.5 text-sm rounded-lg",
    md: "px-4 py-2 text-sm rounded-lg",
    lg: "px-6 py-3 text-base rounded-xl",
  };
  return map[props.size];
});
</script>
