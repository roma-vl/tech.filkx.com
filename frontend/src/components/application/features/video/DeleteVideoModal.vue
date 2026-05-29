<template>
  <AppModal
    :model-value="true"
    :title="t('media.deleteModal.title')"
    @update:model-value="close"
  >
    <div class="space-y-4">
      <p class="text-gray-700 dark:text-gray-300">
        {{ t("media.deleteModal.confirmText") }}
        <span class="font-semibold">"{{ video.title }}"</span>?
      </p>

      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ t("media.deleteModal.warningText") }}
      </p>

      <p class="text-sm text-red-600 dark:text-red-400 font-medium">
        {{ t("media.deleteModal.irreversibleText") }}
      </p>
    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 w-full">
        <AppButton
          variant="secondary"
          @click="close"
        >
          {{ t("media.deleteModal.cancelButton") }}
        </AppButton>

        <AppButton
          variant="primary"
          class="!bg-red-600 hover:!bg-red-700 !border-red-600 text-white"
          @click="submit"
        >
          {{ t("media.deleteModal.confirmButton") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import api from "@/services/api.js";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  video: { type: Object, required: true },
});

const emit = defineEmits(["close", "deleted"]);

const close = () => emit("close");

const submit = async () => {
  await api.delete(`/videos/${props.video.id}`);
  emit("deleted", props.video.id);
  emit("close");
};
</script>
