<template>
  <div
    class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all duration-300 group"
  >
    <div class="flex items-start justify-between">
      <div>
        <p
          class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 group-hover:text-primary-500 transition-colors"
        >
          {{ label }}
        </p>
        <div class="flex items-baseline gap-2">
          <h3
            class="text-3xl font-black text-gray-900 dark:text-white tracking-tight"
          >
            {{ value }}
          </h3>
          <span
            v-if="trend"
            :class="[
              'text-[10px] font-black pb-1 uppercase tracking-tighter',
              trendColor,
            ]"
          >
            {{ trend }}
          </span>
        </div>
      </div>
      <div
        v-if="icon"
        class="p-2 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-400 dark:text-gray-500 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/30 group-hover:text-primary-500 transition-all duration-300"
      >
        <component
          :is="icon"
          class="w-5 h-5"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  trend: {
    type: String,
    default: "",
  },
  trendType: {
    type: String,
    default: "up", // 'up', 'down', 'neutral'
    validator: (v) => ["up", "down", "neutral"].includes(v),
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
});

const trendColor = computed(() => {
  if (props.trendType === "up") return "text-green-600 dark:text-green-400";
  if (props.trendType === "down") return "text-red-600 dark:text-red-400";
  return "text-gray-400 dark:text-gray-500";
});
</script>
