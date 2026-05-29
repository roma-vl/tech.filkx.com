<template>
  <div
    class="group flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200"
  >
    <div
      class="relative w-32 aspect-video rounded-lg overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
    >
      <img
        v-if="video.thumbnails?.thumbnail"
        :src="video.thumbnails.thumbnail"
        class="w-full h-full object-cover"
        alt=""
      >
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-gray-400"
      >
        <VideoIcon class="w-8 h-8 opacity-20" />
      </div>

      <div
        v-if="video.status !== 'uploading' && video.status !== 'failed'"
        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
        @click="$emit('play', video.id)"
      >
        <div
          class="w-10 h-10 rounded-full bg-primary/90 text-white flex items-center justify-center shadow-lg transform scale-90 group-hover:scale-100 transition-transform"
        >
          <PlayIcon class="w-5 h-5 ml-0.5" />
        </div>
      </div>
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-3 mb-1">
        <div
          class="p-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 group-hover:border-primary/20 group-hover:bg-primary/5 transition-all"
          :class="video.isOptimized ? 'text-primary' : 'text-gray-400'"
        >
          <ZapIcon v-if="video.isOptimized" class="w-4 h-4" />
          <VideoIcon v-else class="w-4 h-4" />
        </div>
        <h4
          class="font-bold text-gray-900 dark:text-white truncate text-base group-hover:text-primary transition-colors"
        >
          {{ video.title }}
        </h4>

        <!-- Ready badge -->
        <div
          v-if="video.status === 'ready' && !video.isOriginalLocked"
          class="flex-shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50"
        >
          {{ t("media.videos.status.ready") }}
        </div>

        <!-- Locked original badge -->
        <div
          v-if="video.isOriginalLocked"
          class="flex-shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800/50 flex items-center gap-1.5"
        >
          <div class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse" />
          {{ t("media.videos.status.streamingRestricted") }}
        </div>

      </div>

      <!-- Delete original recommendation -->
      <div
        v-if="video.isOriginalLocked && video.meta?.optimizedVideoId"
        class="flex items-center gap-2 mb-2 px-3 py-1.5 rounded-lg bg-primary/5 border border-primary/10 w-fit animate-in slide-in-from-left-1 duration-300"
      >
        <InfoIcon class="w-3 h-3 text-primary" />
        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">
          {{ t("media.videos.recommendation.deleteOriginal") }}
        </span>
      </div>

      <div
        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 font-medium"
      >
        <div class="flex items-center gap-1">
          <ClockIcon class="w-3.5 h-3.5 opacity-60" />
          {{ formatDate(video.createdAt) }}
        </div>
        <div class="flex items-center gap-1">
          <DatabaseIcon class="w-3.5 h-3.5 opacity-60" />
          {{ formatSize(video.videoInfo?.size || video.size) }}
        </div>
        <div
          v-if="video.durationFormatted"
          class="flex items-center gap-1"
        >
          <PlayIcon class="w-3.5 h-3.5 opacity-60" />
          {{ video.durationFormatted }}
        </div>
        <div
          v-if="video.isOptimized"
          class="flex items-center gap-1 text-primary-600 dark:text-primary-400"
        >
          <ZapIcon class="w-3.5 h-3.5" />
          {{ t("media.videos.status.optimized") }}
        </div>
        <div
          v-if="video.resolutionLabel"
          class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest"
          :class="resolutionBadgeClass"
          :title="!isSupported ? t('streams.errors.resolutionNotSupported') : ''"
        >
          <MonitorIcon class="w-3 h-3" />
          {{ video.resolutionLabel }}
          <span v-if="!isSupported" class="ml-1 opacity-70">({{ t("common.notSupported") }})</span>
        </div>
      </div>
    </div>

    <!-- Right status panel -->
    <div class="flex flex-col items-start sm:items-end sm:mr-8 sm:w-48 font-mono mt-2 sm:mt-0">
      <div v-if="video.status === 'processing' || video.status === 'queued_for_transcoding'">
        <div
          class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-2 border border-gray-200 dark:border-gray-600"
        >
          <div
            class="h-full bg-primary transition-all duration-500"
            :style="{ width: (video.transcodingProgress || 0) + '%' }"
            :class="{ 'animate-pulse': video.status === 'queued_for_transcoding' }"
          />
        </div>
        <span
          class="text-[10px] uppercase font-bold tracking-widest"
          :class="video.status === 'processing' ? 'text-primary' : 'text-gray-400'"
        >
          {{
            video.status === "processing"
              ? `${t("media.videos.status.processing")} ${video.transcodingProgress}%`
              : t("media.videos.status.queued")
          }}
        </span>
      </div>

      <div v-else-if="video.status === 'failed'">
        <span class="text-red-500 text-[10px] font-bold uppercase tracking-widest flex items-center gap-1">
          <AlertCircleIcon class="w-3.5 h-3.5" />
          {{ t("media.videos.status.failed") }}
        </span>
      </div>

      <div v-else-if="video.status === 'ready'">
        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest flex items-center gap-1">
          <CheckCircleIcon class="w-3.5 h-3.5 text-green-500" />
          {{ t("media.videos.status.completed") }}
        </div>
      </div>
    </div>

    <div
      class="absolute top-4 right-4 sm:relative sm:top-0 sm:right-0 flex items-center justify-center w-10 sm:w-12 sm:mr-2"
    >
      <Dropdown align="right" width="48">
        <template #trigger>
          <button
            class="flex items-center justify-center w-10 h-10 rounded-xl text-gray-400 hover:text-primary hover:bg-primary/5 transition-all outline-none border border-transparent hover:border-primary/10"
          >
            <MoreVerticalIcon class="w-5 h-5" />
          </button>
        </template>
        <template #content>
          <div class="p-1.5 space-y-1">
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
              <ZapIcon class="w-4 h-4" />
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
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import Dropdown from "@/components/ui/Dropdown.vue";
import { useSubscriptionStore } from "@/stores/subscription";
import {
  AlertCircleIcon,
  CheckCircleIcon,
  ClockIcon,
  DatabaseIcon,
  DownloadIcon,
  Edit3Icon as EditIcon,
  InfoIcon,
  MonitorIcon,
  MoreVerticalIcon,
  PlayIcon,
  SearchIcon,
  Trash2Icon as TrashIcon,
  VideoIcon,
  ZapIcon,
} from "lucide-vue-next";
import { computed } from "vue";

const { t } = useI18n();

const props = defineProps({
  video: {
    type: Object,
    required: true,
  },
});

defineEmits(["play", "edit", "delete", "check", "optimize", "download"]);

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

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
    return "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50";
  }
  const label = props.video.resolutionLabel;
  if (label === "4K") return "bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50";
  if (label === "1080p") return "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50";
  if (label === "720p") return "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800/50";
  return "bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400 border border-gray-200 dark:border-gray-800/50";
});
</script>
