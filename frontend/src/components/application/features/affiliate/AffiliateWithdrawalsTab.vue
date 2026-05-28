<template>
  <div class="space-y-6">
    <!-- Payout History Placeholder -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
    >
      <div
        class="px-6 py-5 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between"
      >
        <h2
          class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight"
        >
          {{ t("affiliate.dashboard.history") }}
        </h2>
      </div>
      <div
        class="py-12 flex flex-col items-center justify-center text-center opacity-40"
      >
        <p class="text-sm font-bold uppercase tracking-widest text-gray-400">
          {{ t("affiliate.dashboard.historyEmpty") }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
});

defineEmits(["withdraw"]);

const { t } = useI18n();
</script>
