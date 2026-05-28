<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <div class="flex items-center justify-between mb-8">
      <div>
        <h3
          class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
        >
          {{ t("admin.growth.attribution.title") }}
        </h3>
        <p class="text-sm text-gray-400 mt-1">
          {{ t("admin.growth.attribution.roi_subtitle") }}
        </p>
      </div>
    </div>

    <AdminTable
      :headers="headers"
      :items="sortedSources"
      :empty-text="t('admin.growth.alerts.no_data')"
    >
      <template #row="{ item: source }">
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
          {{ source.name }}
        </td>
        <td class="px-6 py-4 text-right tabular-nums">
          {{ source.count }}
        </td>
        <td class="px-6 py-4 text-right tabular-nums">
          {{ source.activated }}
        </td>
        <td class="px-6 py-4 text-right font-bold text-primary-600">
          {{ source.rate }}%
        </td>
        <td class="px-6 py-4 text-right tabular-nums text-gray-400">
          {{ source.avgDuration }}m
        </td>
        <td class="px-6 py-4 text-right tabular-nums">
          <span :class="source.retention7d > 20 ? 'text-green-600 font-bold' : 'text-gray-500'">
            {{ source.retention7d }}%
          </span>
        </td>
        <td class="px-6 py-4 text-right">
          <div
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
            :class="getRoiClass(source.roiScore)"
          >
            {{ source.roiScore }}/10
          </div>
        </td>
      </template>
    </AdminTable>
  </div>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";

const {t} = useI18n();

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
});

const headers = computed(() => [
  {key: "source", label: t("admin.growth.attribution.source")},
  {key: "users", label: t("admin.growth.attribution.users"), class: "text-right"},
  {
    key: "activated",
    label: t("admin.growth.funnel.activated"),
    class: "text-right",
  },
  {
    key: "rate",
    label: t("admin.growth.funnel.activation_rate"),
    class: "text-right",
  },
  {key: "avg_duration", label: t("admin.growth.attribution.avg_duration"), class: "text-right"},
  {key: "retention_7d", label: t("admin.growth.attribution.retention_7d"), class: "text-right"},
  {key: "roi", label: t("admin.growth.attribution.roi"), class: "text-right"},
]);

const sortedSources = computed(() => {
  return Object.entries(props.data)
    .map(([name, data]) => ({
      name,
      count: data.signups,
      activated: data.activated,
      rate: data.signups > 0 ? ((data.activated / data.signups) * 100).toFixed(1) : 0,
      roiScore: data.roi_score || 0,
      retention7d: data.retained_7d_rate || 0,
      avgDuration: data.avg_duration_min || 0,
    }))
    .sort((a, b) => b.roiScore - a.roiScore); // Quality first!
});

const getRoiClass = (score) => {
  if (score >= 7)
    return "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400";
  if (score >= 4)
    return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400";
  return "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400";
};
</script>
