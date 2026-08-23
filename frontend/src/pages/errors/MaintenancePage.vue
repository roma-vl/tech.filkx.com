<template>
  <div
    class="min-h-screen bg-gray-50 dark:bg-gray-950 flex items-center justify-center p-6 text-center"
  >
    <div class="max-w-md w-full space-y-8">
      <div class="relative flex justify-center">
        <div
          class="absolute inset-0 bg-indigo-500/20 blur-3xl rounded-full scale-150 animate-pulse"
        />
        <div
          class="relative bg-white dark:bg-gray-900 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-800"
        >
          <WrenchScrewdriverIcon
            class="w-16 h-16 text-indigo-600 animate-bounce"
          />
        </div>
      </div>
      <div class="space-y-4">
        <h1
          class="text-4xl font-black text-gray-900 dark:text-white tracking-tight"
        >
          {{ t("errors.maintenance.title") }}
          <span class="text-indigo-600">{{
            t("errors.maintenance.titleHighlight")
          }}</span>
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-lg leading-relaxed">
          {{ t("errors.maintenance.message") }}
        </p>
      </div>

      <div
        class="bg-indigo-600/5 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-6 backdrop-blur-sm"
      >
        <div
          class="flex items-center gap-3 justify-center text-indigo-700 dark:text-indigo-400 font-bold mb-2"
        >
          <ClockIcon class="w-5 h-5" />
          <span>{{ t("errors.maintenance.estimatedDowntime") }}</span>
        </div>
        <p class="text-sm text-indigo-600/70 dark:text-indigo-400/60">
          {{ t("errors.maintenance.teamNote") }}
        </p>
      </div>

      <div class="pt-8 flex flex-col items-center gap-4">
        <div class="flex gap-4">
          <div class="w-2 h-2 rounded-full bg-indigo-600 animate-ping" />
          <div
            class="w-2 h-2 rounded-full bg-indigo-600 animate-ping delay-75"
          />
          <div
            class="w-2 h-2 rounded-full bg-indigo-600 animate-ping delay-150"
          />
        </div>
        <p class="text-xs text-gray-400 font-medium uppercase tracking-widest">
          {{ t("errors.maintenance.brand") }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { WrenchScrewdriverIcon, ClockIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<style scoped>
.delay-75 {
  animation-delay: 75ms;
}
.delay-150 {
  animation-delay: 150ms;
}
</style>
