<template>
  <AppModal
    :model-value="isOpen"
    :title="t('admin.billing.pending.approveModal.title')"
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="px-2 py-2 text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 mb-4"
      >
        <CheckBadgeIcon class="h-10 w-10 text-green-600 dark:text-green-500" />
      </div>

      <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("admin.billing.pending.approveModal.confirmText") }}
          <span class="font-bold text-gray-900 dark:text-white">{{ payment?.amount }} {{ payment?.currency }}</span>
          {{ t("admin.billing.pending.table.user").toLowerCase() }}
          <span class="font-bold text-gray-900 dark:text-white">"{{ payment?.user?.name }}"</span>?
        </p>

        <div
          class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-100 dark:border-green-900/30 text-left"
        >
          <p
            class="text-xs text-green-700 dark:text-green-400 leading-relaxed font-medium"
          >
            {{ t("admin.billing.pending.approveModal.impact") }}
          </p>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex flex-col sm:flex-row-reverse gap-3 w-full">
        <AppButton
          variant="primary"
          size="lg"
          class="w-full sm:w-auto !bg-green-600 hover:!bg-green-500 !shadow-green-500/25"
          :loading="loading"
          @click="$emit('confirm', payment?.id)"
        >
          {{ t("admin.billing.pending.approveModal.confirm") }}
        </AppButton>
        <AppButton
          variant="secondary"
          size="lg"
          class="w-full sm:w-auto"
          @click="$emit('close')"
        >
          {{ t("admin.billing.pending.approveModal.cancel") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {useI18n} from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {CheckBadgeIcon} from "@heroicons/vue/24/outline";

defineProps({
  isOpen: Boolean,
  payment: Object,
  loading: Boolean,
});

const emit = defineEmits(["close", "confirm"]);
const { t } = useI18n();
</script>
