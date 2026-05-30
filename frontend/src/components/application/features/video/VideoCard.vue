<template>
  <div
    class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-primary/30 hover:shadow-2xl hover:shadow-primary/5 transition-all duration-300 flex flex-col h-full shadow-sm"
  >
    <div
      class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-900 rounded-t-2xl"
    >
      <img
        v-if="video.thumbnails?.thumbnail"
        :src="video.thumbnails.thumbnail"
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
        alt=""
      >
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700"
      >
        <VideoIcon class="w-12 h-12 opacity-50" />
      </div>

      <div
        class="absolute top-3 right-3 z-30 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <Dropdown
          align="right"
          width="60"
        >
          <template #trigger>
            <button
              class="p-2 rounded-xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-md shadow-lg border border-white/20 text-gray-600 dark:text-gray-300 hover:text-primary transition-all"
            >
              <MoreVerticalIcon class="w-5 h-5" />
            </button>
          </template>
          <template #content>
            <div class="p-1.5 space-y-1 w-60">
              <button
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-primary/5 hover:text-primary rounded-lg transition-all"
                @click="$emit('edit', video)"
              >
                <EditIcon class="w-4 h-4" />
                {{ t("media.videoCard.edit") }}
              </button>
              <button
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-primary/5 hover:text-primary rounded-lg transition-all"
                @click="$emit('check', video)"
              >
                <SearchIcon class="w-4 h-4" />
                {{ t("media.videoCard.check") }}
              </button>
              <button
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-primary hover:bg-primary/10 rounded-lg transition-all"
                @click="$emit('optimize', video)"
              >
                <ZapIcon class="w-4 h-4 text-primary" />
                {{ t("media.videoCard.optimizeDemo") }}
              </button>
              <button
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-primary/5 hover:text-primary rounded-lg transition-all"
                @click="$emit('download', video)"
              >
                <DownloadIcon class="w-4 h-4" />
                {{ t("media.videoCard.download") }}
              </button>
              <div class="h-px bg-gray-100 dark:bg-gray-600 my-1" />
              <button
                class="w-full flex items-center gap-3 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                @click="$emit('delete', video)"
              >
                <TrashIcon class="w-4 h-4" />
                {{ t("media.videoCard.delete") }}
              </button>
            </div>
          </template>
        </Dropdown>
      </div>

      <div
        v-if="
          video.status !== 'uploading' &&
            video.status !== 'failed' &&
            !video.isTranscoding
        "
        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
        @click="$emit('play', video)"
      >
        <div
          class="w-16 h-16 rounded-full bg-primary/90 text-white flex items-center justify-center shadow-2xl transform scale-90 group-hover:scale-100 transition-all duration-300"
        >
          <PlayIcon class="w-8 h-8 ml-1" />
        </div>
      </div>

      <!-- Transcoding overlay -->
      <div
        v-if="video.isTranscoding"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm flex flex-col items-center justify-center gap-3"
      >
        <Loader2Icon class="w-8 h-8 text-amber-400 animate-spin" />
        <div class="text-center px-4">
          <p class="text-white text-xs font-bold uppercase tracking-wider">
            {{
              video.status === "queued_for_transcoding"
                ? "В черзі на конвертацію"
                : "Конвертується..."
            }}
          </p>
          <p
            v-if="video.transcodingProgress > 0"
            class="text-amber-400 text-sm font-black mt-1"
          >
            {{ video.transcodingProgress }}%
          </p>
        </div>
        <!-- Progress bar -->
        <div class="w-3/4 h-1.5 bg-white/20 rounded-full overflow-hidden">
          <div
            class="h-full bg-amber-400 rounded-full transition-all duration-500"
            :class="{
              'animate-pulse': video.status === 'queued_for_transcoding',
            }"
            :style="{ width: (video.transcodingProgress || 5) + '%' }"
          />
        </div>
      </div>
      <div class="absolute top-3 left-3 flex flex-col gap-2">
        <div
          v-if="video.status === 'ready'"
          class="px-2 py-1 rounded-lg bg-white/90 dark:bg-gray-900/90 backdrop-blur-md shadow-sm border border-white/20 flex items-center gap-1.5"
        >
          <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse" />
          <span
            class="text-xs font-bold uppercase tracking-tighter text-gray-900 dark:text-white"
          >{{ t("media.videos.status.ready") }}</span>
        </div>
      </div>

      <div
        v-if="video.durationFormatted"
        class="absolute bottom-3 left-3 px-2 py-1 rounded-md bg-black/60 backdrop-blur-sm text-white text-xs font-bold"
      >
        {{ video.durationFormatted }}
      </div>

      <div
        class="absolute bottom-3 right-3 px-2 py-1 rounded-md bg-black/60 backdrop-blur-sm text-white text-xs font-bold"
      >
        {{ formatSize(video.videoInfo?.size || video.size) }}
      </div>
    </div>

    <div class="p-5 flex flex-col flex-1">
      <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex items-start gap-2 min-w-0 flex-1">
          <div
            class="mt-1 p-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-primary/10 transition-colors"
            :class="video.isOptimized ? 'text-primary' : 'text-gray-400'"
          >
            <ZapIcon
              v-if="video.isOptimized"
              class="w-3.5 h-3.5"
            />
            <VideoIcon
              v-else
              class="w-3.5 h-3.5"
            />
          </div>
          <h4
            class="font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight group-hover:text-primary transition-colors pr-2"
          >
            {{ video.title }}
          </h4>
        </div>
      </div>

      <!-- Recommendation Notice -->
      <div
        v-if="video.isOriginalLocked && video.meta?.optimizedVideoId"
        class="mb-3 px-3 py-2 rounded-xl bg-primary/5 border border-primary/10 flex items-start gap-2.5 animate-in slide-in-from-top-1 duration-300"
      >
        <div class="p-1 rounded-lg bg-primary/10 text-primary mt-0.5">
          <InfoIcon class="w-3.5 h-3.5" />
        </div>
        <p
          class="text-[11px] font-bold text-gray-600 dark:text-gray-400 leading-tight"
        >
          {{ t("media.videos.recommendation.deleteOriginal") }}
        </p>
      </div>

      <!-- Transcoding progress bar (below thumbnail) -->
      <div
        v-if="video.isTranscoding"
        class="mb-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40 px-3 py-2.5"
      >
        <div class="flex items-center gap-2 mb-1.5">
          <Loader2Icon
            class="w-3.5 h-3.5 text-amber-500 animate-spin flex-shrink-0"
          />
          <p
            class="text-xs font-bold text-amber-700 dark:text-amber-400 truncate"
          >
            {{
              video.status === "queued_for_transcoding"
                ? t("media.videos.status.queued") +
                  " — водяний знак додається в фоні"
                : t("media.videos.status.processing")
            }}
            <span
              v-if="
                video.status !== 'queued_for_transcoding' &&
                  video.transcodingProgress > 0
              "
              class="ml-1"
            >
              — {{ video.transcodingProgress }}%
            </span>
          </p>
        </div>
        <div
          class="w-full h-1 bg-amber-200/60 dark:bg-amber-800/60 rounded-full overflow-hidden"
        >
          <div
            class="h-full bg-amber-500 rounded-full transition-all duration-700"
            :class="{
              'animate-pulse': video.status === 'queued_for_transcoding',
            }"
            :style="{
              width: Math.max(video.transcodingProgress || 0, 5) + '%',
            }"
          />
        </div>
        <p
          class="text-[10px] text-amber-500 dark:text-amber-500 mt-1.5 leading-tight"
        >
          Ви отримаєте сповіщення коли відео буде готове до стримінгу
        </p>
      </div>
      <div
        class="mt-auto flex items-center justify-between border-t border-gray-50 dark:border-gray-700/50 pt-4"
      >
        <div
          class="flex items-center gap-1 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"
        >
          <CalendarIcon class="w-3.5 h-3.5" />
          {{ formatDate(video.createdAt) }}
        </div>
        <div class="flex items-center gap-1.5 overflow-hidden">
          <div
            v-if="video.resolutionLabel"
            class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 whitespace-nowrap"
          >
            {{ video.resolutionLabel }}
          </div>
          <div
            v-if="video.isOriginalLocked"
            class="p-1 rounded-md bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 shrink-0"
            :title="t('media.videos.status.streamingRestricted')"
          >
            <LockIcon class="w-3 h-3" />
          </div>
          <div
            v-if="video.isOptimized"
            class="p-1 rounded-md bg-primary/10 text-primary shrink-0"
            :title="t('media.videos.status.optimized') || 'Optimized'"
          >
            <ZapIcon class="w-3 h-3" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useSubscriptionStore } from "@/stores/subscription";
