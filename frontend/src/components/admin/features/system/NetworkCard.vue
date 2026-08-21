<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm relative overflow-hidden h-full flex flex-col group"
  >
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20 group-hover:text-indigo-500 transition-all duration-500 shadow-sm"
        >
          <ArrowsRightLeftIcon class="w-7 h-7" />
        </div>
        <div>
          <h3
            class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]"
          >
            {{ t("admin.system.network.title") }}
          </h3>
          <p
            class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase mt-0.5"
          >
            {{ t("admin.system.network.subtitle") }}
          </p>
        </div>
      </div>
    </div>

    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-6">
      <!-- Incoming -->
      <div class="space-y-4">
        <div
          class="flex justify-between items-center px-4 py-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30"
        >
          <div class="flex flex-col">
            <span
              class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-tight"
            >{{ t("admin.system.network.incoming") }}</span>
            <span
              class="text-lg font-black text-indigo-600 dark:text-indigo-400 font-mono"
            >{{ incoming }} Mbps</span>
          </div>
          <ArrowDownCircleIcon class="w-6 h-6 text-indigo-400 animate-bounce" />
        </div>
        <div class="flex gap-1 h-12 items-end px-2">
          <div
            v-for="(h, i) in history.incoming"
            :key="i"
            class="flex-1 bg-indigo-200 dark:bg-indigo-700/50 rounded-t-sm transition-all duration-500"
            :style="{ height: (h / maxBandwidth) * 100 + '%' }"
          />
        </div>
      </div>

      <!-- Outgoing -->
      <div class="space-y-4">
        <div
          class="flex justify-between items-center px-4 py-3 rounded-2xl bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/30"
        >
          <div class="flex flex-col">
            <span
              class="text-[9px] font-black text-purple-400 uppercase tracking-widest leading-tight"
            >{{ t("admin.system.network.outgoing") }}</span>
            <span
              class="text-lg font-black text-purple-600 dark:text-purple-400 font-mono"
            >{{ outgoing }} Mbps</span>
          </div>
          <ArrowUpCircleIcon
            class="w-6 h-6 text-purple-400 -translate-y-1 animate-bounce"
          />
        </div>
        <div class="flex gap-1 h-12 items-end px-2">
          <div
            v-for="(h, i) in history.outgoing"
            :key="i"
            class="flex-1 bg-purple-200 dark:bg-purple-700/50 rounded-t-sm transition-all duration-500"
            :style="{ height: (h / maxBandwidth) * 100 + '%' }"
          />
        </div>
      </div>
    </div>

    <div
      class="mt-8 pt-8 border-t border-gray-50 dark:border-gray-700/50 space-y-3"
    >
      <div
        class="flex items-center justify-between text-[10px] font-black text-gray-400 uppercase tracking-widest"
      >
        <span>{{ t("admin.system.network.incoming") }} (Total)</span>
        <span class="text-gray-900 dark:text-white font-mono">{{ totalReceived }} GB</span>
      </div>
      <div
        class="flex items-center justify-between text-[10px] font-black text-gray-400 uppercase tracking-widest"
      >
        <span>{{ t("admin.system.network.outgoing") }} (Total)</span>
        <span class="text-gray-900 dark:text-white font-mono">{{ totalSent }} GB</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ArrowDownCircleIcon,
  ArrowsRightLeftIcon,
  ArrowUpCircleIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  incoming: { type: Number, default: 0 },
  outgoing: { type: Number, default: 0 },
  totalReceived: { type: Number, default: 0 },
  totalSent: { type: Number, default: 0 },
  maxBandwidth: { type: Number, default: 1000 },
  history: {
    type: Object,
    default: () => ({
      incoming: [
        12, 45, 67, 23, 89, 45, 34, 12, 56, 78, 45, 23, 67, 89, 90, 45, 34, 56,
        23, 12,
      ],
      outgoing: [
        56, 78, 45, 23, 67, 89, 90, 45, 34, 56, 23, 12, 34, 45, 78, 23, 45, 67,
        89, 90,
      ],
    }),
  },
});
</script>
