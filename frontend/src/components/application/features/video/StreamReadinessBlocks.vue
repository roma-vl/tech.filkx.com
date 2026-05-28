<template>
  <div class="grid grid-cols-2 gap-3 mt-6">
    <div
      v-for="(metric, key) in readinessItems"
      :key="key"
      class="p-4 rounded-2xl border transition-all duration-300 transform hover:scale-[1.02]"
      :class="[
        metric.status === 'green'
          ? 'bg-green-50/50 border-green-100 dark:bg-green-900/10 dark:border-green-800/30'
          : metric.status === 'yellow'
            ? 'bg-yellow-50/50 border-yellow-100 dark:bg-yellow-900/10 dark:border-yellow-800/30'
            : 'bg-red-50/50 border-red-100 dark:bg-red-900/10 dark:border-red-800/30',
      ]"
    >
      <div class="flex items-center gap-2 mb-2">
        <div
          class="w-2 h-2 rounded-full animate-pulse"
          :class="[
            metric.status === 'green'
              ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]'
              : metric.status === 'yellow'
                ? 'bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.5)]'
                : 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]',
          ]"
        />
        <span
          class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500"
        >
          {{ t(`media.readiness.labels.${key}`) }}
        </span>
      </div>

      <div class="flex flex-col">
        <span
          class="text-sm font-black mb-0.5"
          :class="[
            metric.status === 'green'
              ? 'text-green-700 dark:text-green-400'
              : metric.status === 'yellow'
                ? 'text-yellow-700 dark:text-yellow-400'
                : 'text-red-700 dark:text-red-400',
          ]"
        >
          {{ metric.value }}
        </span>
        <span
          class="text-[10px] font-medium leading-tight text-gray-500 dark:text-gray-400"
        >
          {{ metric.message }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  readiness: {
    type: Object,
    required: true,
  },
});

const readinessItems = computed(() => {
  return {
    resolution: props.readiness.resolution || {
      status: "red",
      value: "Unknown",
      message: "Analysis failed",
    },
    frameRate: props.readiness.frameRate || {
      status: "red",
      value: "Unknown",
      message: "Analysis failed",
    },
    codec: props.readiness.codec || {
      status: "red",
      value: "Unknown",
      message: "Analysis failed",
    },
    bitrate: props.readiness.bitrate || {
      status: "red",
      value: "Unknown",
      message: "Analysis failed",
    },
  };
});
</script>
