<template>
  <div
    class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"
    :class="wrapperClass"
  >
    <div
      class="bg-primary-600 h-2 rounded-full transition-all duration-300"
      :style="{ width: percent + '%' }"
      :class="progressClass"
    />
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  value: {
    type: Number,
    required: true,
  },
  max: {
    type: Number,
    default: 100,
  },
  wrapperClass: String,
  progressClass: String,
});

const percent = computed(() => {
  return Math.min(100, Math.max(0, (props.value / props.max) * 100));
});
</script>
