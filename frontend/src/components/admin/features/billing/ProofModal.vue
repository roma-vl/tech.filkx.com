<template>
  <AppModal
    :model-value="true"
    :title="`${t('admin.billing.proof.title')} #${payment.id}`"
    max-width="3xl"
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="px-2 py-2 space-y-6">
      <div
        class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 space-y-2"
      >
        <div
          class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2"
        >
          <span class="font-bold text-gray-900 dark:text-gray-200">{{ t("admin.billing.proof.user") }}:</span>
          <div class="text-right">
            <p class="font-bold text-gray-900 dark:text-gray-100">
              {{ payment.user?.name }}
            </p>
            <p class="text-xs opacity-70">
              {{ payment.user?.email }}
            </p>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <span class="font-bold text-gray-900 dark:text-gray-200">{{ t("admin.billing.proof.amount") }}:</span>
          <span class="font-black text-primary-600 dark:text-primary-400">${{ payment.amount }} {{ payment.currency }}</span>
        </div>

        <div class="flex justify-between items-center">
          <span class="font-bold text-gray-900 dark:text-gray-200">{{ t("admin.billing.proof.plan") }}:</span>
          <span class="font-medium text-gray-800 dark:text-gray-300">{{
              payment.subscription?.name ||
              payment.subscription?.plan?.name ||
              "N/A"
            }}</span>
        </div>

        <div class="flex justify-between items-center">
          <span class="font-bold text-gray-900 dark:text-gray-200">{{ t("admin.billing.proof.created") }}:</span>
          <span class="text-xs text-gray-500">{{
              formatDate(payment.createdAt)
            }}</span>
        </div>
      </div>

      <div
        v-if="proof"
        class="border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden p-2 bg-gray-50 dark:bg-gray-900"
      >
        <img
          v-if="isImage"
          :src="proofUrl"
          class="max-h-[600px] w-full object-contain rounded-xl shadow-lg"
          alt="Payment Proof"
        >

        <iframe
          v-else-if="isPdf"
          :src="proofUrl"
          class="w-full h-[600px] border-none rounded-xl"
        />

        <div
          v-else
          class="py-12 text-center"
        >
          <AppButton
            variant="primary"
            size="lg"
            :href="proofUrl"
            target="_blank"
          >
            {{ t("admin.billing.proof.download") }}
          </AppButton>
        </div>
      </div>

      <div
        v-if="payment.metadata?.proof_notes"
        class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30"
      >
        <p
          class="text-xs font-bold text-amber-800 dark:text-amber-500 uppercase tracking-wider mb-1"
        >
          {{ t("admin.billing.proof.notes") }}
        </p>
        <p
          class="text-sm text-amber-700 dark:text-amber-400 italic font-medium leading-relaxed"
        >
          "{{ payment.metadata.proof_notes }}"
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end w-full">
        <AppButton
          variant="secondary"
          size="lg"
          class="w-full sm:w-auto"
          @click="$emit('close')"
        >
          {{ t("admin.billing.proof.close") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  payment: {
    type: Object,
    required: true,
  },
});

const proof = computed(() => props.payment.paymentProof);

const isImage = computed(() => proof.value?.type === "image");
const isPdf = computed(() => proof.value?.type === "pdf");

const proofUrl = computed(() => proof.value?.url);

const formatDate = (date) => new Date(date).toLocaleString();
</script>
