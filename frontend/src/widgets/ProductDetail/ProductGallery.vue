<template>
  <div class="flex flex-col-reverse md:flex-row gap-4">
    <!-- Thumbnails -->
    <div class="flex md:flex-col gap-2 overflow-x-auto md:overflow-x-visible shrink-0 pb-1 md:pb-0">
      <button
        v-for="(image, index) in galleryImages"
        :key="image.label"
        :class="selectedImageIndex === index
          ? 'border-[#00a046] ring-1 ring-[#00a046]/25 bg-white dark:bg-zinc-900'
          : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 bg-white dark:bg-zinc-900'"
        class="w-16 h-16 md:w-[72px] md:h-[72px] shrink-0 border rounded-lg overflow-hidden cursor-pointer p-1.5 transition-all"
        @click="$emit('select-image', index)"
      >
        <img
          :alt="image.label"
          class="w-full h-full object-contain hover:scale-105 transition-transform duration-200"
          :src="image.src"
        />
      </button>
    </div>

    <!-- Main image -->
    <div
      class="flex-1 bg-white dark:bg-white/98 rounded-xl border border-zinc-200 dark:border-zinc-800 relative group overflow-hidden flex justify-center items-center cursor-zoom-in select-none min-h-[320px] md:min-h-[400px]"
      style="aspect-ratio: 1.1/1"
      @mousemove="$emit('mouse-move', $event)"
      @mouseleave="$emit('mouse-leave')"
    >
      <img
        :alt="productName"
        class="max-h-[360px] md:max-h-[440px] object-contain transition-transform duration-100 p-8 pointer-events-none w-full"
        :style="zoomStyle"
        :src="selectedImage"
      />

      <!-- Zoom hint -->
      <div
        class="absolute bottom-3 right-3 flex items-center gap-1.5 bg-zinc-900/70 backdrop-blur-sm rounded-lg px-2.5 py-1.5 transition-opacity"
        :class="isZoomed ? 'opacity-90' : 'opacity-0 group-hover:opacity-70'"
      >
        <span class="material-symbols-outlined text-white text-[14px]">{{ isZoomed ? 'zoom_in' : 'search' }}</span>
        <span class="text-[10px] font-semibold text-white">{{ isZoomed ? 'Збільшено' : 'Наведіть для наближення' }}</span>
      </div>

      <!-- Prev/Next arrows -->
      <div class="absolute inset-x-3 top-1/2 -translate-y-1/2 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
        <button
          class="w-9 h-9 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm rounded-full shadow-md hover:bg-white dark:hover:bg-zinc-800 flex items-center justify-center border border-zinc-200 dark:border-zinc-700 transition-all hover:scale-105 pointer-events-auto"
          @click.stop="$emit('prev-image')"
        >
          <span class="material-symbols-outlined text-[#00a046] text-[20px]">chevron_left</span>
        </button>
        <button
          class="w-9 h-9 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm rounded-full shadow-md hover:bg-white dark:hover:bg-zinc-800 flex items-center justify-center border border-zinc-200 dark:border-zinc-700 transition-all hover:scale-105 pointer-events-auto"
          @click.stop="$emit('next-image')"
        >
          <span class="material-symbols-outlined text-[#00a046] text-[20px]">chevron_right</span>
        </button>
      </div>

      <!-- Image counter -->
      <div class="absolute bottom-3 left-3 text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity">
        {{ selectedImageIndex + 1 }} / {{ galleryImages.length }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  galleryImages: Array<{ label: string; src: string }>;
  selectedImageIndex: number;
  zoomStyle: Record<string, string>;
  isZoomed: boolean;
  selectedImage: string;
  productName: string;
}>();

defineEmits<{
  (e: "select-image", index: number): void;
  (e: "next-image"): void;
  (e: "prev-image"): void;
  (e: "mouse-move", event: MouseEvent): void;
  (e: "mouse-leave"): void;
}>();
</script>
