<template>
  <div
    class="video-item group flex items-center gap-3 p-2 rounded-xl hover:bg-primary/5 cursor-pointer transition-all duration-200"
    @click="$emit('play', video.id)"
  >
    <div
      class="relative w-20 h-12 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 border border-gray-100 dark:border-gray-700"
    >
      <img
        v-if="video.thumbnails?.thumbnail"
        :src="video.thumbnails.thumbnail"
        :alt="video.title"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
        loading="lazy"
      >
      <div
        v-else
        class="w-full h-full flex items-center justify-center text-gray-300"
      >
        <PlayIcon class="w-4 h-4 opacity-20" />
      </div>

      <div
        class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
      >
        <div
          class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-transform"
        >
          <PlayIcon class="w-3 h-3 ml-0.5" />
        </div>
      </div>
    </div>

    <div class="min-w-0 flex-1">
      <div
        class="font-bold text-xs truncate text-gray-700 dark:text-gray-300 group-hover:text-primary transition-colors"
        :title="video.title"
      >
        {{ video.title }}
      </div>
      <div
        class="text-[9px] font-black uppercase tracking-widest text-gray-400 mt-0.5"
      >
        {{ video.durationFormatted || "00:00" }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { PlayIcon } from "lucide-vue-next";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  video: {
    type: Object,
    required: true,
  },
});

defineEmits(["play"]);
</script>

<style scoped>
.video-item {
  transition: background-color 0.2s ease;
}

.video-item:hover {
  background-color: rgba(59, 130, 246, 0.06);
}

.dark .video-item:hover {
  background-color: rgba(59, 130, 246, 0.12);
}
</style>
