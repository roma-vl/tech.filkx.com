<template>
  <AppModal
    :model-value="open"
    :title="t('media.editPlaylistModal.title')"
    max-width="5xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-6 sm:p-2">
      <!-- Header / Name Input -->
      <div
        class="bg-white/50 dark:bg-gray-800/30 p-5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 shadow-sm backdrop-blur-sm"
      >
        <AppInput
          v-model="form.name"
          :label="t('media.editPlaylistModal.nameLabel')"
          class="!mb-0"
        />
      </div>

      <!-- Dual Pane UX -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- LEFT PANE: Available Videos -->
        <div
          class="flex flex-col h-[500px] bg-gray-50/50 dark:bg-gray-800/20 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 shadow-inner overflow-hidden"
        >
          <div
            class="p-4 border-b border-gray-200/60 dark:border-gray-700/60 bg-white/40 dark:bg-gray-800/40 backdrop-blur-sm flex items-center justify-between"
          >
            <h4
              class="font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2"
            >
              <LibraryIcon class="w-5 h-5 text-primary-500" />
              {{ t("media.editPlaylistModal.availableVideos") }}
            </h4>
            <span
              class="text-xs font-bold text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-1 rounded-md"
            >
              {{ availableVideos.length }} items
            </span>
          </div>

          <!-- Video Search (Client-side fast search) -->
          <div
            class="p-3 border-b border-gray-200/60 dark:border-gray-700/60 bg-white/20 dark:bg-gray-800/20"
          >
            <div class="relative">
              <SearchIcon
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
              />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search videos..."
                class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
              >
            </div>
          </div>

          <div
            id="tour-playlist-video-select"
            class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar"
          >
            <template v-if="filteredAvailableVideos.length">
              <div
                v-for="video in filteredAvailableVideos"
                :key="video.id"
                class="group relative flex items-center gap-3 p-2.5 rounded-xl border transition-all duration-200 bg-white dark:bg-gray-800"
                :class="[
                  isSelected(video.id)
                    ? 'border-primary-500 ring-1 ring-primary-500 bg-primary-50/30 dark:bg-primary-900/10'
                    : 'border-gray-200/60 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-sm cursor-pointer',
                  isVideoDisabled(video)
                    ? 'opacity-50 grayscale cursor-not-allowed'
                    : '',
                ]"
                @click="
                  !isVideoDisabled(video) &&
                    !isSelected(video.id) &&
                    toggle(video)
                "
              >
                <!-- Selection Indicator -->
                <div
                  v-if="isSelected(video.id)"
                  class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[1px] rounded-xl flex items-center justify-center z-10"
                >
                  <div
                    class="bg-primary-500 text-white p-2 rounded-full shadow-lg transform scale-110"
                  >
                    <CheckIcon class="w-5 h-5" />
                  </div>
                </div>

                <div class="relative shrink-0">
                  <img
                    :src="video.thumbnails?.thumbnail"
                    class="w-20 h-12 object-cover rounded-md bg-gray-200 dark:bg-gray-700"
                  >
                  <div
                    class="absolute bottom-1 right-1 px-1 py-0.5 rounded bg-black/70 backdrop-blur-sm text-[9px] font-bold text-white leading-none"
                  >
                    {{ video.durationFormatted || "00:00" }}
                  </div>
                </div>

                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate pr-6 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                  >
                    {{ video.title }}
                  </p>
                  <!-- Preview-only label for locked originals -->
                  <div
                    v-if="video.isOriginalLocked"
                    class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/50"
                  >
                    <LockIcon class="w-2.5 h-2.5" />
                    {{ t("media.videos.status.streamingRestricted") }}
                  </div>
                  <div
                    v-else-if="video.resolutionLabel"
                    class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                    :class="
                      getResolutionClass(
                        video.resolutionLabel,
                        isVideoDisabled(video),
                      )
                    "
                  >
                    <MonitorIcon class="w-2.5 h-2.5" />
                    {{ video.resolutionLabel }}
                    <span
                      v-if="!isVideoSupported(video)"
                      class="ml-0.5 opacity-70"
                    >({{ t("common.notSupported") }})</span>
                  </div>
                </div>

                <div
                  v-if="!isSelected(video.id) && !isVideoDisabled(video)"
                  class="absolute right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <div
                    class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-900/30 dark:hover:text-primary-400"
                  >
                    <PlusIcon class="w-4 h-4" />
                  </div>
                </div>
              </div>
            </template>
            <div
              v-else
              class="h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 space-y-2 opacity-60"
            >
              <SearchXIcon class="w-8 h-8" />
              <p class="text-sm font-medium">
                No videos found
              </p>
            </div>
          </div>
        </div>

        <!-- RIGHT PANE: Selected Videos (Playlist) -->
        <div
          class="flex flex-col h-[500px] bg-white/60 dark:bg-gray-800/40 rounded-2xl border border-primary-200/50 dark:border-primary-800/50 shadow-sm overflow-hidden backdrop-blur-md relative"
        >
          <!-- Decorative glow -->
          <div
            class="absolute top-0 right-0 w-64 h-64 bg-primary-500/5 rounded-full blur-3xl -z-10 pointer-events-none"
          />

          <div
            class="p-4 border-b border-gray-200/60 dark:border-gray-700/60 bg-white/40 dark:bg-gray-800/40 backdrop-blur-sm flex items-center justify-between z-10"
          >
            <h4
              class="font-semibold text-gray-900 dark:text-white flex items-center gap-2"
            >
              <ListVideoIcon class="w-5 h-5 text-primary-500" />
              {{ t("media.editPlaylistModal.selectedVideos") }}
            </h4>
            <span
              class="text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-100 dark:bg-primary-900/50 px-2 py-1 rounded-md border border-primary-200 dark:border-primary-800"
            >
              {{ selectedVideos.length }} selected
            </span>
          </div>

          <div class="flex-1 overflow-y-auto p-3 custom-scrollbar z-10">
            <template v-if="selectedVideos.length > 0">
              <div id="tour-playlist-sort">
                <Draggable
                  v-model="selectedVideos"
                  item-key="id"
                  handle=".drag-handle"
                  class="space-y-2"
                  ghost-class="opacity-50"
                  drag-class="sortable-drag"
                  animation="200"
                >
                  <template #item="{ element, index }">
                    <div
                      class="group flex items-center gap-3 p-2.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all"
                    >
                      <div
                        class="drag-handle cursor-grab active:cursor-grabbing p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                      >
                        <GripVerticalIcon class="w-5 h-5" />
                      </div>

                      <div
                        class="flex-1 min-w-0 flex items-center gap-3 bg-gray-50 rounded-lg dark:bg-gray-900/50 pr-2"
                      >
                        <span
                          class="text-[10px] font-mono font-bold text-gray-400 dark:text-gray-500 w-5 text-center shrink-0 ml-2"
                        >
                          {{ index + 1 }}
                        </span>
                        <img
                          :src="element.thumbnails?.thumbnail"
                          class="w-12 h-8 object-cover rounded shadow-sm shrink-0"
                        >
                        <span
                          class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 py-2"
                        >
                          {{ element.title }}
                        </span>
                      </div>

                      <button
                        class="shrink-0 p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        title="Remove from playlist"
                        @click="remove(element.id)"
                      >
                        <XIcon class="w-5 h-5" />
                      </button>
                    </div>
                  </template>
                </Draggable>
              </div>
            </template>

            <div
              v-else
              class="h-full flex flex-col items-center justify-center text-center space-y-4 px-6 opacity-60"
            >
              <div
                class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-600"
              >
                <MousePointerClickIcon class="w-8 h-8 text-gray-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                  Playlist is empty
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  Select videos from the left pane to add them here.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full sm:px-2 pb-2">
        <AppButton
          variant="white"
          class="hover:bg-gray-50 dark:hover:bg-gray-800"
          @click="$emit('close')"
        >
          {{ t("media.editPlaylistModal.cancelButton") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="saving"
          :disabled="!form.name"
          class="shadow-lg shadow-primary-500/25 px-8"
          @click="save"
        >
          {{
            saving
              ? t("media.editPlaylistModal.savingButton")
              : t("media.editPlaylistModal.saveButton")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, watch, ref } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api.js";
import Draggable from "vuedraggable";
import { usePlaylistForm } from "@/composables/usePlaylistForm";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {
  MonitorIcon,
  SearchIcon,
  LibraryIcon,
  ListVideoIcon,
  CheckIcon,
  LockIcon,
  PlusIcon,
  XIcon,
  GripVerticalIcon,
  SearchXIcon,
  MousePointerClickIcon,
} from "lucide-vue-next";
import { useSubscriptionStore } from "@/stores/subscription";

const { t } = useI18n();

const searchQuery = ref("");

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  playlist: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "saved"]);

const {
  form,
  videos,
  selectedVideos,
  saving,
  fetchVideos,
  isSelected,
  toggle,
  remove,
  buildPayload,
} = usePlaylistForm();

const availableVideos = computed(() => {
  return videos.value.filter((v) => !isSelected(v.id));
});

const filteredAvailableVideos = computed(() => {
  if (!searchQuery.value) return availableVideos.value;
  const q = searchQuery.value.toLowerCase();
  return availableVideos.value.filter((v) => v.title.toLowerCase().includes(q));
});

watch(
  () => props.open,
  async (open) => {
    if (!open || !props.playlist) return;

    await fetchVideos();
    form.value.name = props.playlist.name;
    selectedVideos.value = props.playlist.videos.map((v, i) => ({
      ...v,
      order: i + 1,
    }));
  },
  { immediate: true },
);

const subscriptionStore = useSubscriptionStore();

const planStreamQuality = computed(() => {
  return subscriptionStore.subscription?.features?.stream_quality || "hd";
});

const isVideoSupported = (video) => {
  const label = video.resolutionLabel;
  const quality = planStreamQuality.value;

  if (quality === "4k") return true;
  if (quality === "hd") return label !== "4K";
  if (quality === "sd") return label === "SD" || label === "720p";
  return true;
};

const isVideoDisabled = (video) => {
  return (
    video.isOriginalLocked ||
    !isVideoSupported(video) ||
    (video.status !== "ready" && video.status !== "uploaded")
  );
};

const getResolutionClass = (label, isDisabled) => {
  if (isDisabled && !isVideoSupported({ resolutionLabel: label })) {
    return "bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50";
  }
  if (isDisabled) {
    return "bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-600 dark:border-gray-700";
  }
  if (label === "4K")
    return "bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/50";
  if (label === "1080p")
    return "bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50";
  if (label === "720p")
    return "bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50";
  return "bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700";
};

const save = async () => {
  saving.value = true;
  try {
    const { data } = await api.post(
      `/playlists/${props.playlist.id}/videos/sync`,
      buildPayload(),
    );

    emit("saved", data.data);
    emit("close");
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(107, 114, 128, 0.8);
}
.sortable-drag {
  box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  transform: scale(1.02) rotate(1deg);
}
</style>
