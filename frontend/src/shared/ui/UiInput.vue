<template>
  <div class="w-full">
    <label
      v-if="label"
      class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5"
    >
      {{ label }}
    </label>
    <div class="relative">
      <div
        v-if="$slots.prepend"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400"
      >
        <slot name="prepend" />
      </div>
      <input
        :value="modelValue"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        class="w-full px-4 py-2.5 text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:border-[#00a046] focus:ring-2 focus:ring-[#00a046]/20 disabled:opacity-50 disabled:bg-zinc-50 dark:disabled:bg-zinc-800 transition-colors"
        :class="[
          error ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20' : '',
          $slots.prepend ? 'pl-10' : '',
          $slots.append ? 'pr-10' : '',
        ]"
        v-bind="$attrs"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
      <div
        v-if="$slots.append"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <slot name="append" />
      </div>
    </div>
    <p v-if="error" class="mt-1.5 text-xs text-red-500 font-medium">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: string | number;
  label?: string;
  type?: string;
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  error?: string;
}>();

defineEmits(["update:modelValue"]);
</script>
