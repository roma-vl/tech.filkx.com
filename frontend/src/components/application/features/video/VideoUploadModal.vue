<template>
  <AppModal
    :model-value="true"
    :title="t('media.uploadModal.title')"
    :show-close="!isUploading"
    max-width="2xl"
    :close-on-backdrop="!isUploading"
    @update:model-value="handleClose"
  >
    <div class="space-y-6 sm:p-2">
      <AppAlert
        v-if="!canUploadSelectedVideo"
        variant="warning"
        class="shadow-sm"
      >
        <span v-html="t('media.uploadModal.limitWarning')" />
      </AppAlert>

      <!-- Step 1: Selection -->
      <div
        v-if="currentStep === 'selection'"
        id="tour-video-dropzone"
        class="animate-in fade-in zoom-in-95 duration-300"
      >
        <AppFileUpload
          :disabled="isUploading"
          accept="video/*"
          wrapper-class="!border-0 !bg-transparent !p-0"
          @change="handleFileSelect"
        >
          <template #content="{ trigger }">
            <div
              class="relative overflow-hidden rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/20 p-10 sm:p-14 text-center transition-all hover:border-primary-500 hover:bg-primary-50/50 dark:hover:border-primary-500 dark:hover:bg-primary-900/10 group cursor-pointer"
              @click.stop="trigger"
            >
              <!-- Decorative background glow -->
              <div
                class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-primary-500/10 blur-3xl group-hover:bg-primary-500/20 transition-colors"
              />
              <div
                class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl group-hover:bg-blue-500/20 transition-colors"
              />

              <div
                class="relative z-10 mx-auto mb-6 flex h-32 w-32 items-center justify-center rounded-full bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 transition-transform group-hover:scale-105 group-hover:shadow-md"
              >
                <div
                  ref="container"
                  class="w-[120px] h-[120px]"
                />
              </div>

              <h3
                class="mt-4 text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100 relative z-10"
              >
                {{ t("media.uploadModal.dragDropTitle") }}
              </h3>
              <p
                class="mt-2 text-sm text-gray-500 dark:text-gray-400 relative z-10 font-medium"
              >
                {{ t("media.uploadModal.dragDropSubtitle") }}
              </p>

              <div class="mt-8 flex justify-center relative z-10">
                <AppButton
                  variant="primary"
                  :disabled="isUploading"
                  class="shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all w-full sm:w-auto px-8"
                  @click.stop="trigger"
                >
                  <UploadCloudIcon class="w-5 h-5 mr-2" />
                  {{ t("media.uploadModal.selectFileButton") }}
                </AppButton>
              </div>
            </div>
          </template>
          <template #action>
            <!-- Slot is overridden inside content -->
          </template>
        </AppFileUpload>

        <p
          class="text-sm font-medium text-gray-400 dark:text-gray-500 mt-6 text-center uppercase tracking-wider"
        >
          {{ t("media.uploadModal.supportedFormats") }}
        </p>
      </div>

      <!-- Step 2: Progress -->
      <div
        v-if="currentStep === 'progress'"
        class="animate-in fade-in slide-in-from-bottom-4 duration-500 space-y-6"
      >
        <!-- File Info Card -->
        <div
          class="flex items-center gap-4 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/50 dark:bg-gray-800/50 p-4 shadow-sm backdrop-blur-sm"
        >
          <div
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 border border-primary-200/50 dark:border-primary-800/50"
          >
            <FilmIcon class="h-7 w-7" />
          </div>
          <div class="flex-1 min-w-0">
            <p
              class="truncate text-base font-semibold text-gray-900 dark:text-gray-100"
            >
              {{ fileName }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ fileSize }}
            </p>
          </div>
          <button
            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
            title="Cancel"
            @click="handleCancel"
          >
            <XIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Progress UI -->
        <div
          class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/20 p-6 shadow-inner space-y-5"
        >
          <div class="flex justify-between items-end">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              <span
                class="inline-block w-2 h-2 mr-2 rounded-full bg-primary-500 animate-pulse"
              />
              {{ statusMessage }}
            </span>
            <span
              class="text-2xl font-bold tracking-tight text-primary-600 dark:text-primary-400"
            >{{ progress }}%</span>
          </div>

          <AppProgressBar
            :value="progress"
            class="h-3"
          />

          <!-- Stats Grid -->
          <div
            class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-200 dark:border-gray-700/50"
          >
            <div class="flex flex-col">
              <span
                class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-semibold mb-1"
              >Upload Speed</span>
              <span
                class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-1.5"
              >
                <ActivityIcon class="w-4 h-4 text-green-500" />
                {{ uploadSpeed }} MB/s
              </span>
            </div>
            <div class="flex flex-col items-end">
              <span
                class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-semibold mb-1"
              >Estimated Time</span>
              <span
                class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-1.5"
              >
                <ClockIcon class="w-4 h-4 text-blue-500" />
                {{ timeLeft }}
              </span>
            </div>
          </div>
        </div>

        <!-- Readiness Analysis -->
        <div
          v-if="
            (clientMetadata || videoData?.meta?.readiness) && !hasReadinessError
          "
          class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/50 dark:bg-gray-800/50 p-5 shadow-sm backdrop-blur-sm"
        >
          <div class="flex items-center justify-between mb-4">
            <h5
              class="text-[11px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400"
            >
              Stream Readiness Analysis
            </h5>
            <div
              v-if="!videoData?.meta?.readiness"
              class="flex items-center gap-2 text-primary-600 dark:text-primary-400 text-xs font-semibold"
            >
              <Loader2Icon class="w-3.5 h-3.5 animate-spin" />
              <span>Analyzing...</span>
            </div>
            <div
              v-else
              class="flex items-center gap-1.5 text-green-600 dark:text-green-500 text-xs font-semibold"
            >
              <CheckCircle2Icon class="w-3.5 h-3.5" />
              <span>Complete</span>
            </div>
          </div>
          <StreamReadinessBlocks :readiness="combinedReadiness" />
        </div>
      </div>

      <!-- Step 3: Complete -->
      <div
        v-if="currentStep === 'complete'"
        class="animate-in zoom-in-95 duration-500 flex flex-col items-center justify-center py-10 sm:py-14 text-center space-y-8"
      >
        <div class="relative">
          <div
            class="absolute inset-0 block animate-ping rounded-full bg-green-400/20"
          />
          <div
            class="absolute inset-0 block animate-pulse rounded-full bg-green-400/20 blur-xl"
          />
          <div
            class="relative flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900/60 dark:to-green-900/20 text-green-600 dark:text-green-400 shadow-xl shadow-green-500/20 border-4 border-white dark:border-gray-800"
          >
            <CheckCircle2Icon class="h-14 w-14" />
          </div>
        </div>

        <div class="space-y-3">
          <h4
            class="text-3xl font-bold bg-gradient-to-br from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent"
          >
            {{ t("media.uploadModal.uploadComplete.successTitle") }}
          </h4>
          <p
            class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto text-base"
          >
            {{ t("media.uploadModal.uploadComplete.successMessage") }}
          </p>
        </div>

        <div
          v-if="combinedReadiness.resolution && !hasReadinessError"
          class="w-full max-w-md mx-auto text-left rounded-2xl border border-green-100 dark:border-green-900/30 bg-green-50/50 dark:bg-green-900/10 p-4"
        >
          <StreamReadinessBlocks :readiness="combinedReadiness" />
        </div>

        <div
          class="flex flex-col sm:flex-row justify-center gap-4 w-full max-w-sm pt-4"
        >
          <AppButton
            variant="secondary"
            class="w-full sm:w-auto hover:bg-gray-100 dark:hover:bg-gray-800"
            @click="resetUpload"
          >
            {{ t("media.uploadModal.uploadComplete.uploadMoreButton") }}
          </AppButton>
          <AppButton
            variant="primary"
            class="w-full sm:w-auto shadow-lg shadow-primary-500/25"
            @click="finishUpload"
          >
            {{ t("media.uploadModal.uploadComplete.doneButton") }}
          </AppButton>
        </div>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import { ref, onUnmounted, onMounted, nextTick, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useVideoUpload } from "@/composables/useVideoUpload";
