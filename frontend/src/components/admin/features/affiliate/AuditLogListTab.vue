<template>
  <div
    v-if="loading"
    class="text-center py-12"
  >
    <div
      class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto mb-4"
    />
    <p class="text-gray-500 dark:text-gray-400">
      {{ $t("admin.affiliates.messages.loading") }}
    </p>
  </div>

  <div
    v-else-if="logs.length === 0"
    class="text-center py-12 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
  >
    <p class="text-gray-500 dark:text-gray-400">
      {{ $t("admin.affiliates.messages.no_logs") }}
    </p>
  </div>

  <div
    v-else
    class="relative space-y-4"
  >
    <!-- Timeline Line -->
    <div
      class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-100 dark:bg-gray-700 ml-[-1px]"
    />

    <div
      v-for="log in logs"
      :key="log.id"
      class="relative pl-12"
    >
      <!-- Timeline Dot -->
      <div
        class="absolute left-6 top-5 w-3 h-3 rounded-full bg-primary-500 border-2 border-white dark:border-gray-800 ml-[-6px] z-10 shadow-sm"
      />

      <div
        class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 hover:border-primary-200 dark:hover:border-primary-900/50 transition-all group"
      >
        <div
          class="flex flex-col sm:flex-row sm:items-center justify-between mb-2 gap-2"
        >
          <div class="flex items-center gap-2">
            <span
              class="text-xs font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 px-2 py-0.5 rounded-md"
            >
              {{ log.action }}
            </span>
          </div>
          <span
            class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase"
          >
            {{
              new Date(log.createdAt).toLocaleString(
                $i18n.locale === "uk" ? "uk-UA" : "en-US",
              )
            }}
          </span>
        </div>

        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">
          {{ log.message }}
        </p>

        <div
          class="mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between"
        >
          <div class="flex items-center gap-2">
            <div
              class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[10px] font-bold text-gray-500 uppercase"
            >
              {{ log.user.name.charAt(0) }}
            </div>
            <span
              class="text-[10px] font-bold text-gray-500 uppercase tracking-tight"
            >
              By: {{ log.user.name }}
            </span>
          </div>
          <span
            class="text-[10px] font-mono text-gray-400 dark:text-gray-600 italic"
          >
            IP: {{ log.ipAddress }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});
</script>
