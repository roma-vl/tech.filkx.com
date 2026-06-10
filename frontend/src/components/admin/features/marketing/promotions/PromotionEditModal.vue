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
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl max-h-[90vh] flex flex-col"
            >
              <div class="px-6 py-6 overflow-y-auto flex-1">
                <div class="flex items-center justify-between mb-8">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400"
                    >
                      <MegaphoneIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-xl font-black text-gray-900 dark:text-white tracking-tight"
                      >
                        {{
                          isEditing
                            ? t("admin.marketing.promotions.edit")
                            : t("admin.marketing.promotions.new")
                        }}
                      </DialogTitle>
                      <p
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-0.5"
                      >
                        {{
                          isEditing
                            ? t("admin.marketing.promotions.edit_subtitle")
                            : t("admin.marketing.promotions.new_subtitle")
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
                  <div class="space-y-6">
                    <h4
                      class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2"
                    >
                      <span class="w-1 h-4 bg-primary-500 rounded-full" />
                      Basic Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.name") }}
                        </label>
                        <input
                          v-model="form.name"
                          type="text"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                          placeholder="Summer Sale 2025"
                          required
                        >
                      </div>
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.slug") }}
                        </label>
                        <input
                          v-model="form.slug"
                          type="text"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                          placeholder="summer-2025"
                        >
                      </div>
                    </div>

                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        {{ t("admin.marketing.promotions.description") }}
                      </label>
                      <textarea
                        v-model="form.description"
                        rows="2"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                        placeholder="Special discount for all new subscriptions..."
                      />
                    </div>
                  </div>

                  <div class="space-y-6">
                    <h4
                      class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2"
                    >
                      <span class="w-1 h-4 bg-primary-500 rounded-full" />
                      Discount Settings
                    </h4>

                    <div class="grid grid-cols-2 gap-6">
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.type") }}
                        </label>
                        <select
                          v-model="form.type"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                        >
                          <option value="percent">
                            {{ t("admin.marketing.promotions.types.percent") }}
                          </option>
                          <option value="fixed">
                            {{ t("admin.marketing.promotions.types.fixed") }}
                          </option>
                        </select>
                      </div>
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.amount") }}
                          {{ form.type === "percent" ? "(%)" : "($)" }}
                        </label>
                        <input
                          v-model.number="form.amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                          required
                        >
                      </div>
                    </div>

                    <div class="group/input">
                      <label
                        class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                      >
                        Minimum Purchase Amount ($)
                        <span class="text-[9px] text-gray-400 normal-case">(Optional - leave empty for no minimum)</span>
                      </label>
                      <input
                        v-model.number="form.minimumAmount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                        placeholder="0.00"
                      >
                    </div>
                  </div>

                  <div class="space-y-6">
                    <h4
                      class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2"
                    >
                      <span class="w-1 h-4 bg-primary-500 rounded-full" />
                      Schedule
                    </h4>

                    <div class="grid grid-cols-2 gap-6">
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.starts_at") }}
                          <span class="text-[9px] text-gray-400 normal-case">(Optional)</span>
                        </label>
                        <input
                          v-model="form.startsAt"
                          type="datetime-local"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                        >
                      </div>
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          {{ t("admin.marketing.promotions.ends_at") }}
                          <span class="text-[9px] text-gray-400 normal-case">(Optional)</span>
                        </label>
                        <input
                          v-model="form.endsAt"
                          type="datetime-local"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                        >
                      </div>
                    </div>
                  </div>

                  <div class="space-y-6">
                    <h4
                      class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2"
                    >
                      <span class="w-1 h-4 bg-primary-500 rounded-full" />
                      Application Settings
                    </h4>

                    <div
                      class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-gray-700 space-y-6"
                    >
                      <div class="group/input">
                        <label
                          class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                        >
                          Application Type
                        </label>
                        <select
                          v-model="form.applicationType"
                          class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all cursor-pointer"
                        >
                          <option value="code">
                            Code-based (requires promo code)
                          </option>
                          <option value="auto">
                            Auto-apply (applies automatically)
                          </option>
                          <option value="url">
                            URL-based (via special link)
                          </option>
                        </select>
                      </div>

                      <div
                        class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700/50"
                      >
                        <div>
                          <span
                            class="text-sm font-black text-gray-900 dark:text-white block"
                          >
                            {{ t("admin.marketing.promotions.sitewide") }}
                          </span>
                          <span
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                          >
                            Legacy: Auto-apply to all plans
                          </span>
                        </div>
                        <label
                          class="relative inline-flex items-center cursor-pointer"
                        >
                          <input
                            v-model="form.isSitewide"
                            type="checkbox"
                            class="sr-only peer"
                            @change="handleSitewideChange"
                          >
                          <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600 shadow-sm"
                          />
                        </label>
                      </div>

                      <div
                        class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700/50"
                      >
                        <div class="group/input">
                          <label
                            class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                          >
                            Global Usage Limit
                            <span class="text-[9px] text-gray-400 normal-case">(Optional)</span>
                          </label>
                          <input
                            v-model.number="form.usageLimit"
                            type="number"
                            min="0"
                            class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                            placeholder="Unlimited"
                          >
                        </div>
                        <div class="group/input">
                          <label
                            class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                          >
                            Per-User Limit
                            <span class="text-[9px] text-gray-400 normal-case">(Optional)</span>
                          </label>
                          <input
                            v-model.number="form.usageLimitPerUser"
                            type="number"
                            min="0"
                            class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                            placeholder="Unlimited"
                          >
                        </div>
                      </div>

                      <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform -translate-y-2 opacity-0"
                        enter-to-class="transform translate-y-0 opacity-100"
                      >
                        <div
                          v-if="
                            form.isSitewide || form.applicationType === 'auto'
                          "
                          class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50"
                        >
                          <div class="group/input">
                            <label
                              class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                            >
                              {{ t("admin.marketing.promotions.banner_text") }}
                            </label>
                            <input
                              v-model="form.bannerText"
                              type="text"
                              class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                              placeholder="Flash Sale! Click here to get 30% OFF!"
                            >
                          </div>
                          <div class="group/input">
                            <label
                              class="block text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 group-focus-within/input:text-primary-500 transition-colors"
                            >
                              Banner Color
                            </label>
                            <input
                              v-model="form.bannerColor"
                              type="text"
                              class="w-full px-4 py-3 text-sm font-black rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all"
                              placeholder="primary, red, green, or #FF0000"
                            >
                          </div>
                        </div>
                      </Transition>
                    </div>
                  </div>
                  <div
                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700"
                  >
                    <div>
                      <span
                        class="text-sm font-black text-gray-900 dark:text-white block"
                      >{{ t("admin.marketing.promotions.active") }}</span>
                      <span
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                      >{{ t("admin.marketing.promotions.active_hint") }}</span>
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
                  <RocketLaunchIcon
                    v-else
                    class="w-4 h-4"
                  />
                  {{
                    isEditing
                      ? t("admin.marketing.promotions.save")
                      : t("admin.marketing.promotions.create")
                  }}
                </button>
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white dark:bg-gray-700 px-8 py-3.5 text-xs font-black text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all sm:w-auto uppercase tracking-widest"
                  @click="closeModal"
                >
                  {{ t("admin.marketing.promotions.cancel") }}
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
  MegaphoneIcon,
  XMarkIcon,
  ExclamationCircleIcon,
  RocketLaunchIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";

const props = defineProps({
  isOpen: Boolean,
  promotion: Object,
});

const emit = defineEmits(["close", "saved"]);
const { t } = useI18n();

const isEditing = computed(() => !!props.promotion);

const form = ref({
  name: "",
  slug: "",
  description: "",
  type: "percent",
  amount: 0,
  startsAt: null,
  endsAt: null,
  isActive: true,
  isSitewide: false,
  bannerText: "",
  bannerColor: "",
  applicationType: "code",
  usageLimit: null,
  usageLimitPerUser: null,
  minimumAmount: null,
  applicablePlanIds: [],
});

const loading = ref(false);
const error = ref("");

const formatDateForInput = (dateString) => {
  if (!dateString) return null;
  try {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  } catch (e) {
    return null;
  }
};

watch(
  () => props.promotion,
  (newVal) => {
    if (newVal) {
      form.value = {
        name: newVal.name || "",
        slug: newVal.slug || "",
        description: newVal.description || "",
        type: newVal.type || "percent",
        amount: newVal.amount || 0,
        startsAt: formatDateForInput(newVal.startsAt),
        endsAt: formatDateForInput(newVal.endsAt),
        isActive: newVal.isActive ?? true,
        isSitewide: newVal.isSitewide ?? false,
        bannerText: newVal.bannerText || "",
        bannerColor: newVal.bannerColor || "",
        applicationType:
          newVal.applicationType || (newVal.isSitewide ? "auto" : "code"),
        usageLimit: newVal.usageLimit ?? null,
        usageLimitPerUser: newVal.usageLimitPerUser ?? null,
        minimumAmount: newVal.minimumAmount ?? null,
        applicablePlanIds: newVal.applicablePlanIds || [],
      };
    } else {
      form.value = {
        name: "",
        slug: "",
        description: "",
        type: "percent",
        amount: 0,
        startsAt: null,
        endsAt: null,
        isActive: true,
        isSitewide: false,
        bannerText: "",
        bannerColor: "",
        applicationType: "code",
        usageLimit: null,
        usageLimitPerUser: null,
        minimumAmount: null,
        applicablePlanIds: [],
      };
    }
  },
  { immediate: true },
);

const handleSitewideChange = () => {
  if (form.value.isSitewide && form.value.applicationType === "code") {
    form.value.applicationType = "auto";
  }
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

    if (payload.startsAt === "") payload.startsAt = null;
    if (payload.endsAt === "") payload.endsAt = null;
    if (payload.usageLimit === "") payload.usageLimit = null;
    if (payload.usageLimitPerUser === "") payload.usageLimitPerUser = null;
    if (payload.minimumAmount === "") payload.minimumAmount = null;
    if (payload.bannerText === "") payload.bannerText = null;
    if (payload.bannerColor === "") payload.bannerColor = null;
    if (payload.applicationType === "") payload.applicationType = null;

    if (isEditing.value) {
      await api.put(
        `/admin/marketing/promotions/${props.promotion.id}`,
        payload,
      );
    } else {
      await api.post("/admin/marketing/promotions", payload);
    }
    emit("saved");
    closeModal();
  } catch (e) {
    console.error(e);
    const errorMessage =
      e.response?.data?.message || e.response?.data?.errors
        ? Object.values(e.response.data.errors).flat().join(", ")
        : "Failed to save promotion";
    error.value = errorMessage;
  } finally {
    loading.value = false;
  }
};
</script>
