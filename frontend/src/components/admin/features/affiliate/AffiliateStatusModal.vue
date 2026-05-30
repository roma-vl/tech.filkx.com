<template>
  <AppModal
    :is-open="!!affiliate"
    :title="t('admin.affiliates.actions.edit_status')"
    @close="$emit('close')"
  >
    <div class="space-y-4 pt-2">
      <div
        class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700"
      >
        <p
          class="text-xs text-gray-500 uppercase font-black tracking-wider mb-1"
        >
          {{ t("admin.affiliates.table.partner") }}
        </p>
        <p class="font-bold text-gray-900 dark:text-white">
          {{ affiliate?.user.name }}
        </p>
        <p class="text-sm text-gray-500">
          {{ affiliate?.user.email }}
        </p>
      </div>

      <AppSelect
        v-model="newStatus"
        :label="t('admin.affiliates.table.status')"
        :options="statusOptions"
      />
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors"
          @click="$emit('close')"
        >
          {{ t("common.cancel") }}
        </button>
        <button
          class="px-6 py-2 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary-500/20"
          :disabled="loading"
          @click="updateStatus"
        >
          <span
            v-if="loading"
            class="flex items-center gap-2"
          >
            <svg
              class="animate-spin h-4 w-4 text-white"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
                fill="none"
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            {{ t("admin.affiliates.actions.saving") }}
          </span>
          <span v-else>{{ t("common.save") }}</span>
        </button>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";

const props = defineProps({
  affiliate: { type: Object, default: null },
});

const emit = defineEmits(["close", "updated"]);

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const newStatus = ref("");

const statusOptions = computed(() => [
  { id: "pending", name: t("admin.affiliates.status.pending") },
  { id: "active", name: t("admin.affiliates.status.active") },
  { id: "rejected", name: t("admin.affiliates.status.rejected") },
  { id: "suspended", name: t("admin.affiliates.status.suspended") },
]);

watch(
  () => props.affiliate,
  (affiliate) => {
    if (affiliate) {
      newStatus.value = affiliate.status;
    }
  },
  { immediate: true },
);

const updateStatus = async () => {
  loading.value = true;
  try {
    await api.post(`/admin/affiliates/${props.affiliate.id}/status`, {
      status: newStatus.value,
    });
    toast.success(t("admin.affiliates.messages.status_updated"));
    emit("updated");
  } catch (err) {
    toast.error(t("admin.affiliates.messages.status_update_error"));
  } finally {
    loading.value = false;
  }
};
</script>
