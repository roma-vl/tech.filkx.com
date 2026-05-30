<template>
  <AppModal
    :model-value="true"
    :title="t('streams.editModal.title') + ' - ' + formData.name"
    max-width="3xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
      <div class="space-y-8">
        <div
          v-if="formData.platform"
          class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800"
        >
          <div class="p-2 bg-white dark:bg-gray-900 rounded-xl shadow-sm">
            <img
              :src="getPlatformIcon(formData.platform)"
              :alt="formData.platform"
              class="w-6 h-6 object-contain"
            >
          </div>
          <div>
            <h4
              class="font-black text-gray-900 dark:text-white text-sm uppercase tracking-tight"
            >
              {{ getPlatformName(formData.platform) }}
            </h4>
            <p
              class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"
            >
              {{ t("streams.editModal.platformLabel") }}
            </p>
          </div>
        </div>

        <div class="space-y-6">
          <div class="group-label">
            {{ t("streams.editModal.sectionDetails") }}
          </div>

          <AppInput
            v-model="formData.name"
            :label="t('streams.editModal.nameLabel')"
            :placeholder="t('streams.editModal.namePlaceholder')"
            class="!text-base"
          />

          <AppTextarea
            v-model="formData.comment"
            :label="t('streams.editModal.commentLabel')"
            :placeholder="t('streams.editModal.commentPlaceholder')"
            rows="3"
            class="!text-base transition-all focus:ring-primary-500/20"
          />
        </div>

        <div class="space-y-6">
          <div class="group-label">
            {{ t("streams.editModal.sectionContent") }}
          </div>

          <AppSelect
            v-model="formData.selectionType"
            :label="t('streams.editModal.videoLabel')"
            :options="selectionTypeOptions"
            :placeholder="t('streams.editModal.notSelected')"
            option-value="id"
            option-label="name"
          />

          <div class="space-y-4 pt-2">
            <AppSelect
              v-if="formData.selectionType === 'video'"
              v-model="formData.video"
              :options="selectableVideos"
              option-value="id"
              option-label="title"
            >
              <template #option="{ option }">
                <div class="flex items-center justify-between w-full">
                  <span :class="{ 'opacity-50 line-through': option.disabled }">
                    {{ option.title }}
                  </span>
                  <!-- Lock badge for preview-only originals -->
                  <div
                    v-if="option.isOriginalLocked"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/50 shrink-0"
                  >
                    <LockIcon class="w-2.5 h-2.5" />
                  </div>
                  <div
                    v-else-if="option.resolutionLabel"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                    :class="
                      getResolutionClass(
                        option.resolutionLabel,
                        option.disabled,
                      )
                    "
                  >
                    <MonitorIcon class="w-2.5 h-2.5" />
                    {{ option.resolutionLabel }}
                  </div>
                </div>
              </template>
            </AppSelect>

            <AppSelect
              v-if="formData.selectionType === 'playlist'"
              v-model="formData.playlist"
              :options="selectablePlaylists"
              option-value="id"
              option-label="name"
              :hint="t('streams.editModal.playlistHint')"
            >
              <template #option="{ option }">
                <div class="flex items-center justify-between w-full text-left">
                  <span :class="{ 'opacity-50 line-through': option.disabled }">
                    {{ option.name }}
                  </span>
                  <div
                    v-if="option.disabled"
                    class="text-[10px] text-red-500 font-bold uppercase tracking-widest pl-2"
                  >
                    {{ t("common.notSupported") }}
                  </div>
                </div>
              </template>
            </AppSelect>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-8">
        <div class="space-y-6">
          <div class="group-label">
            {{ t("media.playerModal.preview") }}
          </div>

          <div
            class="relative aspect-video bg-gray-100 dark:bg-gray-950 rounded-3xl overflow-hidden border border-gray-100 dark:border-white/5 shadow-inner group/preview"
          >
            <img
              v-if="previewThumbnail"
              :src="previewThumbnail"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover/preview:scale-105"
            >
            <div
              v-if="previewThumbnail"
              class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"
            />

            <div
              v-if="!previewThumbnail"
              class="flex flex-col items-center justify-center h-full text-gray-400 space-y-4"
            >
              <div
                class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-8 h-8 opacity-40"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <p class="text-xs font-black uppercase tracking-[0.2em]">
                {{ t("streams.editModal.notSelected") }}
              </p>
            </div>

            <div
              v-else
              class="absolute bottom-6 left-6 right-6"
            >
              <p
                class="text-white font-black text-lg line-clamp-1 mb-2 drop-shadow-lg leading-tight"
              >
                {{ previewTitle }}
              </p>
              <div class="flex items-center gap-2">
                <span
                  class="w-2.5 h-2.5 rounded-full bg-primary-500 animate-pulse shadow-[0_0_12px_rgba(var(--primary-rgb),0.8)]"
                />
                <span
                  class="text-[11px] text-white/90 font-black uppercase tracking-[0.2em]"
                >{{ t("streams.status.live") }}
                  {{ t("media.playerModal.preview") }}</span>
              </div>
            </div>
          </div>
        </div>

        <div
          class="mt-auto p-6 bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-white/5 space-y-4"
        >
          <div
            class="flex justify-between items-center text-xs font-bold uppercase tracking-widest text-gray-400"
          >
            <span>{{ t("media.playerModal.status") }}</span>
            <span class="text-primary-500">{{
              t("streams.status." + stream.status)
            }}</span>
          </div>
          <div
            class="flex justify-between items-center text-xs font-bold uppercase tracking-widest text-gray-400"
          >
            <span>{{ t("media.playerModal.platform") }}</span>
            <span class="text-gray-900 dark:text-white">{{
              getPlatformName(formData.platform)
            }}</span>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-4 w-full">
        <AppButton
          variant="secondary"
          class="flex-1 !rounded-2xl !py-4 font-black uppercase tracking-widest text-xs"
          @click="$emit('close')"
        >
          {{ t("streams.editModal.cancelButton") }}
        </AppButton>
        <AppButton
          variant="primary"
          class="flex-1 !rounded-2xl !py-4 font-black uppercase tracking-widest text-xs shadow-xl shadow-primary-500/20"
          :loading="loading"
          :disabled="loading"
          @click="updateStream"
        >
          {{ t("streams.editModal.saveButton") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api.js";
import { useSubscriptionStore } from "@/stores/subscription";
import { useYoutubeStore } from "@/stores/youtube";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppSelect from "@/components/application/ui/Form/AppSelect.vue";
import AppTextarea from "@/components/application/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { LockIcon, MonitorIcon, PlusIcon } from "lucide-vue-next";

const { t } = useI18n();
const props = defineProps({
  modelValue: Boolean,
  stream: {
    type: Object,
    default: null,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "save"]);
const sub = useSubscriptionStore();
const youtubeStore = useYoutubeStore();
const playlists = ref([]);
const videos = ref([]);

const allPlatforms = [
  { id: "youtube", name: "YouTube" },
  { id: "twitch", name: "Twitch" },
  { id: "facebook", name: "Facebook" },
  { id: "tiktok", name: "TikTok" },
  { id: "kick", name: "Kick" },
];

const getPlatformIcon = (platformId) => {
  if (!platformId) return "";
  return new URL(
    `/src/assets/images/platforms/${platformId}.png`,
    import.meta.url,
  ).href;
};

const getPlatformName = (platformId) => {
  if (platformId === "twitch") return "Twitch";
  return allPlatforms.find((p) => p.id === platformId)?.name || platformId;
};

const formData = ref({
  name: "",
  platform: "",
  streamKey: "",
  selectionType: "",
  video: null,
  playlist: null,
  comment: "",
});

const loading = ref(false);

const selectionTypeOptions = computed(() => {
  const opts = [{ id: "video", name: t("streams.editModal.videoOption") }];
  if (sub.subscription?.features?.hasPlaylists) {
    opts.push({ id: "playlist", name: t("streams.editModal.playlistOption") });
  }
  return opts;
});

const previewThumbnail = computed(() => {
  if (formData.value.selectionType === "video" && formData.value.video) {
    const video = videos.value.find((v) => v.id === formData.value.video);
    return video?.thumbnails?.thumbnail || video?.thumbnails?.large || "";
  }
  if (formData.value.selectionType === "playlist" && formData.value.playlist) {
    const playlist = playlists.value.find(
      (p) => p.id === formData.value.playlist,
    );
    const firstVideo = playlist?.videos?.[0];
    return (
      firstVideo?.thumbnails?.thumbnail ||
      firstVideo?.video?.thumbnails?.thumbnail ||
      ""
    );
  }
  return null;
});

const previewTitle = computed(() => {
  if (formData.value.selectionType === "video" && formData.value.video) {
    return videos.value.find((v) => v.id === formData.value.video)?.title || "";
  }
  if (formData.value.selectionType === "playlist" && formData.value.playlist) {
    return (
      playlists.value.find((p) => p.id === formData.value.playlist)?.name || ""
    );
  }
  return "";
});

watch(
  () => props.stream,
  (stream) => {
    if (!stream) return;

    formData.value = {
      name: stream.name ?? "",
      platform: stream.platform ?? "",
      streamKey: stream.streamKey ?? "",
      comment: stream.comment ?? "",
      selectionType: stream.selectionType ?? "",
      video: stream.videoId ?? null,
      playlist: stream.playlistId ?? null,
    };
  },
  { immediate: true },
);

const updateStream = async () => {
  if (!formData.value.name) {
    alert(t("streams.editModal.fillFieldsAlert"));
    return;
  }

  let payload = {
    name: formData.value.name,
    platform: formData.value.platform,
    streamKey: formData.value.streamKey,
    comment: formData.value.comment || null,
  };

  if (formData.value.selectionType === "video") {
    payload.videoId = formData.value.video;
  }

  if (formData.value.selectionType === "playlist") {
    payload.playlistId = formData.value.playlist;
  }

  try {
    loading.value = true;
    const { data } = await api.post(
      `/streams/${props.stream.id}/update`,
      payload,
    );

    emit("save", data.data);
    emit("close");
  } catch (e) {
    if (e.code === "ERR_NETWORK" || e.message?.includes("Network Error")) {
      console.warn(
        "Transient network interruption ignored (container restart)",
        e,
      );
      emit("save", { ...props.stream, ...payload }); // Optimistic update
      emit("close");
      return;
    }

    console.error("Не вдалося змінити стрім", e);
    alert(t("streams.editModal.updateError"));
  } finally {
    loading.value = false;
  }
};

const fetchPlaylists = async () => {
  const { data } = await api.get("/playlists");
  playlists.value = data.data.data;
};

const fetchVideos = async () => {
  try {
    const { data } = await api.get("/videos");
    videos.value = data.data.data;
  } catch (e) {
    console.error("Failed to fetch videos", e);
  }
};

onMounted(() => {
  fetchPlaylists();
  fetchVideos();
});

const planStreamQuality = computed(() => {
  return sub.subscription?.features?.stream_quality || "hd";
});

const isVideoSupported = (video) => {
  const label = video.resolutionLabel;
  const quality = planStreamQuality.value;

  if (quality === "4k") return true;
  if (quality === "hd") return label !== "4K";
  if (quality === "sd") return label === "SD" || label === "720p";
  return true;
};

const selectableVideos = computed(() => {
  return videos.value.map((v) => ({
    ...v,
    disabled:
      v.isOriginalLocked ||
      !isVideoSupported(v) ||
      !["ready", "uploaded"].includes(v.status),
  }));
});

const selectablePlaylists = computed(() => {
  return playlists.value.map((p) => {
    const incompatibleVideos = p.videos
      ? p.videos.filter((v) => !isVideoSupported(v))
      : [];
    return {
      ...p,
      disabled: incompatibleVideos.length > 0,
    };
  });
});

const getResolutionClass = (label, isDisabled) => {
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
watch(
  () => formData.value.selectionType,
  (type) => {
    if (type === "video") {
      formData.value.playlist = null;
    }
    if (type === "playlist") {
      formData.value.video = null;
    }
  },
);
</script>

<style scoped>
.group-label {
  @apply text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4;
}

:deep(.bg-primary\/5) {
  background-color: rgba(var(--primary-rgb), 0.05);
}

:deep(.dark .bg-primary\/10) {
  background-color: rgba(var(--primary-rgb), 0.1);
}
</style>
