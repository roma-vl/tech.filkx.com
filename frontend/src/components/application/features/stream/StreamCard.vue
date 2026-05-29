<template>
  <AppCard
    class="stream-card relative group"
    hoverable
  >
    <div
      class="relative cursor-pointer aspect-video overflow-hidden"
      @click="$emit('play', stream)"
    >
      <img
        :src="thumbnailUrl"
        :alt="stream.name"
        class="w-full h-full object-cover bg-gray-200 dark:bg-gray-700 rounded-t-xl transition-transform duration-500 group-hover:scale-105"
      >

      <div
        class="absolute top-0 left-0 right-0 p-3 flex justify-between items-start pointer-events-none"
      >
        <div
          class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-xl shadow-lg backdrop-blur-md pointer-events-auto"
        >
          <img
            :src="getPlatformIcon(stream.platform)"
            :alt="stream.platform"
            class="w-6 h-6 object-contain"
          >
        </div>

        <div class="flex gap-2 pointer-events-auto z-10">
          <button
            class="p-2 bg-white/90 dark:bg-gray-800/90 text-gray-600 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400 rounded-xl shadow-lg backdrop-blur-md transition-all hover:scale-110 active:scale-95"
            @click.stop="$emit('edit', stream)"
          >
            <EditIcon class="w-5 h-5" />
          </button>
          <button
            v-if="stream.status === 'failed'"
            class="p-2 bg-white/90 dark:bg-gray-800/90 text-primary-600 dark:text-primary-400 hover:text-primary-700 rounded-xl shadow-lg backdrop-blur-md transition-all hover:scale-110 active:scale-95"
            :title="t('streams.actions.restart')"
            @click.stop="$emit('restart', stream)"
          >
            <ArrowPathIcon class="w-5 h-5" />
          </button>
          <button
            class="p-2 bg-white/90 dark:bg-gray-800/90 text-gray-600 dark:text-gray-300 hover:text-red-500 rounded-xl shadow-lg backdrop-blur-md transition-all hover:scale-110 active:scale-95"
            @click.stop="$emit('delete', stream)"
          >
            <TrashIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <div class="absolute bottom-3 left-3 flex items-center gap-2">
        <AppBadge
          v-if="stream.status === 'live'"
          variant="danger"
          class="!px-3 !py-1.5 !text-xs !font-bold !uppercase !tracking-wider !rounded-lg shadow-xl animate-pulse bg-red-600 text-white dark:bg-red-600 dark:text-white"
        >
          <template #icon>
            <span class="w-2 h-2 rounded-full bg-white mr-2" />
          </template>
          {{ t("streams.status.live") }}
        </AppBadge>

        <AppBadge
          v-else-if="stream.status === 'starting' || stream.status === 'pending'"
          variant="warning"
          class="!px-3 !py-1.5 !text-xs !font-bold !uppercase !tracking-wider !rounded-lg shadow-xl"
        >
          <template #icon>
            <div
              class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"
            />
          </template>
          {{ t("streams.status.starting") }}
        </AppBadge>

        <div
          v-else-if="stream.status === 'failed'"
          class="flex flex-col gap-1 items-start px-3 py-1.5 bg-gray-900 border border-red-500/50 rounded-lg shadow-xl"
        >
          <AppBadge
            variant="danger"
            class="!bg-transparent !p-0 !text-xs !font-bold !uppercase !tracking-wider"
          >
            <template #icon>
              <span class="w-2 h-2 rounded-full bg-red-500 mr-2" />
            </template>
            {{ t("streams.status.failed") }}
          </AppBadge>
          <div
            v-if="stream.stopReason"
            class="text-[10px] text-gray-400 font-medium normal-case leading-tight"
          >
            {{ t(`streams.stopReasons.${stream.stopReason}`) }}
          </div>
        </div>

        <AppBadge
          v-else-if="stream.status === 'degraded'"
          variant="warning"
          class="!px-3 !py-1.5 !text-xs !font-bold !uppercase !tracking-wider !rounded-lg shadow-xl"
        >
          <template #icon>
            <div class="relative w-2 h-2 mr-2">
              <span class="absolute w-full h-full rounded-full bg-white opacity-75 animate-ping"/>
              <span class="absolute w-full h-full rounded-full bg-white"/>
            </div>
          </template>
          {{ t("streams.status.degraded") }}
        </AppBadge>

        <AppBadge
          v-else-if="stream.status === 'recovering'"
          variant="info"
          class="!px-3 !py-1.5 !text-xs !font-bold !uppercase !tracking-wider !rounded-lg shadow-xl"
        >
          <template #icon>
            <div
              class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"
            />
          </template>
          {{ t("streams.status.recovering") }}
        </AppBadge>
      </div>

      <div
        v-if="hasContent"
        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
        @click.stop="$emit('play', stream)"
      >
        <div
          class="w-16 h-16 rounded-full bg-primary/90 text-white flex items-center justify-center shadow-2xl transform scale-90 group-hover:scale-100 transition-all duration-300"
        >
          <PlayIcon class="w-8 h-8 ml-1 fill-current" />
        </div>
      </div>

      <div
        v-if="stream.status === 'live' && liveDuration"
        class="absolute bottom-3 right-3 bg-black/70 backdrop-blur-md text-white text-xs font-mono px-2 py-1 rounded-lg border border-white/10 shadow-lg"
      >
        {{ liveDuration }}
      </div>
    </div>

    <div class="p-5 border-t border-gray-100 dark:border-gray-800">
      <div class="flex justify-between items-start gap-3 mb-2">
        <h3
          class="text-xl font-semibold text-gray-900 dark:text-white line-clamp-1 flex-1 leading-tight"
        >
          {{ stream.name }}
        </h3>
      </div>

      <p
        v-if="stream.comment"
        class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4 min-h-[40px]"
      >
        {{ stream.comment }}
      </p>

      <div
        class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-800/50"
      >
        <div
          class="flex items-center text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wider"
        >
          <CalendarIcon class="w-4 h-4 mr-2 opacity-70" />
          {{ formattedDate }}
        </div>

        <div
          v-if="stream.selectionType"
          class="flex items-center text-xs font-bold px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-500/20 uppercase"
        >
          {{
            stream.selectionType === "playlist"
              ? t("streams.editModal.playlistOption")
              : t("streams.editModal.videoOption")
          }}
        </div>
      </div>
    </div>
  </AppCard>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import AppCard from "@/components/application/ui/Data/AppCard.vue";
