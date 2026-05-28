<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center"
  >
    <div
      class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-6"
    >
      <svg
        class="w-10 h-10 text-primary-600 dark:text-primary-400"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
        />
      </svg>
    </div>
    <h2 class="text-2xl font-bold mb-2">
      {{ title }}
    </h2>
    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
      This page is currently under development. Here you will be able to manage
      {{ title.toLowerCase() }}.
    </p>
    <div class="mt-8">
      <router-link
        to="/admin"
        class="inline-flex items-center px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-medium transition-all shadow-lg hover:shadow-primary-500/25"
      >
        Back to Dashboard
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const title = computed(() => route.meta.title || "Admin Page");
</script>
