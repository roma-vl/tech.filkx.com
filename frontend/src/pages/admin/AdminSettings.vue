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

    <SecuritySettings v-model="allSettings.security" />

    <StorageSettings v-model="allSettings.storage" />
    
    <TranscodingSettings v-model="allSettings.transcoding" />

    <SaveSettingsButton
      :saving="saving"
      @save="saveSettings"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import GeneralSettings from "@/components/application/features/admin/settings/GeneralSettings.vue";
import SecuritySettings from "@/components/application/features/admin/settings/SecuritySettings.vue";
import StorageSettings from "@/components/application/features/admin/settings/StorageSettings.vue";
import TranscodingSettings from "@/components/application/features/admin/settings/TranscodingSettings.vue";
import SaveSettingsButton from "@/components/application/features/admin/settings/SaveSettingsButton.vue";

const toast = useToast();
const { t } = useI18n();
const loading = ref(true);
const saving = ref(false);

const allSettings = ref({
  general: {
    platformName: "",
    supportEmail: "",
    allowRegistration: true,
  },
  security: {
    enforce2fa: false,
    rateLimiting: true,
  },
  appearance: {
    darkModeDefault: true,
  },
  storage: {
    cleanupDays: 30,
  },
  transcoding: {
    platformWatermarkUrl: "",
    platformWatermarkPreview: "",
  },
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/settings");
    const data = response.data.data.settings;

    Object.keys(allSettings.value).forEach((group) => {
      const groupData = data[group] || data;

      if (groupData) {
        Object.keys(allSettings.value[group]).forEach((key) => {
          const snakeKey = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
          const val = groupData[key] !== undefined ? groupData[key] : groupData[snakeKey];
          
          if (val !== undefined) {
            if (val === "true" || val === true) allSettings.value[group][key] = true;
            else if (val === "false" || val === false) allSettings.value[group][key] = false;
            else allSettings.value[group][key] = val;
          }
        });
      }
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
        // Exclude UI-only preview fields
        if (!key.endsWith("Preview") && !key.startsWith("_")) {
          flatSettings[key] = value;
        }
      });
    });

    await api.post("/admin/settings", { settings: flatSettings });
    toast.success(t("admin.settings.success"));
  } catch (error) {
    toast.error(t("admin.settings.error"));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
