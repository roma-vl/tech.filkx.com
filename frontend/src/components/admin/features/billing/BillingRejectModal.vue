<template>
  <AppModal
    :model-value="isOpen"
    :title="t('admin.billing.pending.rejectModal.title')"
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="px-2 py-2 text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4"
      >
        <XCircleIcon class="h-10 w-10 text-red-600 dark:text-red-500" />
      </div>

      <div class="space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("admin.billing.pending.rejectModal.confirmText") }}
          <span class="font-bold text-gray-900 dark:text-white">"{{ payment?.user?.name }}"</span>?
        </p>

        <div class="text-left">
          <AppTextarea
            v-model="reason"
            :label="t('admin.billing.pending.rejectModal.reasonLabel')"
            :placeholder="
              t('admin.billing.pending.rejectModal.reasonPlaceholder')
            "
            rows="3"
          />
        </div>

        <div
          class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30 text-left"
        >
          <p
            class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium"
          >
            {{ t("admin.billing.pending.rejectModal.impact") }}
          </p>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex flex-col sm:flex-row-reverse gap-3 w-full">
        <AppButton
          variant="danger"
          size="lg"
          class="w-full sm:w-auto"
          :loading="loading"
          :disabled="!reason.trim()"
          @click="handleSubmit"
        >
          {{ t("admin.billing.pending.rejectModal.confirm") }}
        </AppButton>
        <AppButton
          variant="secondary"
          size="lg"
          class="w-full sm:w-auto"
          @click="$emit('close')"
        >
          {{ t("admin.billing.pending.rejectModal.cancel") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import { XCircleIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  isOpen: Boolean,
  payment: Object,
  loading: Boolean,
});

const emit = defineEmits(["close", "confirm"]);
const { t } = useI18n();

const reason = ref("");

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) reason.value = "";
  },
);

const handleSubmit = () => {
  emit("confirm", { id: props.payment?.id, reason: reason.value });
};
</script>
