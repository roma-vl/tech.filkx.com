<template>
  <div class="flex items-center justify-between">
    <div v-if="label || $slots.default">
      <p class="font-bold text-sm text-gray-900 dark:text-gray-100">
        <slot>{{ label }}</slot>
      </p>
      <p
        v-if="description"
        class="text-xs text-gray-500"
      >
        {{ description }}
      </p>
    </div>
    <label class="relative inline-flex items-center cursor-pointer">
      <input
        type="checkbox"
        class="sr-only peer"
        :checked="modelValue"
        :disabled="disabled"
        @change="$emit('update:modelValue', $event.target.checked)"
      >
      <div
        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600"
        :class="activeColorClass"
      />
    </label>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  label: String,
  description: String,
  disabled: Boolean,
  activeColor: {
    type: String,
    default: "indigo", // indigo, emerald, amber, etc.
  },
});

defineEmits(["update:modelValue"]);

const activeColorClass = computed(() => {
  const colors = {
    indigo: "peer-checked:bg-indigo-600",
    emerald: "peer-checked:bg-emerald-600",
    amber: "peer-checked:bg-amber-600",
    primary: "peer-checked:bg-primary-600",
  };
  return colors[props.activeColor] || colors.indigo;
});
</script>
