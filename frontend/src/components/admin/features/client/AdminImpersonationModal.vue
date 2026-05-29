<template>
  <AppModal
    v-model="show"
    max-width="md"
  >
    <div class="p-6">
      <div class="flex items-center gap-4 mb-6">
        <div
          class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600"
        >
          <IdentificationIcon class="w-6 h-6" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ $t("admin.impersonation.title") }}
          </h3>
          <p class="text-sm text-gray-500">
            {{ $t("admin.impersonation.confirmText", { name: client?.name }) }}
          </p>
        </div>
      </div>

      <div
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-4 mb-6"
      >
        <div class="flex gap-3">
          <ExclamationTriangleIcon class="w-5 h-5 text-amber-600 shrink-0" />
          <p class="text-xs text-amber-800 dark:text-amber-400 leading-relaxed">
            {{ $t("admin.impersonation.warning") }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <AppButton
          variant="secondary"
          class="flex-1"
          @click="$emit('close')"
        >
          {{ $t("admin.impersonation.cancel", { defaultValue: "Cancel" }) }}
        </AppButton>
        <AppButton
          variant="primary"
          class="flex-1 !bg-amber-600 hover:!bg-amber-700 !shadow-amber-600/20"
          :loading="loading"
          @click="$emit('confirm')"
        >
          {{ $t("admin.impersonation.confirmButton") }}
        </AppButton>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { computed } from "vue";
import {
  IdentificationIcon,
  ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  client: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "confirm"]);

const show = computed({
  get: () => !!props.client,
  set: (val) => {
    if (!val) emit("close");
  },
});
</script>
