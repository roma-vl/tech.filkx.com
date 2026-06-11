<template>
  <component
    :is="tag"
    :type="isButton ? type : undefined"
    :disabled="disabled || loading"
    :to="to"
    :href="href"
    class="inline-flex items-center justify-center font-bold transition-all duration-200 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#00a046] focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
    :class="[variantClasses, sizeClasses]"
    v-bind="filteredAttrs"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 shrink-0"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
    </svg>
    <slot name="prefix" />
    <slot />
    <slot name="suffix" />
  </component>
</template>

<script setup lang="ts">
import { computed, useAttrs } from "vue";

const props = withDefaults(defineProps<{
  variant?: "primary" | "secondary" | "outline" | "ghost" | "danger";
  size?: "sm" | "md" | "lg";
  type?: string;
  disabled?: boolean;
  loading?: boolean;
  to?: string | object;
  href?: string;
}>(), {
  variant: "primary",
  size: "md",
  type: "button",
});

const attrs = useAttrs();

const isButton = computed(() => !props.to && !props.href);
const tag = computed(() => {
  if (props.to) return "router-link";
  if (props.href) return "a";
  return "button";
});

const filteredAttrs = computed(() => {
  const { class: _, ...rest } = attrs as Record<string, unknown>;
  return rest;
});

const variantClasses = computed(() => ({
  primary:
    "bg-[#00a046] hover:bg-[#00b050] text-white shadow-sm shadow-emerald-500/20 hover:shadow-emerald-500/30",
  secondary:
    "bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700",
  outline:
    "border border-[#00a046] text-[#00a046] hover:bg-emerald-50 dark:hover:bg-emerald-900/20",
  ghost:
    "text-[#00a046] hover:bg-emerald-50 dark:hover:bg-emerald-900/20",
  danger:
    "bg-red-600 hover:bg-red-700 text-white shadow-sm",
}[props.variant]));

const sizeClasses = computed(() => ({
  sm: "px-3 py-1.5 text-xs rounded-lg gap-1.5",
  md: "px-4 py-2.5 text-sm rounded-lg gap-2",
  lg: "px-6 py-3 text-base rounded-xl gap-2",
}[props.size]));
</script>
