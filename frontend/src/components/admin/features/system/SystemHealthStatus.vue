<template>
  <div
    class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm p-8"
  >
    <div class="absolute top-0 right-0 p-8">
      <div class="relative">
        <div
          class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-20"
        />
        <div
          class="relative w-4 h-4 rounded-full bg-green-500 shadow-lg shadow-green-500/50"
        />
      </div>
    </div>

    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-8 relative z-10"
    >
      <div class="space-y-2">
        <h2
          class="text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-tight uppercase"
        >
          {{ t("admin.system.status.operational") }}
        </h2>
        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">
          {{ t("admin.system.description") }}
        </p>
      </div>

      <div class="flex items-center gap-12">
        <div class="flex flex-col">
          <span
            class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
          >
            {{ t("admin.system.resources.uptime") }}
          </span>
          <span
            class="text-lg font-black text-gray-900 dark:text-white font-mono"
          >
            {{ uptime || "00:00:00:00" }}
          </span>
        </div>
        <div class="h-10 w-px bg-gray-100 dark:bg-gray-700" />
        <div class="flex flex-col">
          <span
            class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1"
          >
            Region
          </span>
          <span
            class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight"
          >
            EU-CENTRAL-1
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  uptime: String,
  loading: Boolean,
});
</script>