import { useSubscriptionStore } from "@/stores/subscription";
import lottie from "lottie-web";
import DragDrop from "@/assets/animation/DragDrop.json";

import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import AppFileUpload from "@/components/application/ui/Form/AppFileUpload.vue";
import AppProgressBar from "@/components/application/ui/Feedback/AppProgressBar.vue";
import AppAlert from "@/components/application/ui/Feedback/AppAlert.vue";
import StreamReadinessBlocks from "@/components/application/features/video/StreamReadinessBlocks.vue";
import {
  Loader2Icon,
  UploadCloudIcon,
  FilmIcon,
  XIcon,
  ActivityIcon,
  ClockIcon,
  CheckCircle2Icon,
} from "lucide-vue-next";

const { t } = useI18n();
const emit = defineEmits(["close", "upload-complete"]);
const sub = useSubscriptionStore();

const container = ref(null);
const videoData = ref(null);
const clientMetadata = ref(null);

const combinedReadiness = computed(() => {
  if (videoData.value?.meta?.readiness) {
    return videoData.value.meta.readiness;
  }

  if (clientMetadata.value) {
    const w = clientMetadata.value.width;
    const h = clientMetadata.value.height;
    const is1080p = (w >= 1920 && h >= 1080) || (h >= 1920 && w >= 1080);
    const is720p = (w >= 1280 && h >= 720) || (h >= 1280 && w >= 720);

    const bitMbps = clientMetadata.value.bitrate
      ? (clientMetadata.value.bitrate / 1000000).toFixed(1)
      : 0;

    return {
      resolution: {
        status: is1080p ? "green" : is720p ? "yellow" : "red",
        value: `${w}×${h}`,
        message: is1080p ? "Recommended" : "Adequate",
      },
      frameRate: {
        status: "yellow",
        value: "~30 FPS",
        message: "Detected",
      },
      codec: {
        status: "green",
        value: clientMetadata.value.codec || "H.264",
        message: "Compatible",
      },
      bitrate: {
        status: bitMbps > 5 ? "green" : bitMbps > 2 ? "yellow" : "red",
        value: `~${bitMbps} Mbps`,
        message: bitMbps > 5 ? "Excellent" : "Low",
      },
    };
  }

  return {};
});

