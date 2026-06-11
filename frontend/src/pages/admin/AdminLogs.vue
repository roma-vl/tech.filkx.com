<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <AdminLogFilters
      v-model:search="filters.search"
      v-model:domain="filters.domain"
      v-model:per-page="filters.perPage"
      @refresh="fetchLogs(1)"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading && logs.length === 0"
        :text="t('admin.logs.loading')"
      />

      <AdminLogTimeline
        :logs="logs"
        :loading="loading"
        :has-more="meta.currentPage < meta.lastPage"
        @load-more="loadMore"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import debounce from "lodash/debounce";
import {
  ArrowRightOnRectangleIcon,
  CreditCardIcon,
  ExclamationTriangleIcon,
  FingerPrintIcon,
  InformationCircleIcon,
  KeyIcon,
  ShieldCheckIcon,
  TrashIcon,
  UserIcon,
  VideoCameraIcon,
} from "@heroicons/vue/24/outline";
import api from "@/shared/services/api/apiClient";

import AdminLogFilters from "@/components/admin/features/logs/AdminLogFilters.vue";
import AdminLogTimeline from "@/components/admin/features/logs/AdminLogTimeline.vue";
import AppLoadingOverlay from "@/components/admin/ui/AppLoadingOverlay.vue";

const { t } = useI18n();

const logs = ref([]);
const loading = ref(false);
const meta = ref({
  currentPage: 1,
  lastPage: 1,
});

const filters = ref({
  search: "",
  domain: "",
  perPage: 20,
});

// UI Mapping for domains and actions
const getUIConfig = (action, domain) => {
  const domainConfigs = {
    security: { icon: ShieldCheckIcon, color: "bg-indigo-500" },
    billing: { icon: CreditCardIcon, color: "bg-emerald-500" },
    content: { icon: VideoCameraIcon, color: "bg-blue-500" },
    system: { icon: KeyIcon, color: "bg-amber-500" },
    team: { icon: UserIcon, color: "bg-purple-500" },
  };

  const actionConfigs = {
    "auth.login": { icon: FingerPrintIcon, color: "bg-indigo-600" },
    "auth.logout": { icon: ArrowRightOnRectangleIcon, color: "bg-gray-500" },
    "auth.failed": { icon: ExclamationTriangleIcon, color: "bg-red-600" },
    "video.deleted": { icon: TrashIcon, color: "bg-red-500" },
    "user.deleted": { icon: TrashIcon, color: "bg-rose-600" },
  };

  return (
    actionConfigs[action] ||
    domainConfigs[domain] || {
      icon: InformationCircleIcon,
      color: "bg-gray-600",
    }
  );
};

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: filters.value.search,
      domain: filters.value.domain,
      perPage: filters.value.perPage,
    };
    const response = await api.get("/admin/logs", { params });

    const payload = response.data.data;
    const processedLogs = payload.data.map((log) => ({
      ...log,
      ...getUIConfig(log.action, log.domain),
    }));

    if (page === 1) {
      logs.value = processedLogs;
    } else {
      logs.value = [...logs.value, ...processedLogs];
    }

    meta.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
    };
  } catch (error) {
    console.error("Failed to fetch logs:", error);
  } finally {
    loading.value = false;
  }
};

const loadMore = () => {
  if (meta.value.currentPage < meta.value.lastPage) {
    fetchLogs(meta.value.currentPage + 1);
  }
};

const debouncedSearch = debounce(() => {
  fetchLogs(1);
}, 500);

watch(
  () => filters.value.search,
  () => {
    debouncedSearch();
  },
);

watch(
  () => filters.value.domain,
  () => {
    fetchLogs(1);
  },
);

watch(
  () => filters.value.perPage,
  () => {
    fetchLogs(1);
  },
);

onMounted(() => {
  fetchLogs();
});
</script>
