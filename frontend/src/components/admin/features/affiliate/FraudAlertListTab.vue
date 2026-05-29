<template>
  <div
    v-if="alerts.length === 0"
    class="text-center py-12 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
  >
    <p class="text-gray-500 dark:text-gray-400">
      {{ $t("admin.affiliates.messages.no_fraud") }}
    </p>
  </div>

  <div
    v-else
    class="grid grid-cols-1 gap-4"
  >
    <div
      v-for="(alert, idx) in alerts"
      :key="idx"
      class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all group"
    >
      <div class="flex items-start justify-between">
        <div class="flex gap-4">
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center bg-gray-50 dark:bg-gray-700 group-hover:scale-110 transition-transform duration-300"
          >
            <span class="text-2xl">{{ alert.icon }}</span>
          </div>
          <div class="flex-1">
            <h4
              class="font-bold text-gray-900 dark:text-white capitalize text-lg"
            >
              {{ alert.type.replace(/_/g, " ") }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              {{ alert.details }}
            </p>
            <div
              class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-50 dark:border-gray-700"
            >
              <p class="text-xs font-medium text-gray-500">
                <span class="opacity-60">{{ $t("admin.affiliates.table.partner") }}:</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ alert.affiliate_name }} (ID:
                  {{ alert.affiliate_id }})</span>
              </p>
            </div>
          </div>
        </div>
        <AdminBadge
          :variant="getSeverityVariant(alert.severity)"
          custom-class="uppercase font-black tracking-widest text-[10px]"
        >
          {{ alert.severity }}
        </AdminBadge>
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";

defineProps({
  alerts: {
    type: Array,
    required: true,
  },
});

const getSeverityVariant = (severity) => {
  const map = {
    high: "error",
    medium: "warning",
    low: "info",
  };
  return map[severity] || "info";
};
</script>