import Dropdown from "@/components/ui/Dropdown.vue";
import {
  CalendarIcon,
  DownloadIcon,
  Edit3Icon as EditIcon,
  InfoIcon,
  Loader2Icon,
  LockIcon,
  MonitorIcon,
  MoreVerticalIcon,
  PlayIcon,
  SearchIcon,
  Trash2Icon as TrashIcon,
  VideoIcon,
  ZapIcon,
} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps({
  video: {
    type: Object,
    required: true,
  },
});

defineEmits(["play", "edit", "delete", "check", "optimize", "download"]);

const formatSize = (bytes) => {
  if (!bytes) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
};

const subscriptionStore = useSubscriptionStore();

const planStreamQuality = computed(() => {
  return subscriptionStore.subscription?.features?.stream_quality || "hd";
});

const isSupported = computed(() => {
  const label = props.video.resolutionLabel;
  const quality = planStreamQuality.value;

  if (quality === "4k") return true;
  if (quality === "hd") return label !== "4K";
  if (quality === "sd") return label === "SD" || label === "720p";
  return true;
});

const resolutionBadgeClass = computed(() => {
  if (!isSupported.value) {
    return "bg-red-500 text-white border-red-600";
  }

  const label = props.video.resolutionLabel;
  if (label === "4K") {
    return "bg-purple-600 text-white border-purple-700";
  }
  if (label === "1080p") {
    return "bg-blue-600 text-white border-blue-700";
  }
  if (label === "720p") {
    return "bg-green-600 text-white border-green-700";
  }
  return "bg-gray-600 text-white border-gray-700";
});

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
  });
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
