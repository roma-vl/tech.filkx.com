<template>
  <div class="max-w-4xl mx-auto space-y-8 pb-20 relative">
    <div
      v-if="loading"
      class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-3xl"
    >
      <div class="flex flex-col items-center gap-4">
        <div
          class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
        />
        <p class="text-sm font-medium text-gray-500">
          {{ $t("admin.settings.loading") }}
        </p>
      </div>
    </div>

    <GeneralSettings v-model="allSettings.general" />

    <ShopSettings v-model="allSettings.shop" />

    <SecuritySettings v-model="allSettings.security" />

    <Transition name="fade">
      <div
        v-if="saving"
        class="fixed bottom-8 right-8 z-30 flex items-center gap-2 px-5 py-3 rounded-2xl bg-gray-800 dark:bg-gray-700 text-white text-sm font-medium shadow-xl"
      >
        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
        {{ $t("admin.settings.saving") }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import api from "@/shared/services/api/apiClient";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import GeneralSettings from "@/components/application/features/admin/settings/GeneralSettings.vue";
import ShopSettings from "@/components/application/features/admin/settings/ShopSettings.vue";
import SecuritySettings from "@/components/application/features/admin/settings/SecuritySettings.vue";

const toast = useToast();
const { t } = useI18n();
const loading = ref(true);
const saving = ref(false);
let saveTimer = null;

const allSettings = ref({
  general: {
    platformName: "",
    supportEmail: "",
    allowRegistration: true,
  },
  shop: {
    currency: "UAH",
    defaultTaxRate: 20,
    minOrderAmount: 0,
    allowGuestCheckout: true,
    freeShippingThreshold: 0,
  },
  security: {
    rateLimiting: true,
  },
});

const toSnake = (str) => str.replace(/[A-Z]/g, (c) => `_${c.toLowerCase()}`);

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/settings");
    const data = response.data.data.settings;

    Object.keys(allSettings.value).forEach((group) => {
      const groupData = data[group];
      if (!groupData) return;

      Object.keys(allSettings.value[group]).forEach((camelKey) => {
        const snakeKey = toSnake(camelKey);
        const val = groupData[snakeKey] ?? groupData[camelKey];

        if (val !== undefined) {
          if (val === "true" || val === true) allSettings.value[group][camelKey] = true;
          else if (val === "false" || val === false) allSettings.value[group][camelKey] = false;
          else allSettings.value[group][camelKey] = val;
        }
      });
    });
  } catch (error) {
    console.error(error);
    toast.error(t("admin.settings.load_error"));
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const flatSettings = {};
    Object.values(allSettings.value).forEach((group) => {
      Object.entries(group).forEach(([key, value]) => {
        flatSettings[toSnake(key)] = value;
      });
    });

    await api.post("/admin/settings", { settings: flatSettings });
  } catch (error) {
    console.error("Settings save error:", error?.response?.data ?? error);
    toast.error(t("admin.settings.error"));
  } finally {
    saving.value = false;
  }
};

watch(
  allSettings,
  () => {
    if (loading.value) return;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveSettings, 800);
  },
  { deep: true },
);

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
