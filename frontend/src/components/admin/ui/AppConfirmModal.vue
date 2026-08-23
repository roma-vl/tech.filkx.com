<template>
  <AppModal
    :model-value="modelValue"
    :title="title"
    max-width="sm"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="p-2 text-center">
      <!-- Icon -->
      <div
        v-if="icon === 'danger'"
        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4 animate-bounce"
      >
        <svg
          class="h-7 w-7 text-red-600 dark:text-red-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>
      <div
        v-else
        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 mb-4"
      >
        <svg
          class="h-7 w-7 text-amber-600 dark:text-amber-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
      </div>

      <!-- Content -->
      <div class="space-y-2">
        <p
          class="text-sm text-gray-650 dark:text-gray-300 font-medium leading-relaxed"
        >
          {{ message }}
        </p>
        <p
          v-if="irreversible"
          class="text-[11px] font-black text-red-650 dark:text-red-500 uppercase tracking-wide"
        >
          {{ t("admin.common.confirmModal.irreversible") }}
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full sm:w-auto">
        <AppButton
          variant="secondary"
          class="w-full sm:w-auto"
          @click="onCancel"
        >
          {{ cancelText }}
        </AppButton>
        <AppButton
          :variant="confirmVariant"
          class="w-full sm:w-auto"
          :loading="loading"
          @click="onConfirm"
        >
          {{ confirmText }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "./AppModal.vue";
import AppButton from "./AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: null,
  },
  message: {
    type: String,
    default: null,
  },
  confirmText: {
    type: String,
    default: null,
  },
  cancelText: {
    type: String,
    default: null,
  },
  confirmVariant: {
    type: String,
    default: "danger",
  },
  icon: {
    type: String,
    default: "danger", // 'danger' | 'warning'
  },
  irreversible: {
    type: Boolean,
    default: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const title = computed(
  () => props.title ?? t("admin.common.confirmModal.title"),
);
const message = computed(
  () => props.message ?? t("admin.common.confirmModal.message"),
);
const confirmText = computed(
  () => props.confirmText ?? t("admin.common.confirmModal.confirm"),
);
const cancelText = computed(
  () => props.cancelText ?? t("admin.common.confirmModal.cancel"),
);

const emit = defineEmits(["update:modelValue", "confirm", "cancel"]);

const onCancel = () => {
  emit("update:modelValue", false);
  emit("cancel");
};

const onConfirm = () => {
  emit("confirm");
};
</script>
