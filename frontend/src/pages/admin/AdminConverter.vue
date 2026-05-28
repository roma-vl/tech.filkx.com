<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <AdminVideoFilters
      v-model:search-query="searchQuery"
      v-model:active-filter="activeFilter"
      v-model:view-mode="viewMode"
      :status-options="statusOptions"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="t('admin.dashboard.loading')"
      />

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <AdminVideoCard
          v-for="item in displayItems"
          :key="item.id || item.conversion?.id"
          :video="item"
          :show-standard-actions="false"
          @play="playVideo(item)"
          @recode="recodeVideo(item)"
        >
          <template #extra-actions>
            <button
              @click="showInfo(item)"
              class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
            >
              <InfoIcon class="w-4 h-4 mr-3 text-gray-400" />
              {{ t("admin.converter.info.title") }}
            </button>
          </template>
        </AdminVideoCard>
      </div>

      <!-- List View -->
      <AdminVideoTable
        v-else
        :videos="displayItems"
        :show-standard-actions="false"
        @play="playVideo"
        @recode="recodeVideo"
      >
        <template #extra-actions="{ video }">
           <button
              @click="showInfo(video)"
              class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors hover:text-primary-600"
            >
              <InfoIcon class="w-4 h-4 mr-3 text-gray-400" />
              {{ t("admin.converter.info.title") }}
            </button>
        </template>
      </AdminVideoTable>

      <div v-if="!loading && displayItems.length === 0" class="py-20 text-center">
        <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200 dark:border-gray-700">
          <ZapIcon class="w-10 h-10 text-gray-300" />
        </div>
        <p class="text-gray-500 font-medium">{{ t("common.no_data") }}</p>
      </div>
    </div>

    <AppPagination
      v-if="pagination.lastPage > 1"
      :pagination="pagination"
      @page-change="fetchHistory"
    />

    <!-- Info Modal -->
    <AdminConversionInfoModal
      v-if="selectedConversion"
      :conversion="selectedConversion"
      @close="selectedConversion = null"
      @recode="recodeVideo(selectedConversion)"
    />

    <!-- Player Modal -->
    <AdminVideoPlayerModal
      v-if="videoToPlay"
      :video="videoToPlay"
      @close="videoToPlay = null"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import { ZapIcon, InfoIcon } from "lucide-vue-next";
import api from "@/services/api";
import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import AdminVideoFilters from "@/components/admin/features/video/AdminVideoFilters.vue";
import AdminVideoTable from "@/components/admin/features/video/AdminVideoTable.vue";
import AdminVideoCard from "@/components/admin/features/video/AdminVideoCard.vue";
import AdminVideoPlayerModal from "@/components/admin/features/video/AdminVideoPlayerModal.vue";
import AdminConversionInfoModal from "@/components/admin/features/video/AdminConversionInfoModal.vue";

const { t } = useI18n();
const toast = useToast();

const searchQuery = ref("");
const viewMode = ref("list");
const activeFilter = ref("all");
const loading = ref(false);
const history = ref([]);
const videoToPlay = ref(null);
const selectedConversion = ref(null);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
});

const statusOptions = computed(() => [
  { id: "all", name: t("admin.converter.filters.all") },
  { id: "queued", name: t("admin.converter.filters.queued") },
  { id: "processing", name: t("admin.converter.filters.processing") },
  { id: "ready", name: t("admin.converter.filters.ready") },
  { id: "failed", name: t("admin.converter.filters.failed") },
]);

// Map conversions to a format suitable for AdminVideoCard/Row
const displayItems = computed(() => {
  return history.value.map(item => {
    // If video object exists (not deleted), enrich it with conversion data
    // If deleted, create a virtual video object
    const videoObj = item.video || {};
    const optimizedObj = item.optimizedVideo || {};
    
    // Combine thumbnail data
    const thumbnails = {
      thumbnail: videoObj.thumbnails?.thumbnail || optimizedObj.thumbnails?.thumbnail || null,
      small: videoObj.thumbnails?.small || optimizedObj.thumbnails?.small || null,
      large: videoObj.thumbnails?.large || optimizedObj.thumbnails?.large || null
    };

    return {
      ...videoObj,
      id: videoObj.id || null,
      title: item.originalTitle, // Always show original title
      status: item.status,       // Use conversion status
      type: item.type,           // Use conversion type
      user: item.author,
      author: item.author,      // Ensure both 'user' and 'author' are available
      thumbnails: thumbnails,
      sizeBytes: optimizedObj.sizeBytes || videoObj.sizeBytes || 0,
      createdAt: item.startedAt || videoObj.createdAt,
      conversion: item,          // Keep full conversion data
      isDeleted: !item.video
    };
  });
});

const fetchHistory = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value,
      status: activeFilter.value !== "all" ? activeFilter.value : null,
      perPage: 12,
    };
    const response = await api.get("/admin/videos/transcoding/history", { params });
    const payload = response.data.data;

    history.value = payload.data;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
      total: payload.total,
      perPage: payload.perPage,
    };
  } catch (e) {
    console.error("Failed to fetch transcoding history", e);
    toast.error("Failed to load history");
  } finally {
    loading.value = false;
  }
};

const showInfo = (item) => {
  selectedConversion.value = item.conversion;
};

const playVideo = (item) => {
  if (item.video) {
    videoToPlay.value = item.video;
  } else {
    toast.info(t("admin.converter.info.deleted"));
  }
};

const recodeVideo = async (item) => {
  if (item.isDeleted) {
    toast.info(t("admin.converter.info.deleted"));
    return;
  }

  const videoId = item.id;
  if (!videoId) {
    toast.error("Video ID error");
    return;
  }

  const confirmMsg = item.type === "trial"
    ? t("admin.converter.recodeConfirmTrial")
    : t("admin.converter.recodeConfirm");

  if (!confirm(confirmMsg)) {
    return;
  }

  try {
    await api.post(`/admin/videos/${videoId}/recode`);
    toast.success(t("admin.converter.recodeSuccess"));
    fetchHistory(pagination.value.currentPage);
  } catch (e) {
    toast.error(e.response?.data?.message || "Failed to trigger recoding");
  }
};

let debounceTimer;
watch(searchQuery, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchHistory(1);
  }, 300);
});

watch(activeFilter, () => {
  fetchHistory(1);
});

onMounted(() => {
  fetchHistory();
});
</script>
