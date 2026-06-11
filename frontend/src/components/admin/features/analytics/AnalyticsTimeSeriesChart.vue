<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <div class="flex items-center justify-between mb-8">
      <h3
        class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
      >
        {{ t("admin.analytics.performance_trends") }}
      </h3>
      <div class="flex bg-gray-100 dark:bg-gray-900 rounded-2xl p-1.5">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          :class="[
            'px-4 py-2 text-xs font-black rounded-xl transition-all uppercase tracking-widest',
            activeTab === tab.value
              ? 'bg-white dark:bg-gray-800 shadow-lg text-primary-600'
              : 'text-gray-400 hover:text-gray-600',
          ]"
          @click="$emit('update:activeTab', tab.value)"
        >
          {{ t(tab.labelKey) }}
        </button>
      </div>
    </div>
    <div class="h-[400px] relative">
      <canvas :id="chartId" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import Chart from "chart.js/auto";

const { t } = useI18n();

const props = defineProps({
  chartId: {
    type: String,
    default: "timeSeriesChart",
  },
  activeTab: {
    type: String,
    required: true,
  },
  labels: {
    type: Array,
    required: true,
  },
  datasets: {
    type: Object,
    required: true,
  },
});

defineEmits(["update:activeTab"]);

const tabs = [
  { value: "revenue", labelKey: "admin.analytics.tabs.revenue" },
  { value: "streams", labelKey: "admin.analytics.tabs.streams" },
  { value: "signups", labelKey: "admin.analytics.tabs.signups" },
];

let chart = null;

const updateChart = () => {
  const ctx = document.getElementById(props.chartId);
  if (!ctx) return;

  if (chart) chart.destroy();

  const activeDataset = props.datasets[props.activeTab];
  const color =
    props.activeTab === "revenue"
      ? "#10b981"
      : props.activeTab === "streams"
        ? "#3b82f6"
        : "#8b5cf6";

  chart = new Chart(ctx, {
    type: "line",
    data: {
      labels: props.labels,
      datasets: [
        {
          label: props.activeTab.toUpperCase(),
          data: activeDataset,
          borderColor: color,
          backgroundColor: color + "20",
          fill: true,
          tension: 0.4,
          borderWidth: 3,
          pointRadius: 0,
          pointHoverRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: "rgba(200, 200, 200, 0.1)" } },
        x: { grid: { display: false } },
      },
    },
  });
};

watch(
  () => [props.activeTab, props.labels, props.datasets],
  () => {
    updateChart();
  },
  { deep: true },
);

onMounted(() => {
  updateChart();
});

onBeforeUnmount(() => {
  if (chart) chart.destroy();
});
</script>
