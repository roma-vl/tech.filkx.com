<template>
  <div
    :class="[
      'flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-bold border transition-all duration-300 group',
      fullWidth ? 'w-full' : '',
      bgColorClass,
    ]"
  >
    <div
      class="p-1.5 rounded-lg bg-white/50 dark:bg-white/5 shadow-sm border border-white/60 dark:border-white/10 group-hover:scale-110 transition-transform flex-shrink-0"
    >
      <component :is="icon" class="h-4 w-4 flex-shrink-0" />
    </div>

    <div class="flex items-center gap-2 whitespace-nowrap overflow-hidden">
      <span
        class="text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-300"
      >
        {{ t(label ?? "") }}:
      </span>

      <div class="flex items-baseline gap-1">
        <span class="text-gray-900 dark:text-white">
          {{ value }}
        </span>
        <span
          v-if="max"
          class="text-gray-500 dark:text-gray-400 font-medium text-xs"
        >
          / {{ max }}<span v-if="unit"> {{ unit }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps<{
  label?: string;
  icon?: object | Function;
  unit?: string;
  value?: number | string;
  max?: number | string;
  fullWidth?: boolean;
  color?: "gray" | "blue" | "purple" | "orange" | "red";
}>();

const bgColorMap: Record<string, string> = {
  gray: "bg-white/40 dark:bg-gray-800/20 border-white/60 dark:border-white/10 shadow-sm",
  blue: "bg-blue-50/50 dark:bg-blue-900/10 border-blue-200/50 dark:border-blue-700/50 text-blue-700 dark:text-blue-300 shadow-sm",
  purple:
    "bg-purple-50/50 dark:bg-purple-900/10 border-purple-200/50 dark:border-purple-700/50 text-purple-700 dark:text-purple-300 shadow-sm",
  orange:
    "bg-orange-50/50 dark:bg-orange-900/10 border-orange-200/50 dark:border-orange-700/50 text-orange-700 dark:text-orange-300 shadow-sm",
  red: "bg-red-50/50 dark:bg-red-900/10 border-red-200/50 dark:border-red-700/50 text-red-700 dark:text-red-300 shadow-sm",
};

const bgColorClass = computed(
  () => bgColorMap[props.color ?? "gray"] ?? bgColorMap.gray,
);
</script>
