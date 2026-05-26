<script setup>
import { computed } from "vue";
import { useUiStore } from "@/stores/ui";

const ui = useUiStore();
const isDark = computed(() => ui.darkMode);

const toggle = () => ui.toggleDark();
</script>

<template>
  <button
    type="button"
    class="relative inline-flex h-8 w-14 items-center rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
    @click="toggle"
  >
    <span
      class="absolute inset-0 rounded-full bg-gray-200 dark:bg-gray-700 transition-colors duration-300 z-0"
    />

    <span
      class="absolute left-1 z-20 flex h-5 w-5 items-center justify-center text-[14px] leading-none transition-opacity duration-300 text-yellow-500 pointer-events-none"
      :class="!isDark ? 'opacity-0' : 'opacity-100'"
    >
      ☀
    </span>

    <span
      class="absolute right-1 z-20 flex h-5 w-5 items-center justify-center text-[14px] leading-none transition-opacity duration-300 text-indigo-300 pointer-events-none"
      :class="!isDark ? 'opacity-100' : 'opacity-0'"
    >
      🌙
    </span>

    <span
      class="relative z-30 inline-block h-6 w-6 rounded-full bg-white dark:bg-gray-900 shadow-md transform transition-transform duration-300"
      :class="isDark ? 'translate-x-7' : 'translate-x-1'"
    />
  </button>
</template>
