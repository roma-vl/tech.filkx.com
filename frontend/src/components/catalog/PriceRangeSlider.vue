<script setup>
import { computed } from "vue";

const props = defineProps({
  minVal: {
    type: Number,
    required: true,
  },
  maxVal: {
    type: Number,
    required: true,
  },
  min: {
    type: Number,
    default: 0,
  },
  max: {
    type: Number,
    default: 200000,
  },
  step: {
    type: Number,
    default: 1000,
  },
});

const emit = defineEmits(["update:minVal", "update:maxVal"]);

const localMin = computed({
  get: () => props.minVal,
  set: (val) => {
    const value = Math.min(val, props.maxVal - props.step);
    emit("update:minVal", value);
  },
});

const localMax = computed({
  get: () => props.maxVal,
  set: (val) => {
    const value = Math.max(val, props.minVal + props.step);
    emit("update:maxVal", value);
  },
});

const minPercent = computed(() => {
  return ((localMin.value - props.min) / (props.max - props.min)) * 100;
});

const maxPercent = computed(() => {
  return ((localMax.value - props.min) / (props.max - props.min)) * 100;
});
</script>

<template>
  <div class="space-y-4">
    <div class="relative w-full h-6 flex items-center select-none">
      <!-- Custom Slider Track -->
      <div
        class="absolute left-0 right-0 h-1 bg-zinc-200 dark:bg-zinc-800 rounded-full"
      />

      <!-- Custom Active Progress Track -->
      <div
        class="absolute h-1 bg-[#00a046] rounded-full"
        :style="{
          left: minPercent + '%',
          right: 100 - maxPercent + '%',
        }"
      />

      <!-- Real range inputs overlaid -->
      <input
        v-model.number="localMin"
        type="range"
        :min="min"
        :max="max"
        :step="step"
        class="absolute w-full h-1 pointer-events-none appearance-none bg-transparent outline-none accent-[#00a046] slider-input"
      >
      <input
        v-model.number="localMax"
        type="range"
        :min="min"
        :max="max"
        :step="step"
        class="absolute w-full h-1 pointer-events-none appearance-none bg-transparent outline-none accent-[#00a046] slider-input"
      >
    </div>

    <!-- Text Inputs -->
    <div class="flex items-center gap-2">
      <div class="flex-1 relative">
        <span
          class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-zinc-455 dark:text-zinc-500 font-extrabold"
        >₴</span>
        <input
          v-model.number="localMin"
          type="number"
          :min="min"
          :max="max"
          class="w-full h-9 pl-6 pr-2 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none text-zinc-800 dark:text-zinc-200"
        >
      </div>
      <span class="text-zinc-450 dark:text-zinc-500 text-[10px] font-black">—</span>
      <div class="flex-1 relative">
        <span
          class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-zinc-455 dark:text-zinc-500 font-extrabold"
        >₴</span>
        <input
          v-model.number="localMax"
          type="number"
          :min="min"
          :max="max"
          class="w-full h-9 pl-6 pr-2 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none text-zinc-800 dark:text-zinc-200"
        >
      </div>
    </div>
  </div>
</template>

<style scoped>
.slider-input {
  -webkit-appearance: none;
  appearance: none;
}

.slider-input::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  pointer-events: auto;
  width: 15px;
  height: 15px;
  border-radius: 50%;
  background: #00a046;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  transition: transform 0.1s ease;
}

.slider-input::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

.slider-input::-moz-range-thumb {
  pointer-events: auto;
  width: 15px;
  height: 15px;
  border-radius: 50%;
  background: #00a046;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  transition: transform 0.1s ease;
}

.slider-input::-moz-range-thumb:hover {
  transform: scale(1.2);
}
</style>
