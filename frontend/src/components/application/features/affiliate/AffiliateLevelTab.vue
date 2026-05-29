<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center"
  >
    <div
      class="w-16 h-16 bg-purple-50 dark:bg-purple-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="w-8 h-8 text-purple-500"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
        />
      </svg>
    </div>
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
      {{ t("affiliate.dashboard.tabs.level") }}
    </h3>
    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
      {{ t("ui.coming_soon") }}
    </p>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>
