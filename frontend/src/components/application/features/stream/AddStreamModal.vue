<template>
  <AppModal
    :model-value="true"
    :title="modalTitle"
    max-width="4xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-4 mb-4">
      <AppNotice
        v-if="!canCreateStream"
        variant="warning"
        :title="t('streams.addModal.limitWarningTitle')"
        :description="t('streams.addModal.limitWarning')"
        center
      />

      <AppNotice
        v-if="formData.platform === 'youtube' && formData.isAutomatic"
        variant="info"
        :title="t('streams.addModal.youtubeSettings.betaNoticeTitle')"
        :description="
          t('streams.addModal.youtubeSettings.betaNoticeDescription')
        "
      />
    </div>

    <PlatformSelector
      v-if="step === 1"
      id="tour-stream-platforms"
      v-model="formData.platform"
      :platforms="platformsList"
      @select="selectPlatform"
    />

    <div
      v-else
      class="space-y-6"
    >
      <div
        class="flex items-center gap-4 p-4 bg-primary/5 dark:bg-primary/10 rounded-lg border border-primary/10"
      >
        <img
          :src="getPlatformIcon(formData.platform)"
          :alt="formData.platform"
          class="w-8 h-8 object-contain"
        >
        <div>
          <h4 class="font-bold text-gray-900 dark:text-white">
            {{ getPlatformName(formData.platform) }}
          </h4>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ t("streams.addModal.steps.configureStream") }}
          </p>
        </div>
        <AppButton
          variant="secondary"
          size="sm"
          class="ml-auto !py-1"
          @click="goBack"
        >
          {{ t("streams.addModal.backButton") }}
        </AppButton>
      </div>

      <YoutubeMethodSelector
        v-if="formData.platform === 'youtube' && step === 1.5"
        @select="selectYoutubeMethod"
      />

      <div
        v-else-if="step === 2"
        id="tour-stream-config"
        class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in slide-in-from-right-4 duration-300"
      >
        <div class="space-y-6">
          <AppInput
            v-model="formData.name"
            :label="t('streams.addModal.nameLabel')"
            :placeholder="t('streams.addModal.namePlaceholder')"
            class="!text-base"
          />

          <YoutubeSettings
            v-if="formData.isAutomatic && formData.platform === 'youtube'"
            :form-data="formData"
            :youtube-categories="youtubeCategories"
          />

          <AppInput
            v-if="!formData.isAutomatic || formData.platform !== 'youtube'"
            v-model="formData.streamKey"
            :label="t('streams.addModal.streamKeyLabel')"
            :placeholder="t('streams.addModal.streamKeyPlaceholder')"
            :hint="t('streams.addModal.streamKeyHint')"
            class="!text-base"
          />

          <AppTextarea
            v-model="formData.comment"
            :label="t('streams.addModal.commentLabel')"
            :placeholder="t('streams.addModal.commentPlaceholder')"
            rows="3"
            class="!text-base"
          />
        </div>

        <div class="flex flex-col gap-6">
          <MediaSelection
            id="tour-video-select"
            :form-data="formData"
            :videos="selectableVideos"
            :playlists="selectablePlaylists"
            :selection-type-options="selectionTypeOptions"
          />

          <StreamPreview
            :is-automatic-you-tube="
              formData.platform === 'youtube' && formData.isAutomatic
            "
            :preview-thumbnail="previewThumbnail"
            :preview-title="previewTitle"
            @thumbnail-change="(file) => (formData.youtubeThumbnail = file)"
          />

          <ScheduleSettings
            :form-data="formData"
            :today-date="todayDate"
          />
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex space-x-3 w-full">
        <AppButton
          variant="secondary"
          class="flex-1"
          @click="$emit('close')"
        >
          {{ t("streams.addModal.cancelButton") }}
        </AppButton>
        <AppButton
          v-if="step === 2"
          variant="primary"
          class="flex-1"
          :disabled="!canCreateStream"
          :loading="saving"
          @click="
            canCreateStream
              ? createStream()
              : toast.warning(t('streams.addModal.limitToast'))
          "
        >
          {{
            saving
              ? t("streams.addModal.savingButton")
              : canCreateStream
                ? t("streams.addModal.createButton")
                : t("streams.addModal.cannotCreateButton")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api.js";
import { useToast } from "vue-toastification";
import { useSubscriptionStore } from "@/stores/subscription";
import { useVideoStore } from "@/stores/application/data/videoStore";
import { useYoutubeStore } from "@/stores/youtube";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppTextarea from "@/components/application/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import AppNotice from "@/components/application/ui/Banners/AppNotice.vue";

// Sub-components
import PlatformSelector from "./AddStream/PlatformSelector.vue";
import YoutubeMethodSelector from "./AddStream/YoutubeMethodSelector.vue";
import YoutubeSettings from "./AddStream/YoutubeSettings.vue";
import MediaSelection from "./AddStream/MediaSelection.vue";
import StreamPreview from "./AddStream/StreamPreview.vue";
import ScheduleSettings from "./AddStream/ScheduleSettings.vue";

const { t } = useI18n();

const props = defineProps({
  date: {
    type: [Date, String, null],
    default: null,
  },
});

const sub = useSubscriptionStore();
const videoStore = useVideoStore();
const youtubeStore = useYoutubeStore();
const toast = useToast();
const emit = defineEmits(["close", "create"]);
const playlists = ref([]);
const saving = ref(false);
const step = ref(1);

const allPlatforms = [
  { id: "youtube", name: "YouTube" },
  { id: "twitch", name: "Twitch" },
  { id: "local", name: "Local Server" },
  { id: "tiktok", name: "TikTok" },
  { id: "kick", name: "Kick", isDevelopment: true },
  { id: "facebook", name: "Facebook", isDevelopment: true },
];

const platformsList = computed(() => {
  const allowedSlugs = sub.subscription?.features?.platforms || [];
  return allPlatforms
    .map((p) => ({
      ...p,
      isAllowed: allowedSlugs.includes(p.id) || p.isDevelopment,
    }))
    .filter((p) => p.isAllowed);
});

const modalTitle = computed(() => {
  if (step.value === 1) return t("streams.addModal.steps.choosePlatform");
  if (step.value === 1.5)
    return t("streams.addModal.youtubeSettings.methodLabel");
  return t("streams.addModal.steps.configureStream");
});

const getPlatformIcon = (platformId) => {
  if (!platformId) return "";
  const fileName = `${platformId}.png`;
  return new URL(`/src/assets/images/platforms/${fileName}`, import.meta.url)
    .href;
};

const getPlatformName = (platformId) => {
  return allPlatforms.find((p) => p.id === platformId)?.name || platformId;
};

const selectPlatform = (platform) => {
  formData.value.platform = platform.id;
  if (platform.id === "youtube") {
    step.value = 1.5;
  } else {
    formData.value.isAutomatic = false;
    step.value = 2;
  }
};

const selectYoutubeMethod = (isAutomatic) => {
  formData.value.isAutomatic = isAutomatic;
  step.value = 2;
};

const goBack = () => {
  if (step.value === 2 && formData.value.platform === "youtube") {
    step.value = 1.5;
  } else {
    step.value = 1;
  }
};

const initialDate = computed(() => {
  if (!props.date) return "";
  const d = new Date(props.date);
  return d.toISOString().split("T")[0];
});

const formData = ref({
  name: "",
  platform: "",
  streamKey: "",
  playlist: "",
  video: "",
  comment: "",
  selectionType: "",
  scheduleType: props.date ? "scheduled" : "now",
  scheduleDate: initialDate.value,
  scheduleTime: "12:00",
  // YouTube Automatic fields
  isAutomatic: false,
  youtubeChannelId: null,
  privacyStatus: "public",
  isMadeForKids: false,
  youtubeTags: "",
  youtubeCategoryId: "22",
  youtubeThumbnail: null,
  youtubeDescription: "",
});

const todayDate = computed(() => new Date().toISOString().split("T")[0]);

const canCreateStream = computed(() => {
  const active = sub.subscription?.usage?.streamsActive ?? 0;
  const limit = sub.subscription?.features?.concurrentStreams ?? 0;
  return active < limit;
});

const selectionTypeOptions = computed(() => {
  const opts = [{ id: "video", name: t("streams.addModal.videoOption") }];
  if (sub.subscription?.features?.hasPlaylists) {
    opts.push({ id: "playlist", name: t("streams.addModal.playlistOption") });
  }
  return opts;
});

const previewThumbnail = computed(() => {
  if (
    formData.value.platform === "youtube" &&
    formData.value.isAutomatic &&
    formData.value.youtubeThumbnail
  ) {
    return URL.createObjectURL(formData.value.youtubeThumbnail);
  }

  if (formData.value.selectionType === "video" && formData.value.video) {
    const video = videoStore.videos.find((v) => v.id === formData.value.video);
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
    return (
      videoStore.videos.find((v) => v.id === formData.value.video)?.title || ""
    );
  }
  if (formData.value.selectionType === "playlist" && formData.value.playlist) {
    return (
      playlists.value.find((p) => p.id === formData.value.playlist)?.name || ""
    );
  }
  return "";
});

const createStream = async () => {
  if (
    !formData.value.name ||
    !formData.value.platform ||
    (!formData.value.isAutomatic && !formData.value.streamKey) ||
    (formData.value.isAutomatic && !formData.value.youtubeChannelId)
  ) {
    if (formData.value.isAutomatic && !formData.value.youtubeChannelId) {
      toast.error(t("streams.addModal.youtubeSettings.selectChannelError"));
    } else {
      alert(t("streams.addModal.fillFieldsAlert"));
    }
    return;
  }

  let scheduledAt = null;
  if (formData.value.scheduleType === "scheduled") {
    if (!formData.value.scheduleDate || !formData.value.scheduleTime) {
      toast.error(t("streams.addModal.scheduledAt.validation.required"));
      return;
    }

    const combined = new Date(
      `${formData.value.scheduleDate}T${formData.value.scheduleTime}`,
    );
    if (combined <= new Date()) {
      toast.error(t("streams.addModal.scheduledAt.validation.future"));
      return;
    }
    scheduledAt = combined.toISOString();
  }

  const fd = new FormData();
  fd.append("name", formData.value.name);
  fd.append(
    "platform",
    formData.value.platform === "twitch" ? "twitch" : formData.value.platform,
  );
  fd.append("scheduleType", formData.value.scheduleType);
  if (scheduledAt) fd.append("scheduledAt", scheduledAt);
  if (formData.value.comment) fd.append("comment", formData.value.comment);

  if (!formData.value.isAutomatic) {
    fd.append("streamKey", formData.value.streamKey);
  } else {
    fd.append("isAutomatic", "1");
    fd.append("youtubeChannelId", formData.value.youtubeChannelId);
    fd.append("privacyStatus", formData.value.privacyStatus);
    fd.append("isMadeForKids", formData.value.isMadeForKids ? "1" : "0");
    if (formData.value.youtubeTags)
      fd.append("youtubeTags", formData.value.youtubeTags);
    if (formData.value.youtubeDescription)
      fd.append("youtubeDescription", formData.value.youtubeDescription);
    if (formData.value.youtubeCategoryId)
      fd.append("youtubeCategoryId", formData.value.youtubeCategoryId);
    if (formData.value.youtubeThumbnail) {
      fd.append("youtubeThumbnail", formData.value.youtubeThumbnail);
    }
  }

  if (formData.value.selectionType === "video") {
    fd.append("videoId", formData.value.video);
  }

  if (formData.value.selectionType === "playlist") {
    fd.append("playlistId", formData.value.playlist);
  }

  try {
    saving.value = true;
    const { data } = await api.post("/streams/start", fd, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    emit("create", data.data);
    emit("close");
  } catch (e) {
    console.error("Stream create failed", e);
    toast.error(e.response?.data?.message || t("streams.addModal.createError"));
  } finally {
    saving.value = false;
  }
};

const fetchPlaylists = async () => {
  const { data } = await api.get("/playlists");
  playlists.value = data.data.data;
};

const youtubeCategories = ref([
  { id: "1", name: "Film & Animation" },
  { id: "2", name: "Autos & Vehicles" },
  { id: "10", name: "Music" },
  { id: "15", name: "Pets & Animals" },
  { id: "17", name: "Sports" },
  { id: "18", name: "Short Movies" },
  { id: "19", name: "Travel & Events" },
  { id: "20", name: "Gaming" },
  { id: "21", name: "Videoblogging" },
  { id: "22", name: "People & Blogs" },
  { id: "23", name: "Comedy" },
  { id: "24", name: "Entertainment" },
  { id: "25", name: "News & Politics" },
  { id: "26", name: "Howto & Style" },
  { id: "27", name: "Education" },
  { id: "28", name: "Science & Technology" },
  { id: "30", name: "Movies" },
  { id: "31", name: "Anime/Animation" },
  { id: "32", name: "Action/Adventure" },
  { id: "33", name: "Classics" },
  { id: "34", name: "Comedy" },
  { id: "35", name: "Documentary" },
  { id: "36", name: "Drama" },
  { id: "37", name: "Family" },
  { id: "38", name: "Foreign" },
  { id: "39", name: "Horror" },
  { id: "40", name: "Sci-Fi/Fantasy" },
  { id: "41", name: "Thriller" },
  { id: "42", name: "Shorts" },
  { id: "43", name: "Shows" },
  { id: "44", name: "Trailers" },
]);

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
  return videoStore.videos.map((v) => ({
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

onMounted(() => {
  fetchPlaylists();
  videoStore.fetchVideos();
  youtubeStore.fetchChannels();
});
</script>
