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
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md"
            >
              <div class="px-6 py-6 text-center">
                <div
                  class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4"
                >
                  <TrashIcon
                    class="h-10 w-10 text-red-600 dark:text-red-500"
                    aria-hidden="true"
                  />
                </div>

                <DialogTitle
                  as="h3"
                  class="text-xl font-black leading-6 text-gray-900 dark:text-white mb-2 font-primary tracking-tight"
                >
                  {{ t("admin.marketing.coupons.alerts.delete_confirm_title") }}
                </DialogTitle>

                <div class="mt-4">
                  <p class="text-sm font-bold text-gray-500 dark:text-gray-400">
                    {{
                      t("admin.marketing.coupons.alerts.delete_confirm", {
                        code: coupon?.code,
                      })
                    }}
                  </p>
                  <p
                    class="mt-2 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"
                  >
                    {{ t("admin.marketing.coupons.alerts.delete_warning") }}
                  </p>
                </div>
              </div>

              <div
                class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700"
              >
                <button
                  type="button"
                  class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-500/25 hover:bg-red-500 transition-all sm:w-auto uppercase tracking-widest"
                  :disabled="loading"
                  @click="$emit('confirm', coupon)"
                >
                  <span
                    v-if="loading"
                    class="loading loading-spinner loading-xs"
                  />
                  {{ t("admin.marketing.coupons.actions.delete") }}
                </button>
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white dark:bg-gray-700 px-6 py-3 text-sm font-black text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all sm:w-auto uppercase tracking-widest"
                  @click="$emit('close')"
                >
                  {{ t("admin.marketing.coupons.cancel") }}
                </button>
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
import { TrashIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

defineProps({
  isOpen: Boolean,
  coupon: Object,
  loading: Boolean,
});

defineEmits(["close", "confirm"]);

const { t } = useI18n();
</script>
