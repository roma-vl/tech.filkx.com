<template>
  <AppModal
    :is-open="!!payout"
    :title="t('admin.affiliates.actions.mark_paid')"
    @close="$emit('close')"
  >
    <div class="grid grid-cols-1 gap-4 pt-2">
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
        <p class="text-lg font-black text-primary-600 mt-2">
          ${{ payout?.amount.toFixed(2) }}
        </p>
      </div>

      <AppSelect
        v-model="method"
        :label="t('admin.affiliates.actions.payout_method')"
        :options="methodOptions"
        required
      />

      <AppInput
        v-model="transactionReference"
        :label="t('admin.affiliates.actions.transaction_ref')"
        :placeholder="t('admin.affiliates.actions.transaction_ref_placeholder')"
        required
      />

      <AppTextarea
        v-model="notes"
        :label="t('admin.affiliates.actions.notes')"
        :placeholder="t('admin.affiliates.actions.notes_placeholder')"
        rows="2"
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
          :disabled="loading || !method || !transactionReference"
          @click="markAsPaid"
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
          <span v-else>{{ t("admin.affiliates.actions.mark_paid") }}</span>
        </button>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, ref } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";

const props = defineProps({
  payout: { type: Object, default: null },
});

const emit = defineEmits(["close", "marked"]);

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const method = ref("");
const transactionReference = ref("");
const notes = ref("");

const methodOptions = computed(() => [
  { id: "bank_card", name: t("admin.affiliates.actions.methods.bank_card") },
  { id: "liqpay", name: t("admin.affiliates.actions.methods.liqpay") },
  { id: "paypal", name: t("admin.affiliates.actions.methods.paypal") },
  { id: "crypto", name: t("admin.affiliates.actions.methods.crypto") },
]);

const markAsPaid = async () => {
  if (!method.value || !transactionReference.value) {
    toast.error(t("admin.affiliates.messages.fill_required"));
    return;
  }

  loading.value = true;
  try {
    await api.post(`/admin/affiliates/payouts/${props.payout.id}/mark-paid`, {
      method: method.value,
      transaction_reference: transactionReference.value,
      notes: notes.value,
    });
    toast.success(t("admin.affiliates.messages.payout_completed"));
    emit("marked");
  } catch (err) {
    toast.error(t("admin.affiliates.messages.payout_complete_error"));
  } finally {
    loading.value = false;
  }
};
</script>