onMounted(() => {
  if (container.value) {
    lottie.loadAnimation({
      container: container.value,
      renderer: "svg",
      loop: true,
      autoplay: true,
      animationData: DragDrop,
    });
  }
});
const {
  uploadId,
  totalChunks,
  uploadedChunks,
  progress,
  isUploading,
  uploadSpeed,
  timeLeft,
  statusMessage,
  initChunkUpload,
  processFileInChunks,
  cancelUpload,
  reset,
} = useVideoUpload();

const hasReadinessError = computed(() => {
  const r = combinedReadiness.value;
  return (
    r.resolution?.value === "N/A" || r.resolution?.message?.includes("FFProbe")
  );
});

const currentStep = ref("selection");
const file = ref(null);
const fileName = ref("");
const fileSize = ref("");

const formatFileSize = (bytes) => {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const handleFileSelect = async (input) => {
  let selectedFile = input;
  if (input instanceof Event) {
    selectedFile = input.target.files[0];
  }

  if (!selectedFile) return;

  if (!selectedFile.type.startsWith("video/")) {
    alert(t("media.uploadModal.alerts.selectVideoFile"));
    return;
  }

  await extractMetadata(selectedFile);
  await startUpload(selectedFile);
};

const extractMetadata = (file) => {
  return new Promise((resolve) => {
    const video = document.createElement("video");
    video.preload = "metadata";
    video.onloadedmetadata = () => {
      window.URL.revokeObjectURL(video.src);
      const duration = video.duration || 0;
      const bitrate = duration > 0 ? Math.round((file.size * 8) / duration) : 0;
      clientMetadata.value = {
        width: video.videoWidth,
        height: video.videoHeight,
        duration: duration,
        bitrate: bitrate,
        codec: file.type.split("/")[1]?.toUpperCase() || "MP4",
      };
      resolve();
    };
    video.onerror = () => {
      resolve();
    };
    video.src = URL.createObjectURL(file);
  });
};

const canUploadSelectedVideo = ref(true);

const canUploadVideo = (fileSizeBytes) => {
  const storageLimit = sub.storageLimitBytes ?? 0;
  const storageUsed = sub.storageUsedBytes ?? 0;
  const videoLimit = sub.subscription?.features?.maxVideos ?? 0;
  const videosUploaded = sub.subscription?.usage?.videosUploaded ?? 0;

  const fitsStorage = storageUsed + fileSizeBytes <= storageLimit;
  const fitsVideos = videosUploaded + 1 <= videoLimit;

  return fitsStorage && fitsVideos;
};

const startUpload = async (selectedFile) => {
  canUploadSelectedVideo.value = canUploadVideo(selectedFile.size);

  if (!canUploadSelectedVideo.value) {
    currentStep.value = "selection";
    return;
  }

  file.value = selectedFile;
  fileName.value = selectedFile.name;
  fileSize.value = formatFileSize(selectedFile.size);
  currentStep.value = "progress";

  try {
    isUploading.value = true;
    await initChunkUpload(selectedFile);
    const result = await processFileInChunks(selectedFile);
    videoData.value = result;
    isUploading.value = false;
    currentStep.value = "complete";
  } catch (err) {
    console.error("Upload failed", err);
    isUploading.value = false;
    currentStep.value = "selection";
    resetForm();
  }
};

const handleCancel = async () => {
  if (!confirm(t("media.uploadModal.alerts.confirmCancel"))) return;
  await cancelUpload(file.value);
  currentStep.value = "selection";
  resetForm();
};

const resetForm = () => {
  file.value = null;
  fileName.value = "";
  fileSize.value = "";
};

const resetUpload = () => {
  currentStep.value = "selection";
  reset();
  resetForm();
};

watch(currentStep, async (val) => {
  if (val === "selection") {
    await nextTick();
    if (container.value) {
      lottie.loadAnimation({
        container: container.value,
        renderer: "svg",
        loop: true,
        autoplay: true,
        animationData: DragDrop,
      });
    }
  }
});

const finishUpload = () => {
  emit("upload-complete");
  emit("close");
};

const handleClose = (val) => {
  if (isUploading.value) {
    if (confirm(t("media.uploadModal.alerts.confirmClose"))) {
      handleCancel();
      emit("close");
    }
  } else {
    emit("close");
  }
};

onUnmounted(() => {
  if (isUploading.value) cancelUpload(file.value);
});
</script>
