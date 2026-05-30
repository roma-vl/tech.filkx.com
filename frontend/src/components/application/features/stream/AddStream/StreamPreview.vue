<template>
  <div class="space-y-4">
    <label
      class="block text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest opacity-50"
    >
      {{ $t("media.playerModal.preview") }}
    </label>

    <div
      class="relative aspect-video bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden border-2 border-dashed flex flex-col items-center justify-center group/preview transition-all duration-300"
      :class="[
        isDragging
          ? 'border-primary bg-primary/5 scale-[1.02] shadow-lg'
          : 'border-gray-200 dark:border-gray-700 hover:border-primary/50',
      ]"
      @dragover.prevent="handleDragOver"
      @dragleave="handleDragLeave"
      @drop.prevent="handleDrop"
    >
      <img
        v-if="previewThumbnail"
        :src="previewThumbnail"
        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/preview:scale-110"
      >
      <div
        v-if="previewThumbnail"
        class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
      />

      <!-- Drag and drop overlay for YouTube Automatic -->
      <div
        v-if="isAutomaticYouTube"
        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/preview:opacity-100 transition-opacity bg-black/20 backdrop-blur-[2px]"
        :class="{ 'opacity-100': isDragging }"
      >
        <div class="flex flex-col items-center text-white text-center p-4">
          <div
            class="w-12 h-12 mb-2 rounded-full bg-white/20 flex items-center justify-center"
          >
            <UploadIcon class="w-6 h-6" />
          </div>
          <p
            class="text-xs font-black uppercase tracking-widest drop-shadow-md"
          >
            {{ $t("streams.addModal.youtubeSettings.selectThumbnail") }}
          </p>
          <p class="text-[10px] opacity-70 mt-1 uppercase tracking-tight">
            {{ isDragging ? "Drop to upload" : "Click or Drag & Drop" }}
          </p>
          <button
            class="absolute inset-0 w-full h-full cursor-pointer"
            @click="$refs.thumbnailInput.click()"
          />
        </div>
      </div>

      <div
        v-if="!previewThumbnail"
        class="flex flex-col items-center text-gray-400 dark:text-gray-500 p-8 text-center"
      >
        <div
          class="w-12 h-12 mb-4 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6"
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
        <p class="text-xs font-bold uppercase tracking-wider">
          {{ $t("streams.editModal.notSelected") }}
        </p>
      </div>

      <div
        v-else
        class="absolute bottom-4 left-4 right-4"
      >
        <p
          class="text-white font-black text-sm line-clamp-1 drop-shadow-md uppercase tracking-tight"
        >
          {{ previewTitle }}
        </p>
        <div class="flex items-center gap-2 mt-1">
          <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse" />
          <span
            class="text-[10px] text-white/80 font-bold uppercase tracking-widest"
          >{{ $t("streams.status.live") }}
            {{ $t("media.playerModal.preview") }}</span>
        </div>
      </div>

      <input
        v-if="isAutomaticYouTube"
        ref="thumbnailInput"
        type="file"
        class="hidden"
        accept="image/*"
        @change="handleThumbnailChange"
      >
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { UploadIcon } from "lucide-vue-next";

const props = defineProps({
  isAutomaticYouTube: {
    type: Boolean,
    default: false,
  },
  previewThumbnail: {
    type: String,
    default: null,
  },
  previewTitle: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["thumbnail-change"]);
const isDragging = ref(false);
const thumbnailInput = ref(null);

const handleThumbnailChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    emit("thumbnail-change", file);
  }
};

const handleDragOver = () => {
  if (props.isAutomaticYouTube) {
    isDragging.value = true;
  }
};

const handleDragLeave = () => {
  isDragging.value = false;
};

const handleDrop = (e) => {
  isDragging.value = false;
  if (!props.isAutomaticYouTube) return;

  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith("image/")) {
    emit("thumbnail-change", file);
  }
};
</script>
