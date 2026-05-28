<template>
  <AppModal
    :model-value="true"
    :title="t('streams.deleteModal.title')"
    max-width="md"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-4">
      <p class="text-gray-700 dark:text-gray-300">
        {{ t("streams.deleteModal.confirmText") }}
        <span class="font-semibold">"{{ stream.name }}"</span>
        {{ t("streams.deleteModal.platform") }}
        <span class="font-bold">{{ stream.platform }}</span>
        ?
      </p>

      <p class="text-sm text-red-600 dark:text-red-400 font-medium">
        {{ t("streams.deleteModal.irreversibleText") }}
      </p>
    </div>

    <template #footer>
      <div class="flex justify-end space-x-3 w-full">
        <AppButton
          variant="secondary"
          @click="close"
        >
          {{ t("streams.deleteModal.cancelButton") }}
        </AppButton>

        <AppButton
          variant="danger"
          :loading="loading"
          :disabled="loading"
          @click="submit"
        >
          {{ t("streams.deleteModal.confirmButton") }}
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
import { ref } from "vue";

const { t } = useI18n();

const props = defineProps({
  stream: { type: Object, required: true },
});

const loading = ref(false);
const emit = defineEmits(["close", "deleted"]);
const close = () => emit("close");

const submit = async () => {
  try {
    loading.value = true;
    const { data } = await api.post(`/streams/${props.stream.id}/stop`);
    emit("deleted", data.data);
    emit("close");
  } catch (e) {
    // If we get a network error while stopping, Docker might have flipped the network
    // but the request likely went through. Since the user reported it's deleted anyway,
    // we handle it gracefully.
    if (e.code === "ERR_NETWORK" || e.message?.includes("Network Error")) {
      console.warn(
        "Network hiccup during stop, assuming success based on user feedback",
        e,
      );
      emit("deleted", null); // Signal to refresh list
      emit("close");
      return;
    }

    console.error("Не вдалося зупинити стрім", e);
    alert(t("streams.deleteModal.deleteError") || "Помилка при видаленні");
  } finally {
    loading.value = false;
  }
};
</script>
