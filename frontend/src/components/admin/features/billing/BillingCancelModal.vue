<template>
  <AppModal
    :model-value="isOpen"
    :title="t('admin.billing.subscriptions.cancelModal.title')"
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="px-2 py-2 text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4"
      >
        <ExclamationTriangleIcon
          class="h-10 w-10 text-red-600 dark:text-red-500"
        />
      </div>

      <div class="space-y-6 text-left">
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
          {{ t("admin.billing.subscriptions.cancelModal.confirmText") }}
          <span class="font-bold text-gray-900 dark:text-white">"{{ subscription?.user?.name }}"</span>?
        </p>

        <AppTextarea
          v-model="reason"
          :label="t('admin.billing.subscriptions.cancelModal.reasonLabel')"
          :placeholder="
            t('admin.billing.subscriptions.cancelModal.reasonPlaceholder')
          "
          rows="3"
        />

        <div
          class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30"
        >
          <p
            class="text-[11px] font-bold text-amber-800 dark:text-amber-500 uppercase tracking-wider mb-1.5"
          >
            {{ t("admin.videos.delete.clientVideoWarning") }}
          </p>
          <p
            class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-semibold"
          >
            {{ t("admin.billing.subscriptions.cancelModal.warning") }}
          </p>
          <p
            class="text-xs text-amber-600 dark:text-amber-500 leading-relaxed mt-2 italic"
          >
            {{ t("admin.billing.subscriptions.cancelModal.impact") }}
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
          {{ t("admin.billing.subscriptions.cancelModal.confirm") }}
        </AppButton>
        <AppButton
          variant="secondary"
          size="lg"
          class="w-full sm:w-auto"
          @click="$emit('close')"
        >
          {{ t("admin.billing.subscriptions.cancelModal.cancel") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { ExclamationTriangleIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  isOpen: Boolean,
  subscription: Object,
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
  emit("confirm", { id: props.subscription?.id, reason: reason.value });
};
</script>
