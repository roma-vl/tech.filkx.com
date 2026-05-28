<template>
  <AppModal
    :model-value="isOpen"
    :title="t('admin.users.deleteModal.title')"
    @update:model-value="(val) => !val && closeModal()"
  >
    <div class="p-6 text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4"
      >
        <ExclamationTriangleIcon
          class="h-10 w-10 text-red-600 dark:text-red-500"
          aria-hidden="true"
        />
      </div>

      <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("admin.users.deleteModal.confirmText") }}
          <span class="font-bold text-gray-900 dark:text-white">"{{ client?.name }}"</span>?
        </p>

        <div
          class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30 text-left"
        >
          <p
            class="text-[11px] font-black text-amber-800 dark:text-amber-500 uppercase tracking-wider mb-1.5"
          >
            {{ t("admin.videos.delete.clientVideoWarning") }}
          </p>
          <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
            {{ t("admin.users.deleteModal.warning") }}
          </p>
        </div>

        <p
          class="text-[11px] font-black text-red-600 dark:text-red-500 uppercase tracking-wide"
        >
          {{ t("admin.users.deleteModal.irreversibleNote") }}
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton
          variant="secondary"
          @click="closeModal"
        >
          {{ t("admin.users.deleteModal.cancel") }}
        </AppButton>
        <AppButton
          variant="danger"
          :loading="loading"
          @click="submit"
        >
          {{ t("admin.users.deleteModal.confirm") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {ExclamationTriangleIcon} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";

defineProps({
  isOpen: Boolean,
  client: Object,
  loading: Boolean,
});

const emit = defineEmits(["close", "confirm"]);
const { t } = useI18n();

const closeModal = () => {
  emit("close");
};

const submit = () => {
  emit("confirm");
};
</script>
