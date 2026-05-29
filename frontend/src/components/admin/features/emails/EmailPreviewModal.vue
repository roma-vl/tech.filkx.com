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
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl max-h-[90vh] flex flex-col"
            >
              <div
                class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600"
                  >
                    <EyeIcon class="w-6 h-6" />
                  </div>
                  <DialogTitle
                    as="h3"
                    class="text-xl font-black text-gray-900 dark:text-white tracking-tight"
                  >
                    {{
                      t("admin.emails.preview_title", { name: campaignName })
                    }}
                  </DialogTitle>
                </div>
                <AppButton
                  variant="ghost"
                  class="!p-3 !rounded-2xl !transition-colors"
                  @click="$emit('close')"
                >
                  <XMarkIcon class="w-6 h-6 text-gray-400" />
                </AppButton>
              </div>
              <div class="flex-1 overflow-auto bg-gray-50 dark:bg-gray-900/30">
                <iframe
                  v-if="previewHtml"
                  :srcdoc="previewHtml"
                  class="w-full h-full min-h-[600px] border-none"
                />
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
import { EyeIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  isOpen: Boolean,
  campaignName: String,
  previewHtml: String,
});

defineEmits(["close"]);
</script>
