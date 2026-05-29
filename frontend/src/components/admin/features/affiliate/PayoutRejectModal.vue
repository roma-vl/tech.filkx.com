<template>
  <AppModal
    :is-open="!!payout"
    :title="t('admin.affiliates.actions.reject_payout')"
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
        <p class="text-lg font-black text-red-600 mt-2">
          ${{ payout?.amount.toFixed(2) }}
        </p>
      </div>

      <AppTextarea
        v-model="reason"
        :label="t('admin.affiliates.actions.reject_reason')"
        :placeholder="t('admin.affiliates.actions.reject_reason_placeholder')"
        rows="4"
        required
      />

      <div
        class="bg-red-50/50 dark:bg-red-900/10 p-4 rounded-xl border border-red-100/50 dark:border-red-900/30"
      >
        <p
          class="text-sm text-red-800 dark:text-red-300 leading-relaxed font-medium"
        >
          {{ t("admin.affiliates.messages.reject_warning") }}
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
          class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-red-500/20"
          :disabled="loading || !reason"
          @click="reject"
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
          <span v-else>{{ t("admin.affiliates.actions.reject") }}</span>
        </button>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {ref} from "vue";
import {useToast} from "vue-toastification";
import {useI18n} from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";

const props = defineProps({
  payout: { type: Object, default: null },
});

const emit = defineEmits(["close", "rejected"]);

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const reason = ref("");

const reject = async () => {
  if (!reason.value) {
    toast.error(t("admin.affiliates.messages.fill_reason"));
    return;
  }

  loading.value = true;
  try {
    await api.post(`/admin/affiliates/payouts/${props.payout.id}/reject`, {
      reason: reason.value,
    });
    toast.success(t("admin.affiliates.messages.payout_rejected"));
    emit("rejected");
  } catch (err) {
    toast.error(
      err.response?.data?.message ||
      t("admin.affiliates.messages.payout_reject_error"),
    );
  } finally {
    loading.value = false;
  }
};
</script>
