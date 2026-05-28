<template>
  <div
    class="group relative bg-white/60 dark:bg-gray-800/40 backdrop-blur-md rounded-3xl overflow-hidden border border-gray-200/60 dark:border-gray-700/60 hover:border-primary-500/30 hover:shadow-xl hover:shadow-primary-500/10 transition-all duration-300 flex flex-col h-full shadow-sm"
  >
    <div class="p-6">
      <div class="flex items-start justify-between mb-1">
        <div class="flex-1 min-w-0">
          <h4
            class="text-lg font-bold text-gray-900 dark:text-white truncate leading-tight group-hover:text-primary transition-colors"
          >
            {{ playlist.name }}
          </h4>
          <div
            class="flex items-center gap-3 mt-1 text-xs font-bold uppercase tracking-widest text-gray-400"
          >
            <span class="flex items-center gap-1">
              <PlayIcon class="w-3 h-3 text-primary" />
              {{
                t("media.playlists.videoCount", {
                  count: playlist.videosCount || 0,
                })
              }}
            </span>
            <span class="flex items-center gap-1">
              <ClockIcon class="w-3 h-3" />
              {{ playlist.totalDurationFormatted || "00:00" }}
            </span>
          </div>
        </div>
        <div
          class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0"
        >
          <AppButton
            variant="ghost"
            size="sm"
            class="!p-2 hover:!bg-primary-50 hover:!text-primary-600 dark:hover:!bg-primary-900/30 text-gray-400 dark:text-gray-300 rounded-xl"
            @click.stop="$emit('edit', playlist)"
          >
            <EditIcon class="w-4 h-4" />
          </AppButton>
          <AppButton
            variant="ghost"
            size="sm"
            class="!p-2 hover:!bg-red-50 hover:!text-red-500 dark:hover:!bg-red-900/30 text-gray-400 dark:text-gray-300 rounded-xl"
            @click.stop="$emit('delete', playlist.id)"
          >
            <TrashIcon class="w-4 h-4" />
          </AppButton>
        </div>
      </div>

      <div
        v-if="playlist.videos?.length > 0"
        class="space-y-3 mt-4"
      >
        <VideoItem
          v-for="video in playlist.videos.slice(0, 3)"
          :key="video.id"
          :video="video"
          @play="$emit('play', video.id)"
        />
        <div
          v-if="playlist.videos.length > 3"
          class="text-center"
        >
          <span
            class="text-xs font-bold text-gray-400 uppercase tracking-widest"
          >
            +{{ playlist.videos.length - 3 }} more
          </span>
        </div>
      </div>

      <div
        v-else
        class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-gray-200/50 dark:border-gray-700/50 rounded-2xl bg-gray-50/30 dark:bg-gray-900/10"
      >
        <PlaylistsIcon
          class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2"
        />
        <span
          class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"
        >{{ t("media.playlists.empty") }}</span>
      </div>
    </div>

    <div
      class="mt-auto border-t border-gray-100/50 dark:border-gray-700/30 p-4 bg-white/30 dark:bg-gray-800/20"
    >
      <AppButton
        variant="primary"
        size="sm"
        full-width
        class="!rounded-xl shadow-sm hover:shadow-md transition-all font-bold tracking-wide"
        :disabled="!playlist.videos?.length"
        @click="$emit('play-playlist', playlist)"
      >
        <template #prefix>
          <PlayIcon class="w-4 h-4 mr-2" />
        </template>
        {{ t("media.playlistCard.playPlaylist") }}
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import VideoItem from "@/components/application/features/playlist/VideoItem.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {
  Edit3Icon as EditIcon,
  Trash2Icon as TrashIcon,
  PlayIcon,
  LayoutListIcon as PlaylistsIcon,
  ClockIcon,
} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps({
  playlist: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["edit", "delete", "play", "play-playlist"]);

const edit = () => emit("edit", props.playlist);
const remove = () => emit("delete", props.playlist.id);
</script>
