<template>
  <AppModal
    :model-value="true"
    :title="t('media.deletePlaylistModal.title')"
    @update:model-value="close"
  >
    <div class="space-y-4">
      <p class="text-gray-700 dark:text-gray-300">
        {{ t("media.deletePlaylistModal.confirmText") }}
        <span class="font-semibold">"{{ playlist.name }}"</span>?
      </p>

      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ t("media.deletePlaylistModal.warningText") }}
      </p>

      <p class="text-sm text-red-600 dark:text-red-400 font-medium">
        {{ t("media.deletePlaylistModal.irreversibleText") }}
      </p>
    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 w-full">
        <AppButton
          variant="secondary"
          @click="close"
        >
          {{ t("media.deletePlaylistModal.cancelButton") }}
        </AppButton>

        <AppButton
          variant="primary"
          class="!bg-red-600 hover:!bg-red-700 !border-red-600 text-white"
          @click="submit"
        >
          {{ t("media.deletePlaylistModal.confirmButton") }}
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
  playlist: { type: Object, required: true },
});

const emit = defineEmits(["close", "deleted"]);

const close = () => emit("close");

const submit = async () => {
  await api.delete(`/playlists/${props.playlist.id}`);
  emit("deleted", props.playlist.id);
  emit("close");
};
</script>
