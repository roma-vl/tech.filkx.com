<template>
  <TransitionRoot
    as="template"
    :show="isOpen"
  >
    <Dialog
      as="div"
      class="relative z-50"
      @close="$emit('close')"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md"
            >
              <div class="px-8 py-8">
                <div class="flex items-center gap-4 mb-6">
                  <div
                    class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600"
                  >
                    <PaperAirplaneIcon class="w-7 h-7" />
                  </div>
                  <DialogTitle
                    as="h3"
                    class="text-2xl font-black text-gray-900 dark:text-white tracking-tight"
                  >
                    {{ t("admin.emails.confirm_dispatch") }}
                  </DialogTitle>
                </div>

                <p
                  class="text-sm font-bold text-gray-500 dark:text-gray-400 leading-relaxed"
                >
                  {{
                    t("admin.emails.confirm_message", {
                      name: campaign?.name,
                      audience: formatAudience(targetAudience),
                    })
                  }}
                </p>
              </div>

              <div
                class="bg-gray-50 dark:bg-gray-800/50 px-8 py-5 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700"
              >
                <AppButton @click="$emit('confirm')">
                  <template #prefix>
                    <RocketLaunchIcon class="w-5 h-5" />
                  </template>
                  {{ t("admin.emails.confirm_send") }}
                </AppButton>
                <AppButton
                  variant="white"
                  @click="$emit('close')"
                >
                  {{ t("admin.emails.cancel") }}
                </AppButton>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { PaperAirplaneIcon, RocketLaunchIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/admin/ui/AppButton.vue";

const { t } = useI18n();

defineProps({
  isOpen: Boolean,
  campaign: Object,
  targetAudience: String,
});

defineEmits(["close", "confirm"]);

const formatAudience = (slug) => {
  const map = {
    all: t("admin.emails.audience.all"),
    trial_expired: t("admin.emails.audience.trial_expired"),
    new_users: t("admin.emails.audience.new_users"),
    active_subscribers: t("admin.emails.audience.active_subscribers"),
  };
  return map[slug] || slug;
};
</script>
