<template>
  <AppModal
    :model-value="true"
    :title="t('converter.modal.title')"
    max-width="2xl"
    :show-close="!isConverting"
    :close-on-backdrop="!isConverting"
    @update:model-value="handleClose"
  >
    <div class="space-y-6 sm:p-2 animate-in fade-in duration-300">
      <!-- Informational Text -->
      <div
        class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-2xl border border-blue-100 dark:border-blue-800/50"
      >
        <p
          class="text-sm text-blue-800 dark:text-blue-300 leading-relaxed font-medium"
        >
          <i18n-t
            keypath="converter.modal.info"
            tag="span"
          >
            <template #prefix>
              <strong>"filkx_"</strong>
            </template>
          </i18n-t>
        </p>
      </div>

      <div
        class="space-y-5 bg-white/40 dark:bg-gray-800/40 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50"
      >
        <!-- Video Selection (if not passed as prop) -->
        <div
          v-if="!selectedVideo"
          class="space-y-2"
        >
          <label
            class="block text-sm font-bold text-gray-900 dark:text-gray-100"
          >
            {{ t("converter.modal.selectVideo") }}
          </label>
          <AppSelect
            v-model="manualVideoId"
            :options="formattedVideosForSelect"
            option-value="id"
            option-label="title"
            :placeholder="t('converter.modal.videoPlaceholder')"
          />
        </div>

        <div
          v-else
          class="space-y-4"
        >
          <label
            class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2"
          >
            {{ t("converter.modal.videoParams") }}
          </label>

          <div
            class="bg-gray-50 dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/60 overflow-hidden text-sm"
          >
            <!-- Header/Title -->
            <div
              class="p-3 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3"
            >
              <div
                class="p-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-lg"
              >
                <VideoIcon class="w-4 h-4 text-primary-500" />
              </div>
              <div class="flex-1 min-w-0">
                <div
                  class="font-medium text-gray-900 dark:text-white truncate"
                  :title="selectedVideo.title"
                >
                  {{ selectedVideo.title }}
                </div>
                <div
                  class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5"
                >
                  {{ t("converter.modal.newName") }}:
                  <span
                    class="font-medium text-primary-600 dark:text-primary-400"
                  >filkx_{{ selectedVideo.title }}</span>
                </div>
              </div>
            </div>

            <!-- Specs Grid -->
            <div
              class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-4 p-4 text-gray-600 dark:text-gray-300"
            >
              <div class="flex flex-col gap-1">
                <span
                  class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold"
                >{{ t("converter.modal.duration") }}</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ selectedVideo.durationFormatted || "00:00" }}
                </span>
              </div>

              <div class="flex flex-col gap-1">
                <span
                  class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold"
                >{{ t("converter.modal.resolution") }}</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ selectedVideo.resolutionLabel || "Unknown" }}
                </span>
              </div>

              <div class="flex flex-col gap-1">
                <span
                  class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold"
                >{{ t("converter.modal.format") }}</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  MP4 (H.264)
                </span>
              </div>

              <div class="flex flex-col gap-1">
                <span
                  class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold"
                >{{ t("converter.modal.currentSize") }}</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ formatBytes(selectedVideo.videoInfo?.size || 0) }}
                </span>
              </div>

              <div class="flex flex-col gap-1">
                <span
                  class="text-xs text-green-600/70 dark:text-green-500/70 uppercase tracking-wider font-semibold flex items-center gap-1"
                >
                  {{ t("converter.modal.newSize") }} <ZapIcon class="w-3 h-3" />
                </span>
                <span class="font-bold text-green-600 dark:text-green-400">
                  ~{{
                    formatBytes((selectedVideo.videoInfo?.size || 0) * 0.45)
                  }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Platform Selection -->
        <div class="space-y-2">
          <label
            class="block text-sm font-bold text-gray-900 dark:text-gray-100"
          >
            {{ t("converter.modal.targetPlatform") }}
          </label>
          <AppSelect
            v-model="platformId"
            :options="platforms"
            option-value="id"
            option-label="name"
          />
        </div>

        <!-- FPS Selection -->
        <div class="space-y-2">
          <label
            class="block text-sm font-bold text-gray-900 dark:text-gray-100"
          >
            {{ t("converter.modal.fps") }}
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label
              class="relative flex cursor-pointer rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all"
              :class="{
                'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-500':
                  fps === 30,
              }"
            >
              <input
                v-model="fps"
                type="radio"
                name="fps"
                value="30"
                class="sr-only"
              >
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <span
                    class="block text-sm font-bold text-gray-900 dark:text-white"
                  >{{ t("converter.modal.fps30") }}</span>
                  <span
                    class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-400"
                  >{{ t("converter.modal.fps30desc") }}</span>
                </span>
              </span>
              <CheckCircle2Icon
                v-if="fps === '30'"
                class="h-5 w-5 text-primary-600"
                aria-hidden="true"
              />
            </label>
            <label
              class="relative flex cursor-pointer rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all"
              :class="{
                'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-500':
                  fps === 60,
              }"
            >
              <input
                v-model="fps"
                type="radio"
                name="fps"
                value="60"
                class="sr-only"
              >
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <span
                    class="block text-sm font-bold text-gray-900 dark:text-white"
                  >{{ t("converter.modal.fps60") }}</span>
                  <span
                    class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-400"
                  >{{ t("converter.modal.fps60desc") }}</span>
                </span>
              </span>
              <CheckCircle2Icon
                v-if="fps === '60'"
                class="h-5 w-5 text-primary-600"
                aria-hidden="true"
              />
            </label>
          </div>
        </div>

        <!-- Options Checkboxes -->
        <div
          class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700"
        >
          <label class="flex items-start gap-3 cursor-pointer group">
            <div class="flex items-center h-5">
              <input
                v-model="deleteOriginal"
                type="checkbox"
                class="w-5 h-5 rounded text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800"
              >
            </div>
            <div class="flex flex-col">
              <span
                class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors"
              >
                {{ t("converter.modal.deleteOriginal") }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ t("converter.modal.deleteDesc") }}
              </span>
            </div>
          </label>

          <label class="flex items-start gap-3 cursor-pointer group">
            <div class="flex items-center h-5">
              <input
                v-model="agreedToTerms"
                type="checkbox"
                class="w-5 h-5 rounded text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800"
              >
            </div>
            <div class="flex flex-col">
              <span
                class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors"
              >
                {{ t("converter.modal.agreeTerms") }}
              </span>
              <span
                class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                v-html="t('converter.modal.agreeDesc')"
              />
            </div>
          </label>
        </div>
      </div>

      <!-- Footer / Cost Estimation -->
      <div
        class="bg-gray-50 dark:bg-gray-800/50 p-5 rounded-2xl flex flex-col sm:flex-row justify-between items-center gap-4 border border-gray-100 dark:border-gray-700"
      >
        <div class="flex items-center gap-4 w-full sm:w-auto">
          <div
            class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center shrink-0"
          >
            <CoinsIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
          </div>
          <div class="flex-1">
            <div class="flex items-baseline gap-2">
              <span class="text-sm font-bold text-gray-900 dark:text-white">
                {{ t("converter.modal.cost") }}:
                <span class="text-lg text-yellow-600 dark:text-yellow-400">{{ estimatedCost }} Fcoin</span>
              </span>
            </div>
            <div
              class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1.5 flex-wrap"
            >
              <span>{{ t("converter.modal.available") }}:
                <strong class="text-gray-700 dark:text-gray-300">150 Fcoin</strong></span>
              <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600" />
              <a
                href="#"
                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium hover:underline transition-all"
              >{{ t("converter.modal.buyMore") }}</a>
            </div>
          </div>
        </div>

        <AppButton
          variant="primary"
          size="lg"
          class="w-full sm:w-auto !rounded-xl shadow-lg shadow-primary-500/25 hover:-translate-y-0.5"
          :disabled="!isValid"
          :loading="isConverting"
          @click="startConversion"
        >
          <template
            v-if="!isConverting"
            #prefix
          >
            <ZapIcon class="w-5 h-5 mr-2" />
          </template>
          {{ t("converter.modal.startbtn") }}
        </AppButton>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppSelect from "@/components/application/ui/Form/AppSelect.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {
  ZapIcon,
  ChevronDownIcon,
  CheckCircle2Icon,
  VideoIcon,
  CoinsIcon,
} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps({
  video: {
    type: Object,
    default: null,
  },
  allVideos: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close"]);

const manualVideoId = ref("");
const fps = ref("30");
const deleteOriginal = ref(false);
const agreedToTerms = ref(false);
const isConverting = ref(false);

const platformId = ref("youtube");
const platforms = computed(() => [
  { id: "youtube", name: "YouTube", disabled: false },
  {
    id: "tiktok",
    name: `TikTok (${t("converter.modal.soon")})`,
    disabled: true,
  },
  {
    id: "twitch",
    name: `Twitch (${t("converter.modal.soon")})`,
    disabled: true,
  },
  {
    id: "facebook",
    name: `Facebook (${t("converter.modal.soon")})`,
    disabled: true,
  },
  { id: "kick", name: `Kick (${t("converter.modal.soon")})`, disabled: true },
]);

const availableVideos = computed(() => {
  return props.allVideos.filter((v) => v.type !== "Event");
});

const formattedVideosForSelect = computed(() => {
  return availableVideos.value.map((v) => ({
    ...v,
    disabled: v.isOptimized,
    title:
      v.title +
      (v.isOptimized ? ` (${t("converter.modal.alreadyOptimized")})` : ""),
  }));
});

const selectedVideo = computed(() => {
  if (props.video) return props.video;
  if (manualVideoId.value) {
    return availableVideos.value.find((v) => v.id === manualVideoId.value);
  }
  return null;
});

const estimatedCost = computed(() => {
  if (!selectedVideo.value) return 0;
  const seconds =
    Number(selectedVideo.value.duration) ||
    Number(selectedVideo.value.videoInfo?.duration) ||
    0;
  const minutes = Math.ceil(seconds / 60);
  return Math.max(1, minutes);
});

const isValid = computed(() => {
  return selectedVideo.value && agreedToTerms.value;
});

const handleClose = () => {
  if (isConverting.value) return;
  emit("close");
};

const startConversion = () => {
  isConverting.value = true;
  // Mock conversion process
  setTimeout(() => {
    isConverting.value = false;
    emit("close");
  }, 1500);
};

const formatBytes = (bytes, decimals = 1) => {
  if (!+bytes) return "0 B";
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ["B", "KB", "MB", "GB", "TB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};
</script>
