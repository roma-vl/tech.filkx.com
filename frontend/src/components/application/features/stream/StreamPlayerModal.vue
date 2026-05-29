<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 bg-black/95 flex items-center justify-center z-[100] backdrop-blur-sm"
      @click.self="close"
    >
      <div
        class="relative w-full max-w-7xl mx-4 flex flex-col md:flex-row bg-white dark:bg-gray-950 rounded-3xl overflow-hidden shadow-2xl border border-gray-200 dark:border-white/10 h-[85vh] transition-colors duration-300"
      >
        <button
          id="tour-close-player"
          class="absolute top-5 right-5 z-20 p-2.5 bg-gray-100/80 dark:bg-black/40 hover:bg-gray-200 dark:hover:bg-black/60 text-gray-800 dark:text-white rounded-2xl transition-all shadow-lg backdrop-blur-md"
          @click="close"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <div class="flex-1 flex flex-col min-w-0 bg-black group/player">
          <div
            class="relative flex-1 flex items-center justify-center overflow-hidden"
          >
            <video
              ref="videoPlayer"
              :src="currentVideoUrl"
              controls
              autoplay
              class="w-full h-full max-h-full object-contain shadow-2xl"
              @ended="onVideoEnded"
              @loadedmetadata="onLoadedMetadata"
            >
              {{ t("media.playerModal.browserNotSupported") }}
            </video>

            <div
              v-if="loading"
              class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[2px]"
            >
              <div
                class="w-16 h-16 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin"
              />
            </div>
          </div>

          <div
            class="p-8 bg-white dark:bg-gray-950 border-t border-gray-100 dark:border-white/5"
          >
            <div
              class="flex flex-col md:flex-row md:items-center justify-between gap-6"
            >
              <div class="min-w-0 flex-1">
                <div class="flex items-center flex-wrap gap-2 mb-2">
                  <span
                    v-if="activeObject?.platform"
                    class="px-2.5 py-1 bg-primary-500 text-white text-[10px] font-black uppercase tracking-widest rounded-md shadow-lg shadow-primary-500/20"
                  >
                    {{ activeObject.platform }}
                  </span>
                  <span
                    v-if="stream?.status === 'live'"
                    class="px-2 py-0.5 bg-red-600/10 text-red-600 text-[10px] font-bold uppercase tracking-wider rounded border border-red-600/20 animate-pulse"
                  >
                    LIVE
                  </span>
                  <h3
                    class="text-2xl font-black text-gray-900 dark:text-white truncate"
                  >
                    {{ activeObject?.name || activeObject?.title }}
                  </h3>
                </div>
                <p
                  class="text-gray-500 dark:text-gray-400 text-base font-medium line-clamp-1"
                >
                  {{ currentVideoTitle || stream?.comment }}
                </p>
              </div>

              <div
                class="flex items-center gap-6 text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest whitespace-nowrap"
              >
                <div class="flex items-center">
                  <CalendarIcon class="w-5 h-5 mr-2.5 opacity-40" />
                  {{ formattedDate }}
                </div>
                <div
                  v-if="currentVideo?.durationFormatted"
                  class="flex items-center"
                >
                  <ClockIcon class="w-5 h-5 mr-2.5 opacity-40" />
                  {{ currentVideo.durationFormatted }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          class="w-full md:w-85 bg-gray-50 dark:bg-gray-900/30 backdrop-blur-2xl border-l border-gray-100 dark:border-white/5 flex flex-col h-full transition-colors"
        >
          <div class="p-6 border-b border-gray-100 dark:border-white/5">
            <div class="flex items-center justify-between mb-1">
              <h4
                class="font-black text-gray-900 dark:text-white text-xs uppercase tracking-[0.2em] flex items-center gap-2.5"
              >
                <PlaylistsIcon class="w-4 h-4 text-primary-500" />
                {{
                  isPlaylist
                    ? t("streams.editModal.playlistOption")
                    : t("streams.editModal.videoOption")
                }}
              </h4>
              <span
                v-if="isPlaylist"
                class="text-[10px] font-black px-2.5 py-1 mr-14 bg-primary-500/10 text-primary-600 dark:text-primary-400 rounded-lg border border-primary-500/20"
              >
                {{ currentVideoIndex + 1 }} / {{ playlistItems.length }}
              </span>
            </div>
            <p
              class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-2"
            >
              {{
                isPlaylist
                  ? t("media.playerModal.queue")
                  : t("media.playerModal.videoInfo")
              }}
            </p>
          </div>

          <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
            <div
              v-for="(item, index) in displayItems"
              :key="item ? item.id : index"
              class="group flex gap-4 p-3 rounded-2xl transition-all duration-300 cursor-pointer border border-transparent"
              :class="[
                currentVideoIndex === index
                  ? 'bg-white dark:bg-primary-500/10 border-gray-100 dark:border-primary-500/20 shadow-xl shadow-black/5 ring-1 ring-gray-100 dark:ring-primary-500/20'
                  : 'hover:bg-white dark:hover:bg-white/5 hover:shadow-lg',
              ]"
              @click="selectVideo(index)"
            >
              <div
                class="relative w-28 h-16 flex-shrink-0 rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-800 shadow-inner"
              >
                <img
                  :src="getVideoThumbnail(item)"
                  class="w-full h-full object-cover transition-all duration-500 group-hover:scale-110"
                  :class="{ 'opacity-40 grayscale': currentVideoIndex !== index }"
                >
                <div
                  v-if="currentVideoIndex === index"
                  class="absolute inset-0 flex items-center justify-center bg-primary-500/10"
                >
                  <div class="relative">
                    <div
                      class="w-3 h-3 bg-primary-500 rounded-full animate-ping opacity-75"
                    />
                    <div
                      class="absolute inset-0 w-3 h-3 bg-primary-500 rounded-full"
                    />
                  </div>
                </div>
                <div
                  v-if="currentVideoIndex !== index"
                  class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/20"
                >
                  <div
                    class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 text-white"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <div class="flex-1 min-w-0 flex flex-col justify-center gap-1">
                <p
                  class="text-sm font-black line-clamp-2 transition-colors leading-tight"
                  :class="
                    currentVideoIndex === index
                      ? 'text-primary-500'
                      : 'text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white'
                  "
                >
                  {{ getVideoTitle(item) }}
                </p>
                <div class="flex items-center gap-2">
                  <span
                    v-if="currentVideoIndex === index"
                    class="text-[10px] font-black uppercase text-primary-500/60 tracking-widest"
                  >{{ t("media.playerModal.playing") }}</span>
                  <span
                    v-else
                    class="text-[10px] font-black uppercase text-gray-400 tracking-widest"
                  >{{
                    isPlaylist
                      ? t("media.playerModal.videoIndex", { index: index + 1 })
                      : t("media.playerModal.source")
                  }}</span>
                  <span
                    v-if="(item.video || item)?.durationFormatted"
                    class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-auto"
                  >
                    {{ (item.video || item).durationFormatted }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="activeObject?.platform"
            class="p-6 bg-gray-100/30 dark:bg-black/20 border-t border-gray-100 dark:border-white/5"
          >
            <div
              class="flex items-center justify-between text-gray-400 dark:text-gray-500"
            >
              <span class="text-[10px] font-black uppercase tracking-widest">{{
                t("media.playerModal.provider")
              }}</span>
              <span
                class="text-[10px] font-black uppercase tracking-widest text-primary-500"
              >{{ activeObject.platform }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import CalendarIcon from "@/components/Icon/CalendarIcon.vue";
import PlaylistsIcon from "@/components/Icon/PlaylistsIcon.vue";

const { t } = useI18n();

const props = defineProps({
  stream: {
    type: Object,
    required: false,
    default: null,
  },
  video: {
    type: Object,
    required: false,
    default: null,
  },
  playlist: {
    type: Object,
    required: false,
    default: null,
  },
});

const emit = defineEmits(["close"]);

const videoPlayer = ref(null);
const currentVideoIndex = ref(0);
const loading = ref(false);

const isPlaylist = computed(() => {
  if (props.stream) return props.stream.selectionType === "playlist";
  if (props.playlist) return true;
  return false;
});

const onLoadedMetadata = () => {
  if (!videoPlayer.value) return;

  videoPlayer.value.volume = 0.3; // 50%
};

const activeObject = computed(() => {
  return props.stream || props.playlist || props.video;
});

const playlistItems = computed(() => {
  if (props.stream) return props.stream.playlist?.videos || [];
  if (props.playlist) return props.playlist.videos || [];
  return [];
});

const displayItems = computed(() => {
  if (isPlaylist.value) {
    return playlistItems.value;
  }
  if (props.stream) return [props.stream.video];
  if (props.video) return [props.video];
  return [];
});

const getSelectedVideo = () => {
  if (isPlaylist.value) {
    const item = playlistItems.value[currentVideoIndex.value];
    return item?.video || item;
  }
  if (props.stream) return props.stream.video;
  return props.video;
};

const currentVideo = computed(() => getSelectedVideo());

const currentVideoUrl = computed(() => {
  const video = getSelectedVideo();
  return video?.videoUrl || video?.url || "";
});

const currentVideoTitle = computed(() => {
  const video = getSelectedVideo();
  return video?.title || "";
});

const formattedDate = computed(() => {
  const obj = activeObject.value;
  if (!obj) return "";
  const dateStr = obj.startedAt || obj.scheduledAt || obj.createdAt;
  const date = new Date(dateStr);
  return date.toLocaleDateString("uk-UA", {
    day: "numeric",
    month: "long",
  });
});

const close = () => {
  if (videoPlayer.value) {
    videoPlayer.value.pause();
  }
  emit("close");
};

const selectVideo = (index) => {
  if (currentVideoIndex.value === index) return;
  currentVideoIndex.value = index;
  loading.value = true;
};

const onVideoEnded = () => {
  if (
    isPlaylist.value &&
    currentVideoIndex.value < playlistItems.value.length - 1
  ) {
    selectVideo(currentVideoIndex.value + 1);
  }
};

const getVideoThumbnail = (item) => {
  if (!item) return "";
  const video = item.video || item;
  return video?.thumbnails?.thumbnail || video?.thumbnail || "";
};

const getVideoTitle = (item) => {
  if (!item) return "";
  const video = item.video || item;
  return video?.title || "";
};

const handleKeydown = (e) => {
  if (e.key === "Escape") close();
  if (
    e.key === " " &&
    e.target.tagName !== "BUTTON" &&
    e.target.tagName !== "INPUT"
  ) {
    e.preventDefault();
    if (videoPlayer.value) {
      videoPlayer.value.paused
        ? videoPlayer.value.play()
        : videoPlayer.value.pause();
    }
  }
};

watch(currentVideoUrl, () => {
  setTimeout(() => {
    loading.value = false;
  }, 300);
});

onMounted(() => {
  document.addEventListener("keydown", handleKeydown);
  document.body.style.overflow = "hidden";
});

onUnmounted(() => {
  document.removeEventListener("keydown", handleKeydown);
  document.body.style.overflow = "";
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(128, 128, 128, 0.1);
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(128, 128, 128, 0.2);
}
.md\:w-85 {
  width: 21.25rem;
}
</style>
