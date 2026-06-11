<template>
  <div
    class="group relative bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm transition-all duration-500 hover:shadow-2xl hover:shadow-primary-500/5 hover:-translate-y-1 overflow-hidden"
  >
    <div
      class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full blur-3xl -mr-16 -mt-16 transition-opacity duration-500 group-hover:opacity-100 opacity-0"
    />

    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-500 transition-all duration-500 shadow-sm"
        >
          <component
            :is="icon"
            class="w-7 h-7"
          />
        </div>
        <div>
          <h3
            class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]"
          >
            {{ title }}
          </h3>
          <p
            class="text-xl font-black text-gray-900 dark:text-white tracking-tight uppercase mt-0.5"
          >
            {{ subtitle }}
          </p>
        </div>
      </div>
      <div
        v-if="trend !== undefined"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm"
        :class="
          trend > 0
            ? 'bg-red-50 dark:bg-red-900/20 text-red-600'
            : 'bg-green-50 dark:bg-green-900/20 text-green-600'
        "
      >
        <component
          :is="trend > 0 ? ArrowUpIcon : ArrowDownIcon"
          class="w-3.5 h-3.5"
        />
        {{ Math.abs(trend) }}%
      </div>
    </div>

    <!-- Progress Style (CPU, RAM) -->
    <div
      v-if="type === 'progress'"
      class="space-y-6"
    >
      <div>
        <div class="flex justify-between items-end mb-3">
          <span
            class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
          >{{ label }}</span>
          <span
            class="text-lg font-black text-gray-900 dark:text-white font-mono"
          >{{ value }}{{ unit }}</span>
        </div>
        <div
          class="h-3 w-full bg-gray-100 dark:bg-gray-900/50 rounded-full overflow-hidden p-0.5 border border-gray-50 dark:border-gray-800"
        >
          <div
            class="h-full rounded-full transition-all duration-1000 ease-out relative"
            :class="
              percentage > 80
                ? 'bg-red-500 shadow-lg shadow-red-500/50'
                : 'bg-primary-600 shadow-lg shadow-primary-500/50'
            "
            :style="{ width: percentage + '%' }"
          >
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-white/20" />
          </div>
        </div>
      </div>
    </div>

    <!-- Circular Style (Disk) -->
    <div
      v-else-if="type === 'circular'"
      class="flex flex-col items-center justify-center"
    >
      <div class="relative w-40 h-40 flex items-center justify-center">
        <svg class="w-full h-full transform -rotate-90">
          <circle
            cx="80"
            cy="80"
            r="68"
            stroke="currentColor"
            stroke-width="10"
            fill="transparent"
            class="text-gray-100 dark:text-gray-900/50"
          />
          <circle
            cx="80"
            cy="80"
            r="68"
            stroke="currentColor"
            stroke-width="12"
            fill="transparent"
            :stroke-dasharray="2 * Math.PI * 68"
            :stroke-dashoffset="
              2 * Math.PI * 68 * (1 - Math.min(percentage, 100) / 100)
            "
            class="transition-all duration-1000 ease-out"
            :class="percentage > 90 ? 'text-red-500' : 'text-primary-500'"
            stroke-linecap="round"
          />
        </svg>
        <div class="absolute flex flex-col items-center">
          <span
            class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter"
          >{{ percentage }}%</span>
          <span
            class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mt-1"
          >{{ label }}</span>
        </div>
      </div>
      <div class="mt-8 grid grid-cols-2 gap-8 w-full">
        <div
          class="flex flex-col border-r border-gray-100 dark:border-gray-700/50"
        >
          <span
            class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1"
          >{{ t("admin.system.resources.disk_used") }}</span>
          <span
            class="text-sm font-black text-gray-900 dark:text-white font-mono"
          >{{ value }}{{ unit }}</span>
        </div>
        <div class="flex flex-col text-right">
          <span
            class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1"
          >{{ t("admin.system.resources.capacity") }}</span>
          <span
            class="text-sm font-black text-gray-900 dark:text-white font-mono"
          >{{ total }}{{ unit }}</span>
        </div>
      </div>
    </div>

    <!-- Stats Style (Database, SaaS) -->
    <div
      v-else
      class="space-y-4"
    >
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-50 dark:border-gray-800"
      >
        <span
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
        >{{ stat.label }}</span>
        <span
          class="text-xs font-black uppercase tracking-tight"
          :class="
            stat.variant === 'success'
              ? 'text-green-500'
              : 'text-gray-900 dark:text-white font-mono'
          "
        >
          {{ stat.value }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowDownIcon, ArrowUpIcon } from "@heroicons/vue/24/solid";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  type: {
    type: String,
    default: "progress", // progress, circular, stats
  },
  icon: Object,
  title: String,
  subtitle: String,
  label: String,
  value: [String, Number],
  total: [String, Number],
  unit: String,
  percentage: Number,
  trend: Number,
  stats: Array, // For stats type: [{ label: string, value: string, variant?: string }]
});
</script>
