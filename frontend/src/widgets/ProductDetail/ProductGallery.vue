<template>
  <div class="flex flex-col md:flex-row gap-4">
    <!-- Thumbnails list -->
    <div
      class="flex md:flex-col gap-3 overflow-x-auto md:overflow-x-visible shrink-0 order-2 md:order-1"
    >
      <button
        v-for="(image, index) in galleryImages"
        :key="image.label"
        :class="
          selectedImageIndex === index
            ? 'border-[#00a046] ring-2 ring-[#00a046]/20'
            : 'border-zinc-200 dark:border-zinc-800 hover:border-[#00a046]'
        "
        class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 border rounded-md overflow-hidden cursor-pointer shadow-sm bg-white p-1 transition-all"
        type="button"
        @click="$emit('select-image', index)"
      >
        <img
          :alt="image.label"
          class="w-full h-full object-contain hover:scale-105 transition-transform"
          :src="image.src"
        />
      </button>
    </div>

    <!-- Main Photo Box with hover Zoom Magnifier -->
    <div
      class="flex-1 bg-white dark:bg-white/95 rounded-lg border border-zinc-200/80 dark:border-zinc-800 shadow-sm relative group overflow-hidden flex justify-center items-center aspect-[1.12/1] cursor-zoom-in order-1 md:order-2 select-none"
      @mousemove="$emit('mouse-move', $event)"
      @mouseleave="$emit('mouse-leave')"
    >
      <img
        :alt="productName"
        class="max-h-[380px] md:max-h-[460px] object-contain transition-transform duration-100 p-6 pointer-events-none"
        :style="zoomStyle"
        :src="selectedImage"
      />

      <!-- Zoom details guide text -->
      <div
        class="absolute bottom-4 left-4 bg-zinc-955/80 backdrop-blur-sm border border-white/10 rounded px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-white"
      >
        {{ isZoomed ? "Збільшено" : "Наведіть для наближення" }}
      </div>

      <div
        class="absolute inset-x-4 top-1/2 -translate-y-1/2 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <button
          class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow hover:bg-white transition-colors"
          type="button"
          @click.stop="$emit('prev-image')"
        >
          <span class="material-symbols-outlined text-[#00a046]"
            >chevron_left</span
          >
        </button>
        <button
          class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow hover:bg-white transition-colors"
          type="button"
          @click.stop="$emit('next-image')"
        >
          <span class="material-symbols-outlined text-[#00a046]"
            >chevron_right</span
          >
        </button>
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
