<template>
  <AppModal
    :model-value="true"
    :title="t('media.editModal.title')"
    @update:model-value="close"
    @close="close"
  >
    <div class="space-y-6">
      <AppInput
        v-model="form.title"
        :label="t('media.editModal.titleLabel')"
        type="text"
      />
    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 w-full">
        <AppButton
          variant="white"
          @click="close"
        >
          {{ t("media.editModal.cancelButton") }}
        </AppButton>
        <AppButton
          variant="primary"
          @click="submit"
        >
          {{ t("media.editModal.saveButton") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api.js";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  video: { type: Object, required: true },
});
const emit = defineEmits(["close", "updated"]);

const form = ref({
  title: props.video.title,
});

const close = () => emit("close");

const submit = async () => {
  try {
    const payload = {
      title: form.value.title?.trim(),
    };

    const response = await api.put(`/videos/${props.video.id}`, payload);

    emit("updated", response.data.data);
    emit("close");
  } catch (error) {
    console.error("Failed to update video", error);
  }
};
</script>
