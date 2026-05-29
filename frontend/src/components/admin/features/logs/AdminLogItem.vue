<template>
  <div class="relative flex items-start gap-4 group">
    <!-- Icon & Line -->
    <div class="flex flex-col items-center">
      <div
        class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 z-10 shadow-lg border-2 border-white dark:border-gray-800 transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-primary-500/20"
        :class="log.color"
      >
        <component
          :is="log.icon"
          class="w-5 h-5 text-white"
        />
      </div>
    </div>

    <!-- Content Card -->
    <div
      class="flex-1 bg-white dark:bg-gray-800/50 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/50 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300 shadow-sm hover:shadow-xl hover:shadow-primary-500/5"
    >
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
          <p class="font-black text-base text-gray-900 dark:text-white tracking-tight leading-tight uppercase">
            {{ log.message }}
          </p>
          <div class="flex items-center gap-2 mt-1">
            <span
              class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[9px] font-black uppercase tracking-widest border border-gray-200 dark:border-gray-700"
            >
              {{ log.domain }}
            </span>
            <span class="text-[10px] font-bold text-gray-400 font-mono">
              {{ (log.id || "").substring(0, 8) }}
            </span>
          </div>
        </div>
        <div class="flex flex-col items-end shrink-0">
          <span class="text-[11px] font-black text-gray-900 dark:text-white uppercase tracking-tighter">
            {{ formatTime(log.createdAt) }}
          </span>
          <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
            {{ formatDate(log.createdAt) }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-50 dark:border-gray-700/50">
        <div class="flex items-center gap-3 max-w-[200px]">
          <div class="relative">
            <template v-if="log.user?.avatar">
              <img
                :src="log.user.avatar"
                :alt="log.user.name"
                class="w-10 h-10 rounded-xl object-cover border-2 border-white dark:border-gray-800 shadow-sm"
              >
            </template>
            <div
              v-else
              class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 border-2 border-white dark:border-gray-800"
            >
              <UserIcon class="w-5 h-5"/>
            </div>
            <!-- Optional status indicator or badge could go here -->
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">
              {{ t("admin.logs.table.user_system") }}
            </span>
            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">
              {{ log.user?.name || t("admin.logs.table.user_system") }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400">
            <FingerPrintIcon class="w-4 h-4"/>
          </div>
          <div class="flex flex-col">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">
              IP ADDRESS
            </span>
            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 font-mono">
              {{ log.ipAddress }}
            </span>
          </div>
        </div>
      </div>

      <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-2">
          <div class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"/>
          <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
            {{ t("admin.logs.table.action") }}
          </span>
          <span class="text-[11px] font-bold text-gray-600 dark:text-gray-400 font-mono">
            {{ log.action }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {FingerPrintIcon, UserIcon,} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";

const {t} = useI18n();

defineProps({
  log: {
    type: Object,
    required: true,
  },
});

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleTimeString(undefined, {
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