import AppBadge from "@/components/application/ui/Feedback/AppBadge.vue";
import EditIcon from "@/components/Icon/EditIcon.vue";
import TrashIcon from "@/components/Icon/TrashIcon.vue";
import CalendarIcon from "@/components/Icon/CalendarIcon.vue";
import {PlayIcon} from "lucide-vue-next";
import {ArrowPathIcon} from "@heroicons/vue/24/outline";

const { t } = useI18n();

const props = defineProps({
  stream: {
    type: Object,
    required: true,
  },
  liveDuration: {
    type: String,
    default: null,
  },
});

defineEmits(["play", "edit", "delete"]);

const thumbnailUrl = computed(() => {
  if (props.stream.video?.thumbnails?.thumbnail) {
    return props.stream.video.thumbnails.thumbnail;
  }

  if (props.stream.playlist?.videos?.length > 0) {
    const firstVideo = props.stream.playlist.videos[0];
    return (
      firstVideo.thumbnails?.thumbnail ||
      firstVideo.video?.thumbnails?.thumbnail
    );
  }

  return null;
});

const hasContent = computed(() => {
  return props.stream.video || props.stream.playlist?.videos?.length > 0;
});

const formattedDate = computed(() => {
  const date = new Date(
    props.stream.startedAt ||
      props.stream.scheduledAt ||
      props.stream.createdAt,
  );
  return date.toLocaleDateString("uk-UA", {
    day: "numeric",
    month: "short",
    hour: "2-digit",
    minute: "2-digit",
  });
});

const getPlatformIcon = (platformId) => {
  if (!platformId) return "";
  return new URL(
    `/src/assets/images/platforms/${platformId}.png`,
    import.meta.url,
  ).href;
};
</script>

<style scoped>
.stream-card {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.stream-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.1);
}
</style>
