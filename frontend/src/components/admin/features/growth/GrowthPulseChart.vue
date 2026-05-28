<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <div class="flex items-center justify-between mb-8">
      <h3
        class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
      >
        {{ t("admin.growth.pulse.title") }}
      </h3>
    </div>
    <div class="relative h-[300px] w-full">
      <canvas :id="chartId"/>
    </div>
  </div>
</template>

<script setup>
import {onBeforeUnmount, onMounted, watch} from "vue";
import {useI18n} from "vue-i18n";
import Chart from "chart.js/auto";

const {t} = useI18n();

const props = defineProps({
  chartId: {
    type: String,
    default: "growthPulseChart",
  },
  data: {
    type: Array,
    required: true,
  },
  mode: {
    type: String,
    default: "activations", // registrations, activations, streams, value_heads
  },
});

let chart = null;

const updateChart = () => {
  const ctx = document.getElementById(props.chartId);
  if (!ctx) return;

  if (chart) chart.destroy();

  const labels = props.data.map((item) => {
    const date = new Date(item.date);
    return date.toLocaleDateString(undefined, {month: 'short', day: 'numeric'});
  });

  chart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: t("admin.growth.funnel.registrations"),
          data: props.data.map((item) => item.registrations),
          borderColor: "rgba(59, 130, 246, 0.3)", // Faded Blue baseline
          backgroundColor: "rgba(59, 130, 246, 0.05)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderDash: [5, 5],
        },
        {
          label: t(`admin.growth.pulse.modes.${props.mode}`),
          data: props.data.map((item) => item[props.mode] || 0),
          borderColor: props.mode === 'registrations' ? '#3b82f6' : '#10b981', // Blue if reg, green otherwise
          backgroundColor: props.mode === 'registrations' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(16, 185, 129, 0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          borderWidth: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
          labels: {
            usePointStyle: true,
            font: {family: "'Inter', sans-serif"},
          },
        },
        tooltip: {
          mode: 'index',
          intersect: false,
        },
      },
      scales: {
        x: {
          grid: {display: false},
          ticks: {maxRotation: 0, autoSkip: true, maxTicksLimit: 10},
        },
        y: {
          beginAtZero: true,
          grid: {color: "rgba(0,0,0,0.05)"},
        },
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    },
  });
};

watch(
  () => [props.data, props.mode],
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
