<template>
  <AppModal
    v-model="isOpen"
    :title="t('subscription.cancel_order_modal_title')"
    max-width="md"
  >
    <div class="space-y-4">
      <div
        class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full"
      >
        <svg
          class="w-6 h-6 text-red-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 15c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("subscription.cancel_order_modal_desc") }}
        </p>
      </div>

      <div
        v-if="orderDescription"
        class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm"
      >
        <p class="font-medium text-gray-700 dark:text-gray-300">
          {{ t("subscription.description") }}:
        </p>
        <p class="text-gray-600 dark:text-gray-400">
          {{ orderDescription }}
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex flex-col sm:flex-row-reverse gap-2 w-full">
        <AppButton
          variant="danger"
          :loading="loading"
          class="sm:ml-2"
          @click="$emit('confirm')"
        >
          {{ t("subscription.cancel_order_confirm_btn") }}
        </AppButton>
        <AppButton
          variant="secondary"
          :disabled="loading"
          @click="isOpen = false"
        >
          {{ t("subscription.cancel_order_cancel_btn") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const props = defineProps({
  modelValue: Boolean,
  loading: Boolean,
  orderDescription: String,
});

const emit = defineEmits(["update:modelValue", "confirm"]);

const { t } = useI18n();

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});
</script>
