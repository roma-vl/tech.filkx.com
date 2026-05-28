<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <AdminPlaylistFilters
      v-model:search-query="searchQuery"
      v-model:view-mode="viewMode"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="t('admin.playlists.loading')"
      />

      <div
        v-if="viewMode === 'grid'"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <AdminPlaylistCard
          v-for="playlist in playlists"
          :key="playlist.id"
          :playlist="playlist"
          @view="viewPlaylist"
          @edit="editPlaylist"
          @delete="confirmDelete"
          @play="playVideo"
        />
      </div>

      <AdminPlaylistTable
        v-else
        :playlists="playlists"
        @view="viewPlaylist"
        @edit="editPlaylist"
        @delete="confirmDelete"
        @play="playVideo"
      />
    </div>

    <AppPagination
      v-if="pagination.lastPage > 1"
      :pagination="pagination"
      @page-change="fetchPlaylists"
    />

    <AdminVideoPlayerModal
      v-if="videoToPlay"
      :video="videoToPlay"
      @close="videoToPlay = null"
    />

    <PlaylistViewModal
      :is-open="!!playlistToView"
      :playlist="playlistToView"
      @close="playlistToView = null"
    />

    <AdminEditPlaylistModal
      :is-open="!!playlistToEdit"
      :playlist="playlistToEdit"
      @close="playlistToEdit = null"
      @saved="fetchPlaylists(pagination.currentPage)"
    />

    <AdminDeletePlaylistModal
      :is-open="!!playlistToDelete"
      :playlist="playlistToDelete"
      @close="playlistToDelete = null"
      @deleted="fetchPlaylists(pagination.currentPage)"
    />
  </div>
</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import {useI18n} from "vue-i18n";
import api from "@/services/api";

import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import AdminPlaylistFilters from "@/components/admin/features/playlist/AdminPlaylistFilters.vue";
import AdminPlaylistTable from "@/components/admin/features/playlist/AdminPlaylistTable.vue";
import AdminPlaylistCard from "@/components/admin/features/playlist/AdminPlaylistCard.vue";
import PlaylistViewModal from "@/components/admin/features/playlist/PlaylistViewModal.vue";
import AdminEditPlaylistModal from "@/components/admin/features/playlist/AdminEditPlaylistModal.vue";
import AdminDeletePlaylistModal from "@/components/admin/features/playlist/AdminDeletePlaylistModal.vue";
import AdminVideoPlayerModal from "@/components/admin/features/video/AdminVideoPlayerModal.vue";

const {t} = useI18n();

const searchQuery = ref("");
const viewMode = ref("grid");
const loading = ref(false);
const playlists = ref([]);

const pagination = ref({
  current_page: 1,
  last_page: 1,
});

const playlistToView = ref(null);
const playlistToEdit = ref(null);
const playlistToDelete = ref(null);
const videoToPlay = ref(null);

const fetchPlaylists = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value,
      perPage: 12,
    };
    const response = await api.get("/admin/playlists", { params });
    const payload = response.data.data;

    playlists.value = payload.data;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
    };
  } catch (e) {
    console.error("Failed to fetch playlists", e);
  } finally {
    loading.value = false;
  }
};

const playVideo = async (playlist) => {
  if (!playlist.videos || playlist.videos.length === 0) return;
  const videoInfo = playlist.videos[0];
  try {
    const response = await api.get(`/admin/videos/${videoInfo.id}`);
    videoToPlay.value = response.data.data;
  } catch (e) {
    console.error(e);
  }
};

const viewPlaylist = (playlist) => {
  playlistToView.value = playlist;
};

const editPlaylist = (playlist) => {
  playlistToEdit.value = playlist;
};

const confirmDelete = (playlist) => {
  playlistToDelete.value = playlist;
};

let debounceTimer;
watch(searchQuery, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchPlaylists(1);
  }, 300);
});

onMounted(() => {
  fetchPlaylists();
});
</script>
