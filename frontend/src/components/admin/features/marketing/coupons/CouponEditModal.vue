<template>
  <AppModal
    :model-value="isOpen"
    :title="isEditing ? t('admin.marketing.coupons.edit') : t('admin.marketing.coupons.new')"
    max-width="lg"
    @update:model-value="closeModal"
  >
    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <div>
        <label class="block text-sm font-medium text-gray-705 dark:text-gray-300 mb-1">
          {{ t("admin.marketing.coupons.code") }}
        </label>
        <div class="flex gap-2">
          <input
            v-model="form.code"
            type="text"
            class="flex-1 px-4 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-655 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-[#00a046] focus:ring-1 focus:ring-[#00a046] focus:outline-none outline-none transition-all shadow-sm"
            placeholder="SUMMER2026"
            required
          >
          <AppButton
            type="button"
            class="!px-5 rounded-lg !bg-[#00a046] hover:!bg-[#00b050] text-white font-semibold text-xs border-none shadow-sm transition-all"
            @click="generateCode"
          >
            {{ t("admin.marketing.coupons.generate") }}
          </AppButton>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <AppSelect
          v-model="form.type"
          :label="t('admin.marketing.coupons.type')"
          :options="[
            { id: 'percent', name: t('admin.marketing.coupons.types.percent') },
            { id: 'fixed', name: t('admin.marketing.coupons.types.fixed') }
          ]"
          option-value="id"
          option-label="name"
        />
        <AppInput
          v-model.number="form.amount"
          type="number"
          step="0.01"
          min="0"
          :label="t('admin.marketing.coupons.amount') + (form.type === 'percent' ? ' (%)' : ' (₴)')"
          required
        />
      </div>

      <div class="grid grid-cols-2 gap-6">
        <AppInput
          v-model.number="form.usageLimit"
          type="number"
          min="1"
          :label="t('admin.marketing.coupons.usage_limit') + ' (Опціонально)'"
          placeholder="Необмежено"
        />
        <AppInput
          v-model="form.expiresAt"
          type="date"
          :label="t('admin.marketing.coupons.expiry') + ' (Опціонально)'"
        />
      </div>

      <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-900/50 rounded-xl border border-gray-205 dark:border-zinc-800">
        <div>
          <span class="text-sm font-semibold text-gray-905 dark:text-white block">
            {{ t("admin.marketing.coupons.active") }}
          </span>
          <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            {{ t("admin.marketing.coupons.active_hint") }}
          </span>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            v-model="form.isActive"
            type="checkbox"
            class="sr-only peer"
          >
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#00a046] shadow-sm" />
        </label>
      </div>

      <transition name="expand">
        <div
          v-if="error"
          class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 flex items-center gap-3 text-red-650 dark:text-red-400"
        >
          <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" />
          <p class="text-xs font-bold">
            {{ error }}
          </p>
        </div>
      </transition>
    </form>

    <template #footer>
      <div class="flex items-center justify-end gap-3 w-full">
        <AppButton
          variant="white"
          class="text-sm"
          type="button"
          @click="closeModal"
        >
          {{ t("admin.marketing.coupons.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="loading"
          class="text-sm !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
          type="button"
          @click="submit"
        >
          <CheckBadgeIcon class="w-4 h-4 mr-1.5 inline" />
          {{ isEditing ? t("admin.marketing.coupons.save") : t("admin.marketing.coupons.create") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import {
  ExclamationCircleIcon,
  CheckBadgeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  isOpen: Boolean,
  coupon: {
    type: Object,
    default: null
  },
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

    if (isEditing.value && props.coupon) {
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
