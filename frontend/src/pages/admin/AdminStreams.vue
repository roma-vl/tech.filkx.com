<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <AdminStreamFilters
      v-model:search-query="searchQuery"
      v-model:active-filter="activeFilter"
      v-model:view-mode="viewMode"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="t('admin.streams.loading')"
      />

      <div
        v-if="viewMode === 'grid'"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <AdminStreamCard
          v-for="stream in streams"
          :key="stream.id"
          :stream="stream"
          :live-duration="formatLiveDuration(stream.startedAt)"
          @play="playVideo"
          @edit="editStream"
          @delete="confirmDelete"
        />
      </div>

      <AdminStreamTable
        v-else
        :streams="streams"
        @play="playVideo"
        @edit="editStream"
        @delete="confirmDelete"
      />
    </div>

    <AppPagination
      v-if="pagination.lastPage > 1"
      :pagination="pagination"
      @page-change="fetchStreams"
    />

    <AdminVideoPlayerModal
      v-if="videoToPlay"
      :video="videoToPlay"
      @close="videoToPlay = null"
    />

    <AdminEditStreamModal
      :is-open="!!streamToEdit"
      :stream="streamToEdit"
      @close="streamToEdit = null"
      @saved="fetchStreams(pagination.currentPage)"
    />

    <AdminDeleteStreamModal
      :is-open="!!streamToDelete"
      :stream="streamToDelete"
      @close="streamToDelete = null"
      @deleted="fetchStreams(pagination.currentPage)"
    />
  </div>
</template>

<script setup>
import {onMounted, ref, watch, onUnmounted} from "vue";
import {useI18n} from "vue-i18n";
import api from "@/services/api";

import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import AdminStreamFilters from "@/components/admin/features/stream/AdminStreamFilters.vue";
import AdminStreamTable from "@/components/admin/features/stream/AdminStreamTable.vue";
import AdminStreamCard from "@/components/admin/features/stream/AdminStreamCard.vue";
import AdminEditStreamModal from "@/components/admin/features/stream/AdminEditStreamModal.vue";
import AdminDeleteStreamModal from "@/components/admin/features/stream/AdminDeleteStreamModal.vue";
import AdminVideoPlayerModal from "@/components/admin/features/video/AdminVideoPlayerModal.vue";

const {t} = useI18n();

const searchQuery = ref("");
const viewMode = ref("grid");
const activeFilter = ref("live");
const loading = ref(false);
const streams = ref([]);
const totalItems = ref(0);

const pagination = ref({
  current_page: 1,
  last_page: 1,
});

const streamToEdit = ref(null);
const streamToDelete = ref(null);
const videoToPlay = ref(null);

const now = ref(Date.now());
let timer = null;

onMounted(() => {
  timer = setInterval(() => {
    now.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const formatLiveDuration = (startedAt) => {
  if (!startedAt) return null;

  const start = new Date(startedAt).getTime();
  const diff = Math.max(0, Math.floor((now.value - start) / 1000));

  const hours = Math.floor(diff / 3600);
  const minutes = Math.floor((diff % 3600) / 60);
  const seconds = diff % 60;

  return [
    hours.toString().padStart(2, "0"),
    minutes.toString().padStart(2, "0"),
    seconds.toString().padStart(2, "0"),
  ].join(":");
};

const fetchStreams = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value,
      status: activeFilter.value !== "all" ? activeFilter.value : null,
      perPage: 12,
    };
    const response = await api.get("/admin/streams", { params });
    const payload = response.data.data;

    streams.value = payload.data;
    totalItems.value = payload.total || 0;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
      total: payload.total,
      perPage: payload.perPage,
    };
  } catch (e) {
    console.error("Failed to fetch streams", e);
  } finally {
    loading.value = false;
  }
};

const playVideo = async (videoInfo) => {
  try {
    const response = await api.get(`/admin/videos/${videoInfo.id}`);
    videoToPlay.value = response.data.data;
  } catch (e) {
    console.error(e);
  }
};

const editStream = (stream) => {
  streamToEdit.value = stream;
};

const confirmDelete = (stream) => {
  streamToDelete.value = stream;
};

let debounceTimer;
watch(searchQuery, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchStreams(1);
  }, 300);
});

watch(activeFilter, () => {
  fetchStreams(1);
});

onMounted(() => {
  fetchStreams();
});
</script>
