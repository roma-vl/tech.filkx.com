<template>
  <div
    class="group flex items-center gap-4 p-4 bg-white/40 dark:bg-gray-800/20 border-b border-gray-100/50 dark:border-gray-700/30 hover:bg-white/80 dark:hover:bg-gray-800/60 transition-all duration-300"
  >
    <div
      class="relative w-32 aspect-video flex-shrink-0 group/thumb cursor-pointer"
      @click="$emit('play', playlist)"
    >
      <div
        class="absolute -top-1 -right-1 w-full h-full bg-primary-100/50 dark:bg-primary-900/20 rounded-lg transform rotate-2 transition-transform duration-300 group-hover/thumb:rotate-3 group-hover/thumb:scale-105"
      />
      <div
        class="absolute inset-0 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200/60 dark:border-gray-700/60 overflow-hidden shadow-sm transition-transform duration-300 group-hover/thumb:scale-[1.02]"
      >
        <img
          v-if="playlist.videos?.[0]?.thumbnails?.thumbnail"
          :src="playlist.videos[0].thumbnails.thumbnail"
          class="w-full h-full object-cover"
          alt=""
        >
        <div
          v-else
          class="w-full h-full flex items-center justify-center text-gray-400"
        >
          <PlaylistsIcon class="w-8 h-8 opacity-20" />
        </div>

        <div
          class="absolute inset-0 bg-black/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]"
        >
          <div
            class="w-10 h-10 rounded-full bg-primary-500/90 text-white flex items-center justify-center shadow-lg transform scale-90 group-hover/thumb:scale-100 transition-transform duration-300"
          >
            <PlayIcon class="w-5 h-5 ml-0.5" />
          </div>
        </div>

        <div
          class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/70 backdrop-blur-sm text-xs font-bold text-white uppercase tracking-widest"
        >
          {{
            t("media.playlists.videoCount", {
              count: playlist.videosCount || 0,
            })
          }}
        </div>
      </div>
    </div>

    <div class="flex-1 min-w-0 pr-4">
      <h4
        class="font-bold text-gray-900 dark:text-white truncate text-base mb-1.5 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300"
      >
        {{ playlist.name }}
      </h4>
      <div
        class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 font-medium"
      >
        <div class="flex items-center gap-1">
          <ClockIcon class="w-3.5 h-3.5 opacity-60" />
          {{ playlist.totalDurationFormatted || "00:00" }}
        </div>
        <div class="flex items-center gap-1">
          <CalendarIcon class="w-3.5 h-3.5 opacity-60" />
          {{ formatDate(playlist.createdAt) }}
        </div>
      </div>
    </div>

    <div
      class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0"
    >
      <AppButton
        variant="ghost"
        size="sm"
        class="!p-2 hover:!bg-primary-50 hover:!text-primary-600 dark:hover:!bg-primary-900/30 text-gray-400 dark:text-gray-300 rounded-xl"
        @click="$emit('edit', playlist)"
      >
        <EditIcon class="w-4 h-4" />
      </AppButton>
      <AppButton
        variant="ghost"
        size="sm"
        class="!p-2 hover:!bg-red-50 hover:!text-red-500 dark:hover:!bg-red-900/30 text-gray-400 dark:text-gray-300 rounded-xl"
        @click="$emit('delete', playlist.id)"
      >
        <TrashIcon class="w-4 h-4" />
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {
  PlayIcon,
  LayoutListIcon as PlaylistsIcon,
  Edit3Icon as EditIcon,
  Trash2Icon as TrashIcon,
  ClockIcon,
  CalendarIcon,
} from "lucide-vue-next";

const { t } = useI18n();

const props = defineProps({
  playlist: {
    type: Object,
    required: true,
  },
});

defineEmits(["play", "edit", "delete"]);

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};
</script>
