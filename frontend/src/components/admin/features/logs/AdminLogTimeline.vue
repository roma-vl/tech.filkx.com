<template>
  <div class="relative">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 px-2">
      <h3 class="font-black text-xs uppercase tracking-[0.2em] text-gray-400">
        {{ t("admin.logs.table.title") }}
      </h3>
      <div class="h-px flex-1 mx-6 bg-gradient-to-r from-gray-100 via-gray-200 dark:via-gray-700 to-transparent"/>
    </div>

    <!-- Timeline Container -->
    <div class="relative space-y-8 pb-12">
      <!-- Vertical Line -->
      <div
        class="absolute left-6 top-6 bottom-0 w-px bg-gradient-to-b from-primary-500/50 via-gray-200 dark:via-gray-700 to-transparent"/>

      <AdminLogItem
        v-for="log in logs"
        :key="log.id"
        :log="log"
      />

      <!-- Load More -->
      <div
        v-if="hasMore"
        class="flex justify-center pt-8"
      >
        <button
          :disabled="loading"
          class="group relative px-12 py-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl hover:border-primary-500 transition-all duration-300 disabled:opacity-50"
          @click="$emit('load-more')"
        >
          <div class="flex items-center gap-3">
            <ArrowPathIcon
              v-if="loading"
              class="w-4 h-4 animate-spin text-primary-500"
            />
            <span
              class="text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
              {{ loading ? t("admin.logs.loading") : t("admin.logs.load_more") }}
            </span>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ArrowPathIcon} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";
import AdminLogItem from "./AdminLogItem.vue";

const {t} = useI18n();

defineProps({
  logs: {
    type: Array,
    required: true,
  },
  loading: Boolean,
  hasMore: Boolean,
});

defineEmits(["load-more"]);
</script>
