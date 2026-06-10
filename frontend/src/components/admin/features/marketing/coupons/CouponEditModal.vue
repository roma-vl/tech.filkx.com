<template>
  <TransitionRoot
    as="template"
    :show="isOpen"
  >
    <Dialog
      as="div"
      class="relative z-50"
      @close="closeModal"
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
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            >
              <div class="px-6 py-6">
                <div class="flex items-center justify-between mb-8">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400"
                    >
                      <TicketIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-xl font-black text-gray-900 dark:text-white tracking-tight"
                      >
                        {{
                          isEditing
                            ? t("admin.marketing.coupons.edit")
                            : t("admin.marketing.coupons.new")
                        }}
                      </DialogTitle>
                      <p
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5"
                      >
                        {{
                          isEditing
                            ? t("admin.marketing.coupons.edit_subtitle")
                            : t("admin.marketing.coupons.new_subtitle")
                        }}
                      </p>
                    </div>
                  </div>
                  <button
                    class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-colors"
                    @click="closeModal"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <form
                  class="space-y-6"
                  @submit.prevent="submit"
                >
                  <div class="group/input">
                    <label
                      class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                    >
                      {{ t("admin.marketing.coupons.code") }}
                    </label>
                    <div class="flex gap-2">
                      <input
                        v-model="form.code"
                        type="text"
                        class="flex-1 px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                        placeholder="SUMMER2025"
                        required
                      >
                      <button
                        type="button"
                        class="px-5 rounded-2xl bg-gray-900 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-black text-[11px] uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-gray-900/10"
                        @click="generateCode"
                      >
                        {{ t("admin.marketing.coupons.generate") }}
                      </button>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-6">
                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        {{ t("admin.marketing.coupons.type") }}
                      </label>
                      <select
                        v-model="form.type"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                      >
                        <option value="percent">
                          {{ t("admin.marketing.coupons.types.percent") }}
                        </option>
                        <option value="fixed">
                          {{ t("admin.marketing.coupons.types.fixed") }}
                        </option>
                      </select>
                    </div>
                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        {{ t("admin.marketing.coupons.amount") }}
                        {{ form.type === "percent" ? "(%)" : "($)" }}
                      </label>
                      <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                        required
                      >
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-6">
                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        {{ t("admin.marketing.coupons.usage_limit") }}
                      </label>
                      <input
                        v-model.number="form.usageLimit"
                        type="number"
                        min="1"
                        placeholder="Unlimited"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                      >
                    </div>
                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        {{ t("admin.marketing.coupons.expiry") }}
                      </label>
                      <input
                        v-model="form.expiresAt"
                        type="date"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                      >
                    </div>
                  </div>

                  <div
                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700"
                  >
                    <div>
                      <span
                        class="text-sm font-black text-gray-900 dark:text-white block"
                      >{{ t("admin.marketing.coupons.active") }}</span>
                      <span
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                      >{{ t("admin.marketing.coupons.active_hint") }}</span>
                    </div>
                    <label
                      class="relative inline-flex items-center cursor-pointer"
                    >
                      <input
                        v-model="form.isActive"
                        type="checkbox"
                        class="sr-only peer"
                      >
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600 shadow-sm"
                      />
                    </label>
                  </div>

                  <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform -translate-y-2 opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform translate-y-0 opacity-100"
                    leave-to-class="transform -translate-y-2 opacity-0"
                  >
                    <div
                      v-if="error"
                      class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 flex items-center gap-3 text-red-600 dark:text-red-400"
                    >
                      <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" />
                      <p class="text-xs font-bold">
                        {{ error }}
                      </p>
                    </div>
                  </Transition>
                </form>
              </div>

              <div
                class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700"
              >
                <button
                  type="button"
                  class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-gray-900 dark:bg-primary-600 px-8 py-3.5 text-xs font-black text-white shadow-xl shadow-primary-500/20 hover:bg-black dark:hover:bg-primary-500 transition-all sm:w-auto uppercase tracking-widest"
                  :disabled="loading"
                  @click="submit"
                >
                  <span
                    v-if="loading"
                    class="loading loading-spinner loading-xs"
                  />
                  <CheckBadgeIcon
                    v-else
                    class="w-4 h-4"
                  />
                  {{
                    isEditing
                      ? t("admin.marketing.coupons.save")
                      : t("admin.marketing.coupons.create")
                  }}
                </button>
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white dark:bg-gray-700 px-8 py-3.5 text-xs font-black text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all sm:w-auto uppercase tracking-widest"
                  @click="closeModal"
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
import { ref, watch, computed } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  TicketIcon,
  XMarkIcon,
  ExclamationCircleIcon,
  CheckBadgeIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";

const props = defineProps({
  isOpen: Boolean,
  coupon: Object,
});

const emit = defineEmits(["close", "saved"]);
const { t } = useI18n();

const isEditing = computed(() => !!props.coupon);

const form = ref({
  code: "",
  type: "percent",
  amount: 0,
  usageLimit: null,
  expiresAt: null,
  isActive: true,
});

const loading = ref(false);
const error = ref("");

watch(
  () => props.coupon,
  (newVal) => {
    if (newVal) {
      form.value = {
        code: newVal.code || "",
        type: newVal.type || "percent",
        amount: newVal.amount || 0,
        isActive: newVal.isActive ?? true,
        usageLimit: newVal.usageLimit ?? null,
        expiresAt: newVal.expiresAt
          ? new Date(newVal.expiresAt).toISOString().split("T")[0]
          : null,
      };
    } else {
      form.value = {
        code: "",
        type: "percent",
        amount: 0,
        usageLimit: null,
        expiresAt: null,
        isActive: true,
      };
    }
  },
  { immediate: true },
);

const generateCode = () => {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  let result = "";
  for (let i = 0; i < 8; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  form.value.code = result;
};

const closeModal = () => {
  emit("close");
  error.value = "";
};

const submit = async () => {
  loading.value = true;
  error.value = "";
  try {
    const payload = { ...form.value };

    if (payload.usageLimit === "") payload.usageLimit = null;
    if (payload.expiresAt === "") payload.expiresAt = null;

    if (isEditing.value) {
      await api.put(`/admin/marketing/coupons/${props.coupon.id}`, payload);
    } else {
      await api.post("/admin/marketing/coupons", payload);
    }
    emit("saved");
    closeModal();
  } catch (e) {
    console.error(e);
    error.value = e.response?.data?.message || "Failed to save coupon";
  } finally {
    loading.value = false;
  }
};
</script>
