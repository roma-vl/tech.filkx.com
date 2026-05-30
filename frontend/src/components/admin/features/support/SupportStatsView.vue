<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="stat in summaryStats"
        :key="stat.label"
        class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm"
      >
        <p
          class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2"
        >
          {{ stat.label }}
        </p>
        <div class="flex items-end gap-2">
          <p class="text-3xl font-black text-gray-900 dark:text-white">
            {{ stat.value }}
          </p>
          <p
            v-if="stat.trend"
            :class="[
              'text-xs font-bold mb-1',
              stat.trend > 0 ? 'text-green-500' : 'text-red-500',
            ]"
          >
            {{ stat.trend > 0 ? "+" : "" }}{{ stat.trend }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Activity Chart -->
    <div
      class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm"
    >
      <div class="flex items-center justify-between mb-8">
        <div>
          <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">
            {{ t("admin.support.stats.activity_title") }}
          </h3>
          <p class="text-xs text-gray-400 font-medium">
            {{ t("admin.support.stats.activity_subtitle") }}
          </p>
        </div>
        <select
          v-model="timeRange"
          class="bg-gray-50 dark:bg-gray-900 border-none rounded-lg text-[10px] font-black uppercase tracking-widest px-4 py-2 outline-none cursor-pointer"
        >
          <option value="7d">
            Last 7 Days
          </option>
          <option value="30d">
            Last 30 Days
          </option>
        </select>
      </div>

      <div class="h-[400px] relative">
        <canvas ref="chartCanvas" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import axios from "@/services/api";
import Chart from "chart.js/auto";

const { t } = useI18n();
const chartCanvas = ref(null);
const chartInstance = ref(null);
const statsData = ref(null);
const timeRange = ref("30d");

const summaryStats = computed(() => [
  {
    label: t("admin.support.stats.total_tickets"),
    value: statsData.value?.totalTickets || 0,
    trend: 12,
  },
  {
    label: t("admin.support.stats.resolved_tickets"),
    value: statsData.value?.resolvedTickets || 0,
    trend: 8,
  },
  { label: t("admin.support.stats.avg_resolution"), value: "2.4h", trend: -5 },
]);

const fetchStats = async () => {
  try {
    const response = await axios.get("/admin/support/stats");
    statsData.value = response.data.data;
    renderChart();
  } catch (error) {
    console.error("Failed to fetch stats", error);
  }
};

const renderChart = () => {
  if (!chartCanvas.value || !statsData.value) return;

  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  const ctx = chartCanvas.value.getContext("2d");
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, "rgba(59, 130, 246, 0.2)");
  gradient.addColorStop(1, "rgba(59, 130, 246, 0)");

  chartInstance.value = new Chart(ctx, {
    type: "line",
    data: {
      labels: statsData.value.chartData.map((d) => d.date),
      datasets: [
        {
          label: t("admin.support.stats.tickets"),
          data: statsData.value.chartData.map((d) => d.total),
          borderColor: "#3b82f6",
          borderWidth: 4,
          pointBackgroundColor: "#fff",
          pointBorderColor: "#3b82f6",
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          backgroundColor: gradient,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#1f2937",
          padding: 12,
          titleFont: { size: 12, weight: "bold" },
          bodyFont: { size: 12 },
          displayColors: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: "rgba(156, 163, 175, 0.1)", drawBorder: false },
          ticks: { font: { size: 10, weight: "bold" }, color: "#9ca3af" },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10, weight: "bold" }, color: "#9ca3af" },
        },
      },
    },
  });
};

watch(timeRange, fetchStats);

onMounted(fetchStats);
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
