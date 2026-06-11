<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <button
          class="p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all hover:scale-110 active:scale-95"
          :class="{ 'animate-spin': loading }"
          :title="t('admin.analytics.refresh')"
          @click="$emit('refresh')"
        >
          <ArrowPathIcon class="w-5 h-5 text-gray-500" />
        </button>
      </div>
    </div>

    <AnalyticsStatsGrid :stats="overview" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <AnalyticsTimeSeriesChart
          v-model:active-tab="activeTimeSeries"
          :labels="timeSeriesData.labels"
          :datasets="timeSeriesData.datasets"
        />
      </div>

      <div class="space-y-6">
        <AnalyticsPlanChart :plans="distributionData.plans" />
        <AnalyticsContentMix :content="distributionData.content" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { ArrowPathIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";

import AnalyticsStatsGrid from "@/components/admin/features/analytics/AnalyticsStatsGrid.vue";
import AnalyticsTimeSeriesChart from "@/components/admin/features/analytics/AnalyticsTimeSeriesChart.vue";
import AnalyticsPlanChart from "@/components/admin/features/analytics/AnalyticsPlanChart.vue";
import AnalyticsContentMix from "@/components/admin/features/analytics/AnalyticsContentMix.vue";

const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const overview = ref([]);
const timeSeriesData = ref({ labels: [], datasets: {} });
const distributionData = ref({ plans: [], content: [] });
const activeTimeSeries = ref("revenue");

const init = async () => {
  loading.value = true;
  try {
    const [ovResp, chartResp, distResp] = await Promise.all([
      api.get("/admin/analytics/overview"),
      api.get("/admin/analytics/charts"),
      api.get("/admin/analytics/distributions"),
    ]);

    overview.value = ovResp.data.data.overview;
    timeSeriesData.value = chartResp.data.data;
    distributionData.value = distResp.data.data;
  } catch (err) {
    console.error("Failed to load analytics:", err);
    toast.error(t("admin.analytics.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  init();
});

defineExpose({ refresh: init });
</script>
