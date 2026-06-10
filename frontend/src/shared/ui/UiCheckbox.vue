<template>
  <label class="inline-flex items-center gap-2.5 cursor-pointer select-none group" :class="disabled ? 'opacity-50 cursor-not-allowed' : ''">
    <input
      type="checkbox"
      class="sr-only"
      :checked="isChecked"
      :disabled="disabled"
      @change="onChange"
    />
    <!-- Visual indicator -->
    <span
      class="relative flex items-center justify-center w-4 h-4 transition-all duration-150 flex-shrink-0"
      :class="[
        isRadio ? 'rounded-full' : 'rounded',
        isChecked
          ? 'bg-[#00a046] border-2 border-[#00a046]'
          : 'bg-white dark:bg-zinc-900 border-2 border-zinc-300 dark:border-zinc-600 group-hover:border-[#00a046]'
      ]"
    >
      <!-- Checkmark for checkbox mode -->
      <svg
        v-if="!isRadio && isChecked"
        class="w-2.5 h-2.5 text-white"
        viewBox="0 0 10 8"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <polyline points="1,4 3.5,6.5 9,1" />
      </svg>
      <!-- Dot for radio mode -->
      <span v-if="isRadio && isChecked" class="w-1.5 h-1.5 rounded-full bg-white" />
    </span>
    <slot>
      <span v-if="label" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
        {{ label }}
      </span>
    </slot>
  </label>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  modelValue: boolean | unknown[] | string;
  value?: unknown;
  label?: string;
  disabled?: boolean;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", val: boolean | unknown[] | string): void;
}>();

const isRadio = computed(() => typeof props.modelValue === "string");

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) return props.modelValue.includes(props.value);
  if (typeof props.modelValue === "string") return props.modelValue === props.value;
  return props.modelValue;
});

const onChange = (e: Event) => {
  const checked = (e.target as HTMLInputElement).checked;

  if (Array.isArray(props.modelValue)) {
    const arr = [...props.modelValue];
    if (checked) {
      if (!arr.includes(props.value)) arr.push(props.value);
    } else {
      const idx = arr.indexOf(props.value);
      if (idx !== -1) arr.splice(idx, 1);
    }
    emit("update:modelValue", arr);
  } else if (typeof props.modelValue === "string") {
    emit("update:modelValue", checked ? (props.value as string) : "");
  } else {
    emit("update:modelValue", checked);
  }
};
</script>
