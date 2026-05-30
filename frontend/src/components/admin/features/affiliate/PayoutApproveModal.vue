<template>
  <AppModal
    :is-open="!!payout"
    :title="t('admin.affiliates.actions.approve_payout')"
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
          {{ payout?.affiliate.user_name }}
        </p>
        <p class="text-sm text-gray-500">
          {{ payout?.affiliate.user_email }}
        </p>
      </div>

      <div
        class="bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-100/50 dark:border-blue-900/30"
      >
        <p
          class="text-xs text-blue-600 dark:text-blue-400 uppercase font-black tracking-wider mb-1"
        >
          {{ t("admin.affiliates.table.amount") }}
        </p>
        <p class="text-2xl font-black text-blue-700 dark:text-blue-300">
          ${{ payout?.amount.toFixed(2) }}
        </p>
      </div>

      <AppTextarea
        v-model="notes"
        :label="t('admin.affiliates.actions.notes')"
        :placeholder="t('admin.affiliates.actions.notes_placeholder')"
        rows="3"
      />

      <div
        class="bg-yellow-50/50 dark:bg-yellow-900/10 p-4 rounded-xl border border-yellow-100/50 dark:border-yellow-900/30"
      >
        <p
          class="text-sm text-yellow-800 dark:text-yellow-300 leading-relaxed font-medium"
        >
          {{ t("admin.affiliates.messages.approve_warning") }}
        </p>
      </div>
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
          class="px-6 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-green-500/20"
          :disabled="loading"
          @click="approve"
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
            {{ t("admin.affiliates.actions.processing") }}
          </span>
          <span v-else>{{ t("admin.affiliates.actions.approve") }}</span>
        </button>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";

const props = defineProps({
  payout: { type: Object, default: null },
});

const emit = defineEmits(["close", "approved"]);

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const notes = ref("");

const approve = async () => {
  loading.value = true;
  try {
    await api.post(`/admin/affiliates/payouts/${props.payout.id}/approve`, {
      notes: notes.value,
    });
    toast.success(t("admin.affiliates.messages.payout_approved"));
    emit("approved");
  } catch (err) {
    toast.error(
      err.response?.data?.message ||
        t("admin.affiliates.messages.payout_approve_error"),
    );
  } finally {
    loading.value = false;
  }
};
</script>
