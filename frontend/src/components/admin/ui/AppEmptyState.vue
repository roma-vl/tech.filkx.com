<template>
  <div
    class="group flex flex-col items-center text-center py-12 px-4 text-gray-500 dark:text-gray-400"
  >
    <div
      class="flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4 transition-all duration-300 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20"
    >
      <FolderIcon
        class="w-8 h-8 text-gray-400 dark:text-gray-500 transition-transform duration-300 group-hover:scale-110"
      />
    </div>

    <p
      v-if="title"
      class="text-lg font-medium text-gray-900 dark:text-gray-200"
    >
      {{ title }}
    </p>

    <p
      v-if="description"
      class="text-sm mt-1 max-w-sm"
    >
      {{ description }}
    </p>

    <slot />
  </div>
</template>

<script setup>
import FolderIcon from "@/components/Icon/FolderIcon.vue";

defineProps({
  title: String,
  description: String,
});
</script>
