<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <div class="flex items-center justify-between mb-8">
      <h3
        class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
      >
        {{ t("admin.growth.funnel.title") }}
      </h3>
    </div>
    <div class="relative flex items-center gap-8 h-[300px] w-full">
      <div class="flex-1 h-full">
        <canvas :id="chartId"/>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, onBeforeUnmount, onMounted, watch} from "vue";
import {useI18n} from "vue-i18n";
import Chart from "chart.js/auto";

const {t} = useI18n();

const props = defineProps({
  chartId: {
    type: String,
    default: "growthFunnelChart",
  },
  data: {
    type: Object,
    required: true,
  },
});

let chart = null;

const conversionRate = computed(() => {
  const reg = props.data.registrations || 0;
  const act = props.data.activated || 0;
  if (!reg) return "0%";
  return ((act / reg) * 100).toFixed(1) + "%";
});

const updateChart = () => {
  const ctx = document.getElementById(props.chartId);
  if (!ctx) return;

  if (chart) chart.destroy();

  const labels = [
    t("admin.growth.funnel.registrations"),
    t("admin.growth.funnel.email_confirmed"),
    t("admin.growth.funnel.stream_created"),
    t("admin.growth.funnel.stream_started"),
    t("admin.growth.funnel.activated"),
    t("admin.growth.funnel.retained_d1"),
  ];

  const values = [
    props.data.registrations || 0,
    props.data.email_confirmed || 0,
    props.data.stream_created || 0,
    props.data.stream_started || 0,
    props.data.activated || 0,
    props.data.retained_d1 || 0,
  ];

  // Calculate conversion rates for labels - relative to previous step
  const stepConversions = values.map((val, index) => {
    if (index === 0) return "100%";
    const prev = values[index - 1];
    return prev > 0 ? ((val / prev) * 100).toFixed(0) + "%" : "0%";
  });

  chart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: t("admin.growth.funnel.users"),
          data: values,
          backgroundColor: [
            "rgba(59, 130, 246, 0.4)", // Blue
            "rgba(59, 130, 246, 0.6)",
            "rgba(99, 102, 241, 0.7)", // Indigo
            "rgba(99, 102, 241, 0.9)",
            "rgba(16, 185, 129, 0.9)", // Green
            "rgba(139, 92, 246, 1.0)", // Purple
          ],
          borderRadius: 8,
          barPercentage: 0.8,
        },
      ],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {display: false},
        tooltip: {
          callbacks: {
            label: (context) => {
              const value = context.raw;
              const rate = stepConversions[context.dataIndex];
              return `${value} Users (${rate} of prev. step)`;
            },
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: {display: false},
        },
        y: {
          grid: {display: false},
          ticks: {
            font: {weight: 'bold', size: 14}
          }
        },
      },
    },
  });
};

watch(
  () => props.data,
  () => {
    updateChart();
  },
  {deep: true},
);

onMounted(() => {
  updateChart();
});

onBeforeUnmount(() => {
  if (chart) chart.destroy();
});
</script>
