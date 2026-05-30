<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm relative overflow-hidden group"
  >
    <div
      class="absolute top-0 right-0 w-32 h-32 bg-rose-500/5 rounded-full blur-3xl -mr-16 -mt-16 transition-opacity duration-500 group-hover:opacity-100 opacity-0"
    />

    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-rose-50 dark:group-hover:bg-rose-900/20 group-hover:text-rose-500 transition-all duration-500 shadow-sm"
        >
          <VideoCameraIcon class="w-7 h-7" />
        </div>
        <div>
          <h3
            class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]"
          >
            {{ t("admin.system.streaming.title") }}
          </h3>
          <p
            class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase mt-0.5"
          >
            {{ t("admin.system.streaming.subtitle") }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
      <div
        class="p-6 rounded-3xl bg-gray-50 dark:bg-gray-900/50 border border-gray-50 dark:border-gray-800 flex flex-col items-center"
      >
        <span
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2"
        >{{ t("admin.system.streaming.active_streams") }}</span>
        <span
          class="text-4xl font-black text-gray-900 dark:text-white font-mono leading-none"
        >
          {{ activeStreams }}
        </span>
        <div class="flex items-center gap-1 mt-3">
          <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" />
          <span class="text-[9px] font-black text-green-600 uppercase">{{
            t("admin.system.streaming.live_now")
          }}</span>
        </div>
      </div>

      <div
        class="p-6 rounded-3xl bg-gray-50 dark:bg-gray-900/50 border border-gray-50 dark:border-gray-800 flex flex-col items-center"
      >
        <span
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2"
        >{{ t("admin.system.streaming.encoder_load") }}</span>
        <span
          class="text-4xl font-black text-gray-900 dark:text-white font-mono leading-none"
        >
          {{ encoderLoad }}%
        </span>
        <div
          class="w-full bg-gray-200 dark:bg-gray-700 h-1 rounded-full mt-4 overflow-hidden"
        >
          <div
            class="h-full bg-rose-500 transition-all duration-1000"
            :style="{ width: encoderLoad + '%' }"
          />
        </div>
      </div>
    </div>

    <div class="mt-8 space-y-4">
      <div
        v-for="stat in streamingJobs"
        :key="stat.name"
        class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-gray-800/50 border border-gray-50 dark:border-gray-700/50"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-1.5 h-1.5 rounded-full"
            :class="
              stat.status === 'processing'
                ? 'bg-amber-500 animate-pulse'
                : 'bg-primary-500'
            "
          />
          <span
            class="text-xs font-black text-gray-600 dark:text-gray-400 uppercase tracking-tight"
          >{{ translateJobName(stat.name) }}</span>
        </div>
        <span
          class="text-xs font-black text-gray-900 dark:text-white font-mono"
        >
          {{ stat.value }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { VideoCameraIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  activeStreams: { type: Number, default: 0 },
  encoderLoad: { type: Number, default: 0 },
  streamingJobs: {
    type: Array,
    default: () => [],
  },
});

const translateJobName = (name) => {
  const map = {
    "Parallel Transcodes": t("admin.system.streaming.parallel_transcodes"),
    "YouTube Destinations": t("admin.system.streaming.youtube_destinations"),
    "RTMP Ingests": t("admin.system.streaming.rtmp_ingests"),
    "Total Library": t("admin.system.streaming.total_library"),
  };
  return map[name] || name;
};
</script>
