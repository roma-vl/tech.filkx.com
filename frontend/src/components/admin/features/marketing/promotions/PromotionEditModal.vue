<template>
  <AppModal
    :model-value="isOpen"
    :title="isEditing ? t('admin.marketing.promotions.edit') : t('admin.marketing.promotions.new')"
    max-width="3xl"
    @update:model-value="closeModal"
  >
    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <div class="space-y-6">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
          <span class="w-1.5 h-4 bg-[#00a046] rounded-full" />
          Основна інформація
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <AppInput
            v-model="form.name"
            :label="t('admin.marketing.promotions.name')"
            placeholder="Summer Sale 2026"
            required
          />
          <AppInput
            v-model="form.slug"
            :label="t('admin.marketing.promotions.slug')"
            placeholder="summer-2026"
          />
        </div>

        <AppTextarea
          v-model="form.description"
          :label="t('admin.marketing.promotions.description')"
          placeholder="Опис акції..."
          rows="2"
        />
      </div>

      <div class="space-y-6">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
          <span class="w-1.5 h-4 bg-[#00a046] rounded-full" />
          Налаштування знижки
        </h4>

        <div class="grid grid-cols-2 gap-6">
          <AppSelect
            v-model="form.type"
            :label="t('admin.marketing.promotions.type')"
            :options="[
              { id: 'percent', name: t('admin.marketing.promotions.types.percent') },
              { id: 'fixed', name: t('admin.marketing.promotions.types.fixed') }
            ]"
            option-value="id"
            option-label="name"
          />
          <AppInput
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0"
            :label="t('admin.marketing.promotions.amount') + (form.type === 'percent' ? ' (%)' : ' (₴)')"
            required
          />
        </div>

        <AppInput
          v-model.number="form.minimumAmount"
          type="number"
          step="0.01"
          min="0"
          label="Мінімальна сума покупки (Опціонально)"
          placeholder="0.00"
        />
      </div>

      <div class="space-y-6">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
          <span class="w-1.5 h-4 bg-[#00a046] rounded-full" />
          Розклад акції
        </h4>

        <div class="grid grid-cols-2 gap-6">
          <AppInput
            v-model="form.startsAt"
            type="datetime-local"
            :label="t('admin.marketing.promotions.starts_at') + ' (Опціонально)'"
          />
          <AppInput
            v-model="form.endsAt"
            type="datetime-local"
            :label="t('admin.marketing.promotions.ends_at') + ' (Опціонально)'"
          />
        </div>
      </div>

      <div class="space-y-6">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
          <span class="w-1.5 h-4 bg-[#00a046] rounded-full" />
          Параметри застосування
        </h4>

        <div class="p-6 bg-gray-50/50 dark:bg-zinc-900/50 rounded-2xl border border-gray-200 dark:border-zinc-800 space-y-6">
          <AppSelect
            v-model="form.applicationType"
            label="Спосіб застосування"
            :options="[
              { id: 'code', name: 'За промокодом' },
              { id: 'auto', name: 'Автоматично' },
              { id: 'url', name: 'За спеціальним посиланням' }
            ]"
            option-value="id"
            option-label="name"
          />

          <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-zinc-800">
            <div>
              <span class="text-sm font-semibold text-gray-900 dark:text-white block">
                {{ t("admin.marketing.promotions.sitewide") }}
              </span>
              <span class="text-[10px] text-gray-405 dark:text-gray-500 uppercase tracking-wider">
                Автоматично застосувати до всього каталогу
              </span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="form.isSitewide"
                type="checkbox"
                class="sr-only peer"
                @change="handleSitewideChange"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[#00a046] shadow-sm" />
            </label>
          </div>

          <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-zinc-800">
            <AppInput
              v-model.number="form.usageLimit"
              type="number"
              min="0"
              label="Загальний ліміт використань (Опціонально)"
              placeholder="Необмежено"
            />
            <AppInput
              v-model.number="form.usageLimitPerUser"
              type="number"
              min="0"
              label="Ліміт на одного користувача (Опціонально)"
              placeholder="Необмежено"
            />
          </div>

          <transition name="expand">
            <div
              v-if="form.isSitewide || form.applicationType === 'auto'"
              class="space-y-4 pt-4 border-t border-gray-200 dark:border-zinc-800"
            >
              <AppInput
                v-model="form.bannerText"
                type="text"
                :label="t('admin.marketing.promotions.banner_text')"
                placeholder="Введіть текст банера..."
              />
              <AppInput
                v-model="form.bannerColor"
                type="text"
                label="Колір банера"
                placeholder="напр. primary, red, green або #FF0000"
              />
            </div>
          </transition>
        </div>
      </div>

      <div class="flex items-center justify-between p-4 bg-gray-55 dark:bg-zinc-900/50 rounded-xl border border-gray-200 dark:border-zinc-800">
        <div>
          <span class="text-sm font-semibold text-gray-900 dark:text-white block">
            {{ t("admin.marketing.promotions.active") }}
          </span>
          <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            {{ t("admin.marketing.promotions.active_hint") }}
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
          {{ t("admin.marketing.promotions.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="loading"
          class="text-sm !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
          type="button"
          @click="submit"
        >
          <RocketLaunchIcon class="w-4 h-4 mr-1.5 inline" />
          {{ isEditing ? t("admin.marketing.promotions.save") : t("admin.marketing.promotions.create") }}
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
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import {
  ExclamationCircleIcon,
  RocketLaunchIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  isOpen: Boolean,
  promotion: {
    type: Object,
    default: null
  },
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
  } catch {
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

    if (isEditing.value && props.promotion) {
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
