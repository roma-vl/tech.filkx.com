<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <h3
      class="font-black text-xl mb-8 text-gray-900 dark:text-white tracking-tight"
    >
      {{ t("admin.analytics.plan_adoption") }}
    </h3>
    <div class="h-[250px] relative">
      <canvas :id="chartId" />
    </div>
  </div>
</template>

<script setup>
import { watch, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import Chart from "chart.js/auto";

const { t } = useI18n();

const props = defineProps({
  chartId: {
    type: String,
    default: "planChart",
  },
  plans: {
    type: Array,
    required: true,
  },
});

let chart = null;

const updateChart = () => {
  const ctx = document.getElementById(props.chartId);
  if (!ctx) return;

  if (chart) chart.destroy();

  chart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: props.plans.map((p) => p.label),
      datasets: [
        {
          data: props.plans.map((p) => p.value),
          backgroundColor: [
            "#3b82f6",
            "#10b981",
            "#f59e0b",
            "#8b5cf6",
            "#ef4444",
          ],
          borderWidth: 0,
          cutout: "70%",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            usePointStyle: true,
            boxWidth: 6,
            font: { size: 10, weight: "bold" },
            padding: 12,
          },
        },
      },
    },
  });
};

watch(
  () => props.plans,
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
