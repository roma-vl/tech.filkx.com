<template>
  <div
    class="animate-pulse bg-gray-200 dark:bg-gray-700 rounded"
    :class="[className]"
    :style="styleObj"
  />
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  width: {
    type: [String, Number],
    default: "100%",
  },
  height: {
    type: [String, Number],
    default: "1em",
  },
  className: {
    type: String,
    default: "",
  },
  circle: {
    type: Boolean,
    default: false,
  },
});

const styleObj = computed(() => {
  const w = typeof props.width === "number" ? `${props.width}px` : props.width;
  const h =
    typeof props.height === "number" ? `${props.height}px` : props.height;

  return {
    width: w,
    height: h,
    borderRadius: props.circle ? "50%" : undefined,
  };
});
</script>
