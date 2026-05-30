<template>
  <AppModal
    :model-value="isOpen"
    :title="t('admin.users.suspendModal.title')"
    @update:model-value="(val) => !val && closeModal()"
  >
    <div class="p-6 text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 mb-4"
      >
        <NoSymbolIcon
          class="h-10 w-10 text-amber-600 dark:text-amber-500"
          aria-hidden="true"
        />
      </div>

      <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{
            isSuspended
              ? t("admin.users.suspendModal.confirmUnsuspend")
              : t("admin.users.suspendModal.confirmSuspend")
          }}
          <span class="font-bold text-gray-900 dark:text-white">"{{ client?.name }}"</span>?
        </p>

        <div
          class="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700 text-left"
        >
          <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
            {{
              isSuspended
                ? t("admin.users.suspendModal.unsuspendDescription")
                : t("admin.users.suspendModal.suspendDescription")
            }}
          </p>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton
          variant="secondary"
          @click="closeModal"
        >
          {{ t("admin.users.suspendModal.cancel") }}
        </AppButton>
        <AppButton
          :variant="isSuspended ? 'primary' : 'primary'"
          class="!text-sm"
          :class="
            isSuspended
              ? '!bg-green-600 hover:!bg-green-500 !shadow-green-500/25'
              : '!bg-amber-600 hover:!bg-amber-500 !shadow-amber-500/25'
          "
          :loading="loading"
          @click="submit"
        >
          {{
            isSuspended
              ? t("admin.users.suspendModal.confirmUnsuspendAction")
              : t("admin.users.suspendModal.confirmSuspendAction")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from "vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { NoSymbolIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const props = defineProps({
  isOpen: Boolean,
  client: Object,
  loading: Boolean,
});

const emit = defineEmits(["close", "confirm"]);
const { t } = useI18n();

const isSuspended = computed(() => props.client?.status === "suspended");

const closeModal = () => {
  emit("close");
};

const submit = () => {
  emit("confirm", props.client?.id);
};
</script>
