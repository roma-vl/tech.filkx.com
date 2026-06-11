<template>
  <div class="w-full">
    <label
      v-if="label"
      class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5"
    >
      {{ label }}
    </label>
    <div class="relative">
      <select
        :value="modelValue"
        :disabled="disabled"
        class="w-full appearance-none px-4 py-2.5 pr-10 text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-[#00a046] focus:ring-2 focus:ring-[#00a046]/20 disabled:opacity-50 disabled:bg-zinc-50 dark:disabled:bg-zinc-800 transition-colors cursor-pointer"
        :class="error ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : ''"
        v-bind="$attrs"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      >
        <option v-if="placeholder" value="" disabled :selected="!modelValue">
          {{ placeholder }}
        </option>
        <option
          v-for="opt in normalizedOptions"
          :key="opt.value"
          :value="opt.value"
        >
          {{ opt.label }}
        </option>
      </select>
      <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-zinc-400">
        <span class="material-symbols-outlined text-[18px]">expand_more</span>
      </span>
    </div>
    <p v-if="error" class="mt-1.5 text-xs text-red-500 font-medium">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  modelValue?: string | number;
  label?: string;
  placeholder?: string;
  disabled?: boolean;
  error?: string;
  options: Array<string | { value: string | number; label: string }>;
}>();

defineEmits(["update:modelValue"]);

const normalizedOptions = computed(() =>
  props.options.map((o) =>
    typeof o === "string" ? { value: o, label: o } : o,
  ),
);
</script>
