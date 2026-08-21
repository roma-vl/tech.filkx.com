<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      {{ t("settings.notification_preferences_title") }}
    </h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
      {{ t("settings.notification_preferences_description") }}
    </p>

    <div class="space-y-4">
      <div
        v-for="pref in preferences"
        :key="pref.key"
        class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700 last:border-0"
      >
        <div>
          <label
            :for="`pref-${pref.key}`"
            class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer"
          >
            {{ pref.label }}
          </label>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ pref.description }}
          </p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            :id="`pref-${pref.key}`"
            v-model="pref.value"
            type="checkbox"
            class="sr-only peer"
            @change="savePreferences"
          >
          <div
            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
          />
        </label>
      </div>
    </div>

    <div
      v-if="saving"
      class="mt-4 text-sm text-gray-500 dark:text-gray-400"
    >
      {{ t("settings.saving") }}...
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";

const { t } = useI18n();
const toast = useToast();

const saving = ref(false);

const preferences = reactive([
  {
    key: "newsletter",
    label: t("settings.notification_newsletter"),
    description: t("settings.notification_newsletter_desc"),
    value: false,
  },
  {
    key: "product_updates",
    label: t("settings.notification_product_updates"),
    description: t("settings.notification_product_updates_desc"),
    value: true,
  },
  {
    key: "marketing_emails",
    label: t("settings.notification_marketing"),
    description: t("settings.notification_marketing_desc"),
    value: false,
  },
]);

const loadPreferences = async () => {
  try {
    const { data } = await api.get("/user/settings/preferences");
    const savedPrefs = data.data.preferences;

    preferences.forEach((pref) => {
      if (savedPrefs.hasOwnProperty(pref.key)) {
        pref.value = savedPrefs[pref.key];
      }
    });
  } catch (e) {
    console.error("Failed to load preferences", e);
  }
};

const savePreferences = async () => {
  saving.value = true;

  try {
    const payload = {};
    preferences.forEach((pref) => {
      payload[pref.key] = pref.value;
    });

    await api.put("/user/settings/preferences", payload);
    toast.success(t("settings.notification_preferences_saved"));
  } catch (e) {
    toast.error(t("settings.notification_preferences_error"));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadPreferences();
});
</script>
