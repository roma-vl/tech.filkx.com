<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
  >
    <div class="flex items-center justify-between mb-8">
      <div>
        <h3
          class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
        >
          {{ t("admin.growth.cohorts.title") }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          {{ t("admin.growth.cohorts.subtitle") }}
        </p>
      </div>
      <div class="flex items-center gap-3">
        <span
          class="px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-primary-100 dark:border-primary-800"
        >
          {{ t("admin.growth.funnel.base_info") }}
        </span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead>
          <tr
            class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700"
          >
            <th class="px-4 py-3 w-32">
              {{ t("admin.growth.cohorts.week") }}
            </th>
            <th class="px-4 py-3 w-20 text-center">
              {{ t("admin.growth.cohorts.users") }}
            </th>
            <th class="px-4 py-3 text-center">
              D1
            </th>
            <th class="px-4 py-3 text-center">
              D3
            </th>
            <th class="px-4 py-3 text-center">
              D7
            </th>
            <th class="px-4 py-3 text-center">
              D14
            </th>
            <th class="px-4 py-3 text-center">
              D30
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
          <tr
            v-for="cohort in data"
            :key="cohort.week"
            class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
          >
            <td class="px-4 py-4 font-bold text-gray-900 dark:text-white">
              {{ cohort.week }}
            </td>
            <td class="px-4 py-4 text-center text-gray-500">
              {{ cohort.count }}
            </td>
            <td
              v-for="day in ['d1', 'd3', 'd7', 'd14', 'd30']"
              :key="day"
              class="px-2 py-4 text-center border-l border-gray-50 dark:border-gray-700"
              :style="getHeatmapStyle(cohort.retention[day])"
            >
              <span
                :class="
                  cohort.retention[day] > 0
                    ? 'font-bold text-gray-900 dark:text-gray-100'
                    : 'text-gray-300 dark:text-gray-600'
                "
              >
                {{ cohort.retention[day] }}%
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});

const getHeatmapStyle = (value) => {
  if (!value) return {};

  // Calculate opacity based on retention percentage (0-100)
  // We want higher retention to be more intense
  const opacity = (value / 50).toFixed(2); // Capped at 50% for full saturation

  return {
    backgroundColor: `rgba(59, 130, 246, ${Math.min(opacity, 0.4)})`,
    color: value > 25 ? "white" : "inherit",
  };
};
</script>
