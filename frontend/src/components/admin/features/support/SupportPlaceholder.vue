<template>
  <div
    class="flex-1 flex flex-col items-center justify-center text-center p-12 bg-gray-50/20 dark:bg-gray-900/5 animate-in fade-in zoom-in duration-700"
  >
    <div
      class="w-24 h-24 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 flex items-center justify-center mb-8 shadow-xl shadow-primary-500/5 relative group"
    >
      <div
        class="absolute inset-0 bg-primary-500 rounded-[2rem] opacity-0 group-hover:opacity-10 blur-xl transition-opacity duration-500"
      />
      <TicketIcon
        class="w-12 h-12 text-primary-500 opacity-40 group-hover:opacity-100 transition-all duration-500 group-hover:scale-110"
      />
    </div>
    <h3
      class="text-2xl font-black mb-3 text-gray-900 dark:text-white uppercase tracking-tight"
    >
      {{ t("admin.support.ticket.select_prompt") }}
    </h3>
    <p
      class="text-sm font-bold text-gray-400 dark:text-gray-500 max-w-xs leading-relaxed uppercase tracking-widest opacity-60"
    >
      {{ t("admin.support.ticket.select_description") }}
    </p>

    <div class="mt-10 flex gap-4">
      <div
        v-for="i in 3"
        :key="i"
        class="w-2 h-2 rounded-full bg-primary-500/20 animate-pulse"
        :style="{ animationDelay: i * 200 + 'ms' }"
      />
    </div>
  </div>
</template>

<script setup>
import {TicketIcon} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";

const {t} = useI18n();
</script>
