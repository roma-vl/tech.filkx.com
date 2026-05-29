<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div
      v-for="stat in stats"
      :key="stat.label"
      class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300 group"
    >
      <div class="flex items-center justify-between mb-4">
        <div
          :class="[
            'p-3 rounded-2xl text-white shadow-xl group-hover:scale-110 transition-transform',
            stat.bgClass,
          ]"
        >
          <component
            :is="getIcon(stat.icon)"
            class="w-6 h-6"
          />
        </div>
        <div
          v-if="stat.trend !== undefined"
          :class="[
            'flex items-center gap-1.5 text-xs font-black uppercase tracking-widest',
            stat.trend >= 0 ? 'text-green-500' : 'text-red-500',
          ]"
        >
          <span>{{ stat.trend >= 0 ? "+" : "" }}{{ stat.trend }}%</span>
          <TrendingUpIcon
            v-if="stat.trend >= 0"
            class="w-4 h-4"
          />
          <TrendingDownIcon
            v-else
            class="w-4 h-4"
          />
        </div>
      </div>
      <p
        class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2"
      >
        {{ stat.label }}
      </p>
      <p
        class="text-3xl font-black text-gray-900 dark:text-white tracking-tight"
      >
        {{ stat.value }}
      </p>
    </div>
  </div>
</template>

<script setup>
import {
  UsersIcon,
  SignalIcon,
  VideoCameraIcon,
  BanknotesIcon,
  ClockIcon,
  Square3Stack3DIcon,
  CheckBadgeIcon,
  ArrowTrendingUpIcon as TrendingUpIcon,
  ArrowTrendingDownIcon as TrendingDownIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  stats: {
    type: Array,
    required: true,
  },
});

const getIcon = (name) => {
  const icons = {
    UsersIcon,
    SignalIcon,
    VideoCameraIcon,
    BanknotesIcon,
    ClockIcon,
    Square3Stack3DIcon,
    CheckBadgeIcon,
  };
  return icons[name] || ClockIcon;
};
</script>
