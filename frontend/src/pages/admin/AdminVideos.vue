<template>
  <div class="space-y-8 animate-in fade-in duration-500">

    <AdminVideoFilters
      v-model:search-query="searchQuery"
      v-model:view-mode="viewMode"
      v-model:active-filter="activeFilter"
      :total-count="totalItems"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="$t('admin.videos.loading')"
      />

      <AdminVideoGrid
        v-if="viewMode === 'grid' && videos.length > 0"
        :videos="videos"
        @play="playVideo"
        @edit="editVideo"
        @delete="confirmDelete"
        @recode="recodeVideo"
      />

      <AdminVideoTable
        v-else-if="viewMode === 'list' && videos.length > 0"
        :videos="videos"
        @play="playVideo"
        @edit="editVideo"
        @delete="confirmDelete"
        @recode="recodeVideo"
      />

      <div
        v-if="!loading && videos.length === 0"
        class="py-20 text-center"
      >
        <div
          class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200 dark:border-gray-700"
        >
          <VideoCameraIcon class="w-10 h-10 text-gray-300" />
        </div>
        <p class="text-gray-500 font-medium">
          {{ $t("admin.videos.empty") }}
        </p>
      </div>
    </div>

    <AppPagination
      v-if="pagination.lastPage > 1"
      :pagination="pagination"
      @page-change="fetchVideos"
    />

    <AdminVideoPlayerModal
      v-if="videoToPlay"
      :video="videoToPlay"
      @close="videoToPlay = null"
    />

    <AdminEditVideoModal
      :is-open="!!videoToEdit"
      :video="videoToEdit"
      @close="videoToEdit = null"
      @saved="fetchVideos(pagination.currentPage)"
    />

    <AdminDeleteVideoModal
      :is-open="!!videoToDelete"
      :video="videoToDelete"
      @close="videoToDelete = null"
      @deleted="fetchVideos(pagination.currentPage)"
    />
  </div>
</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import {useI18n} from "vue-i18n";
import {useToast} from "vue-toastification";
import {VideoCameraIcon} from "@heroicons/vue/24/outline";
import api from "@/services/api";
import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import AdminVideoFilters from "@/components/admin/features/video/AdminVideoFilters.vue";
import AdminVideoGrid from "@/components/admin/features/video/AdminVideoGrid.vue";
import AdminVideoTable from "@/components/admin/features/video/AdminVideoTable.vue";
import AdminVideoPlayerModal from "@/components/admin/features/video/AdminVideoPlayerModal.vue";
import AdminEditVideoModal from "@/components/admin/features/video/AdminEditVideoModal.vue";
import AdminDeleteVideoModal from "@/components/admin/features/video/AdminDeleteVideoModal.vue";

const {t} = useI18n();
const toast = useToast();

const searchQuery = ref("");
const viewMode = ref("grid");
const activeFilter = ref("all");
const loading = ref(false);
const videos = ref([]);
const totalItems = ref(0);

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
});

const videoToPlay = ref(null);
const videoToEdit = ref(null);
const videoToDelete = ref(null);

const fetchVideos = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value,
      status: activeFilter.value !== "all" ? activeFilter.value : null,
      perPage: 12,
    };
    const response = await api.get("/admin/videos", { params });
    const payload = response.data.data;

    videos.value = payload.data;
    totalItems.value = payload.total || 0;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
      total: payload.total,
      perPage: payload.perPage,
    };
  } catch (e) {
    console.error("Failed to fetch videos", e);
  } finally {
    loading.value = false;
  }
};

const playVideo = (video) => {
  videoToPlay.value = video;
};

const editVideo = (video) => {
  videoToEdit.value = video;
};

const confirmDelete = (video) => {
  videoToDelete.value = video;
};

const recodeVideo = async (video) => {
  if (!confirm(t("admin.converter.recodeConfirm"))) {
    return;
  }

  try {
    await api.post(`/admin/videos/${video.id}/recode`);
    toast.success(t("admin.converter.recodeSuccess"));
    fetchVideos(pagination.value.currentPage);
  } catch (e) {
    toast.error(e.response?.data?.message || "Failed to trigger recoding");
  }
};

let debounceTimer;
watch(searchQuery, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchVideos(1);
  }, 300);
});

watch(activeFilter, () => {
  fetchVideos(1);
});

onMounted(() => {
  fetchVideos();
});
</script>
