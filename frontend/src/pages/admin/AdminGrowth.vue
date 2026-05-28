<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <AppButton
          variant="secondary"
          class="!p-3"
          :loading="loading"
          :title="t('admin.analytics.refresh')"
          @click="init"
        >
          <ArrowPathIcon
            class="w-5 h-5 text-gray-500"
            :class="{ 'animate-spin': loading }"
          />
        </AppButton>
      </div>
    </div>

    <!-- Executive Signals (Am I dying?) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <!-- North Star (Weekly Active Value Heads) -->
      <div
        class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm border-l-4 border-l-primary-500 relative overflow-hidden">
        <div class="flex items-center justify-between mb-1">
          <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
            {{ t("admin.growth.summary.wavh") }}
          </h4>
          <div
            v-if="summary.wavhDelta !== undefined"
            class="px-2 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1"
            :class="summary.wavhDelta >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
          >
            {{ summary.wavhDelta >= 0 ? '↑' : '↓' }} {{ Math.abs(summary.wavhDelta) }}%
          </div>
        </div>
        <div class="text-3xl font-black text-primary-600 dark:text-primary-400">{{ summary.wavh || 0 }}</div>
        <p class="text-[10px] text-gray-400 mt-1 uppercase leading-none">{{ t("admin.growth.summary.wavh_subtitle") }}</p>
      </div>

      <!-- Active Streamers (30d) -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
          {{ t("admin.growth.summary.active_30d") }}</h4>
        <div class="text-3xl font-black text-gray-900 dark:text-white">{{ summary.activeStreamers30d }}</div>
      </div>

      <!-- Avg Restarts (Stability Signal) -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
          {{ t("admin.growth.health.avg_restarts") }}</h4>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-black" :class="health.avgRestarts > 1 ? 'text-red-500' : 'text-green-500'">
              {{ health.avgRestarts }}
            </span>
          <span class="text-xs text-gray-400">/ stream</span>
        </div>
      </div>

      <!-- Returning Users (7d) -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
          {{ t("admin.growth.summary.returning_7d") }}</h4>
        <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400">
          {{ summary.returningUsers7d || 0 }}
        </div>
      </div>
    </div>

    <!-- Pulse Section -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight">
            {{ t("admin.growth.pulse.title") }}
          </h3>
          <p class="text-sm text-gray-500 mt-1">{{ t("admin.growth.pulse.subtitle") }}</p>
        </div>
        <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-900 p-1 rounded-xl">
          <button
            v-for="mode in ['registrations', 'activations', 'streams', 'value_heads']"
            :key="mode"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
            :class="pulseMode === mode ? 'bg-white dark:bg-gray-800 shadow-sm text-primary-600' : 'text-gray-500 hover:text-gray-700'"
            @click="pulseMode = mode"
          >
            {{ t(`admin.growth.pulse.modes.${mode}`) }}
          </button>
        </div>
      </div>
      <GrowthPulseChart :data="pulseData" :mode="pulseMode"/>
    </div>

    <!-- Funnel & Cohorts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <GrowthFunnelChart :data="funnelData"/>
      <CohortRetentionTable :data="cohortData"/>
    </div>

    <!-- Attribution Section -->
    <AttributionSourceStats :data="attributionData"/>
  </div>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import {ArrowPathIcon} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";
import {useToast} from "vue-toastification";
import api from "@/services/api";

import AppButton from "@/components/application/ui/Button/AppButton.vue";
import GrowthPulseChart from "@/components/admin/features/growth/GrowthPulseChart.vue";
import GrowthFunnelChart from "@/components/admin/features/growth/GrowthFunnelChart.vue";
import CohortRetentionTable from "@/components/admin/features/growth/CohortRetentionTable.vue";
import AttributionSourceStats from "@/components/admin/features/growth/AttributionSourceStats.vue";

const {t} = useI18n();
const toast = useToast();

const loading = ref(false);
const pulseData = ref([]);
const pulseMode = ref("activations");
const funnelData = ref({
  registrations: 0,
  email_confirmed: 0,
  stream_created: 0,
  stream_started: 0,
  activated: 0,
  retained_d1: 0,
});
const health = ref({
  avgRestarts: 0,
  failureRate: 0,
});
const attributionData = ref({});
const cohortData = ref([]);
const summary = ref({
  wavh: 0,
  wavhDelta: 0,
  returningUsers7d: 0,
  activeStreamers30d: 0,
  activationRate: 0,
});

const activationRate = computed(() => {
  const reg = funnelData.value.registrations || 0;
  const act = funnelData.value.activated || 0;
  if (!reg) return "0%";
  return ((act / reg) * 100).toFixed(1) + "%";
});

const init = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/analytics/growth");
    const data = response.data.data;

    pulseData.value = data.pulse;
    funnelData.value = {
      registrations: data.funnel.registrations,
      email_confirmed: data.funnel.email_confirmed || 0,
      stream_created: data.funnel.stream_created || 0,
      stream_started: data.funnel.stream_started || 0,
      activated: data.funnel.activated || 0,
      retained_d1: data.funnel.retained_d1 || 0,
    };
    health.value = {
      avgRestarts: data.health?.avg_restarts || 0,
      failureRate: data.health?.failure_rate || 0,
    };
    attributionData.value = data.sources;
    cohortData.value = data.cohorts;
    summary.value = {
      wavh: data.summary.wavh,
      wavhDelta: data.summary.wavh_delta,
      returningUsers7d: data.summary.returning_users_7d,
      activeStreamers30d: data.summary.active_streamers_30d,
      activationRate: data.summary.activation_rate,
    };
  } catch (err) {
    console.error("Failed to load growth analytics:", err);
    toast.error(t("admin.growth.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  init();
});

defineExpose({refresh: init});
</script>
