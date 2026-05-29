<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-2"
    >
      <AppButton
        variant="white"
        class="!px-6 !py-3 !rounded-2xl"
        :loading="loading"
        @click="fetchHealth"
      >
        <template #prepend>
          <ArrowPathIcon class="w-4 h-4"/>
        </template>
        {{ t("admin.system.refresh") }}
      </AppButton>
    </div>

    <!-- Main Status Banner -->
    <SystemHealthStatus
      :uptime="health.server?.uptime"
      :loading="loading"
    />

    <!-- Resource Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- CPU Usage -->
      <ResourceCard
        type="progress"
        :icon="CpuChipIcon"
        title="Processing"
        subtitle="CPU Cluster"
        label="Load Average"
        :value="health.server?.cpu?.percentage || 0"
        unit="%"
        :percentage="health.server?.cpu?.percentage || 0"
        :trend="0"
      />

      <!-- RAM Usage -->
      <ResourceCard
        type="progress"
        :icon="CircleStackIcon"
        title="Memory"
        subtitle="RAM Usage"
        label="Physical Memory"
        :value="health.server?.ram?.used || '0'"
        unit=" GB"
        :percentage="health.server?.ram?.percentage || 0"
        :trend="0"
      />

      <!-- Disk Storage -->
      <ResourceCard
        type="circular"
        :icon="FolderIcon"
        title="Storage"
        subtitle="Disk Space"
        label="Used"
        :value="health.server?.disk?.used || '0'"
        :total="health.server?.disk?.total || '0'"
        unit=" GB"
        :percentage="health.server?.disk?.percentage || 0"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Network Monitoring (NEW) -->
      <NetworkCard
        :incoming="health.network?.incoming || 0"
        :outgoing="health.network?.outgoing || 0"
        :total-received="health.network?.totalReceived || 0"
        :total-sent="health.network?.totalSent || 0"
      />

      <!-- Streaming SaaS Metrics (NEW) -->
      <StreamingStatsCard
        :active-streams="streamingStats.active"
        :encoder-load="streamingStats.encoderLoad"
        :streaming-jobs="streamingStats.jobs"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Database Cluster -->
      <div class="lg:col-span-1">
        <ResourceCard
          type="stats"
          :icon="CircleStackIcon"
          title="Cluster"
          subtitle="Database"
          :stats="[
            {
              label: 'Connections',
              value: `${health.database?.connections || 0} / ${health.database?.maxConnections || 0}`,
            },
            { label: 'Latency', value: `${health.database?.latency || 0}ms` },
            {
              label: 'Health Status',
              value: health.database?.status || 'Active',
              variant: 'success',
            },
          ]"
        />
      </div>

      <!-- Services List -->
      <div class="lg:col-span-2">
        <ServicesTable
          :services="health.services"
          :loading="loading"
          @refresh="fetchHealth"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, onUnmounted, ref} from "vue";
import {useI18n} from "vue-i18n";
import {ArrowPathIcon, CircleStackIcon, CpuChipIcon, FolderIcon,} from "@heroicons/vue/24/outline";
import api from "@/services/api";

// Feature Components
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import SystemHealthStatus from "@/components/admin/features/system/SystemHealthStatus.vue";
import ResourceCard from "@/components/admin/features/system/ResourceCard.vue";
import NetworkCard from "@/components/admin/features/system/NetworkCard.vue";
import StreamingStatsCard from "@/components/admin/features/system/StreamingStatsCard.vue";
import ServicesTable from "@/components/admin/features/system/ServicesTable.vue";

const {t} = useI18n();

const health = ref({
  server: { cpu: {}, ram: {}, disk: {} },
  database: {},
  services: [],
  network: {incoming: 0, outgoing: 0},
  streaming: {activeStreams: 0, encoderLoad: 0, jobs: []},
});
const loading = ref(true);
let pollInterval = null;

const streamingStats = computed(() => ({
  active: health.value.streaming?.activeStreams || 0,
  encoderLoad: health.value.streaming?.encoderLoad || 0,
  jobs: health.value.streaming?.jobs || [],
}));

const fetchHealth = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/system/health");
    health.value = response.data.data;
  } catch (error) {
    console.error("Failed to fetch system health", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchHealth();
  pollInterval = setInterval(fetchHealth, 15000); // Polling every 15s for more "live" feeling
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>
