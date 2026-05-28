<template>
  <AppModal
    :model-value="true"
    :title="t('media.readiness.messages.complete')"
    max-width="2xl"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-6">
      <div
        class="flex items-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-800"
      >
        <div
          class="relative w-32 aspect-video rounded-xl overflow-hidden shadow-sm flex-shrink-0 bg-gray-200 dark:bg-gray-800"
        >
          <img
            v-if="video.thumbnails?.thumbnail"
            :src="video.thumbnails.thumbnail"
            class="w-full h-full object-cover"
          >
          <div
            v-else
            class="w-full h-full flex items-center justify-center text-gray-400"
          >
            <VideoIcon class="w-8 h-8 opacity-20" />
          </div>
        </div>
        <div class="min-w-0 flex-1">
          <h4 class="font-black text-gray-900 dark:text-white truncate">
            {{ video.title }}
          </h4>
          <p
            class="text-[10px] font-black uppercase tracking-widest text-gray-400 mt-1"
          >
            {{ formatSize(video.videoInfo?.size || video.size) }} •
            {{ video.durationFormatted || "00:00" }} •
            {{ video.status }}
          </p>
        </div>
      </div>

      <!-- Restricted Notice -->
      <div
        v-if="video.isOriginalLocked"
        class="flex items-center gap-3 p-4 rounded-2xl bg-orange-50 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-800/30 text-orange-700 dark:text-orange-400"
      >
        <div class="p-2 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex-shrink-0">
          <AlertCircleIcon class="w-5 h-5 animate-pulse" />
        </div>
        <div class="min-w-0">
          <p class="text-sm font-black uppercase tracking-wider leading-tight">
            {{ t("media.videos.status.lockedForStreaming") }}
          </p>
          <p class="text-[11px] font-medium opacity-80 mt-1 leading-normal">
            {{ t("media.videos.status.originalWarning") }}
          </p>
        </div>
      </div>

      <div
        v-if="loading"
        class="py-12 flex flex-col items-center justify-center space-y-4"
      >
        <Loader2Icon class="w-12 h-12 text-primary animate-spin opacity-50" />
        <p class="text-sm font-medium text-gray-500 animate-pulse">
          {{ t("media.readiness.messages.analyzing") }}
        </p>
      </div>

      <StreamReadinessBlocks
        v-else-if="localReadiness"
        :readiness="localReadiness"
      />

      <div
        v-else
        class="py-12 text-center bg-gray-50 dark:bg-gray-900/10 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800"
      >
        <AlertCircleIcon
          class="w-12 h-12 text-gray-300 dark:text-gray-700 mx-auto mb-3"
        />
        <p class="text-gray-500 dark:text-gray-400 font-medium">
          {{ t("media.readiness.messages.noData") }}
        </p>
        <AppButton
          variant="secondary"
          size="sm"
          class="mt-4"
          @click="fetchReadiness(true)"
        >
          {{ t("common.retry") }}
        </AppButton>
      </div>

      <div
        class="flex flex-col sm:flex-row justify-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-800"
      >
        <AppButton
          variant="secondary"
          class="order-2 sm:order-1"
          @click="$emit('close')"
        >
          {{ t("common.close") }}
        </AppButton>
        <AppButton
          variant="primary"
          @click="$emit('optimize', video.id)"
        >
          <template #prefix>
            <ZapIcon class="w-4 h-4 mr-2" />
          </template>
          {{ t("media.videoCard.optimize") }}
        </AppButton>
        <AppButton
          variant="secondary"
          @click="$emit('download', video.id)"
        >
          <template #prefix>
            <DownloadIcon class="w-4 h-4 mr-2" />
          </template>
          {{ t("common.download") }}
        </AppButton>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import {onMounted, ref} from "vue";
import {useI18n} from "vue-i18n";
import api from "@/services/api.js";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import StreamReadinessBlocks from "@/components/application/features/video/StreamReadinessBlocks.vue";
import {AlertCircleIcon, DownloadIcon, Loader2Icon, VideoIcon, ZapIcon,} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps({
  video: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "optimize", "download"]);

const loading = ref(false);
const localReadiness = ref(props.video.meta?.readiness || null);

const fetchReadiness = async (force = false) => {
  if (force) localReadiness.value = null;
  loading.value = true;
  try {
    const { data } = await api.get(
      `/videos/${props.video.id}/readiness${force ? "?force=1" : ""}`,
    );
    localReadiness.value = data.data;
  } catch (err) {
    console.error("Failed to fetch readiness info:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (!localReadiness.value) {
    fetchReadiness();
  }
});

const formatSize = (bytes) => {
  if (!bytes) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
};
</script>
