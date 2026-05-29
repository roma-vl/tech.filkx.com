<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      {{ t("settings.affiliate_referrer_title") }}
    </h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
      {{ t("settings.affiliate_referrer_description") }}
    </p>

    <!-- Already Set State with Details -->
    <div
      v-if="hasReferrer"
      class="space-y-4"
    >
      <div
        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
      >
        <div class="flex items-start">
          <svg
            class="w-5 h-5 text-green-500 mr-3 mt-0.5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          <div class="flex-1">
            <p class="text-sm font-medium text-green-800 dark:text-green-300">
              {{ t("settings.affiliate_referrer_already_set") }}
            </p>
            <div
              v-if="referrerDetails"
              class="mt-3 text-sm text-green-700 dark:text-green-400 space-y-2"
            >
              <div
                class="p-3 bg-white dark:bg-gray-800 rounded border border-green-100 dark:border-green-800/50"
              >
                <p
                  class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide font-semibold"
                >
                  {{ t("settings.affiliate_referrer_linked_link") }}
                </p>
                <div
                  class="font-mono text-gray-800 dark:text-gray-200 break-all"
                >
                  .../partners/<span
                    class="bg-green-100 dark:bg-green-900/50 px-1 py-0.5 rounded text-green-800 dark:text-green-300 font-bold"
                  >{{ referrerDetails.code }}</span>
                </div>
              </div>
              <div class="flex flex-col sm:flex-row sm:space-x-4 text-xs mt-2">
                <p v-if="referrerDetails.name">
                  <span class="text-green-600 dark:text-green-500 font-medium">{{ t("settings.affiliate_referrer_from") }}:</span>
                  {{ referrerDetails.name }}
                </p>
                <p>
                  <span class="text-green-600 dark:text-green-500 font-medium">{{ t("settings.affiliate_referrer_set_on") }}:</span>
                  {{ formatDate(referrerDetails.setAt) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Form -->
    <div
      v-else
      class="space-y-4"
    >
      <div
        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4"
      >
        <p class="text-blue-800 dark:text-blue-200 text-sm">
          💡 {{ t("settings.affiliate_referrer_helper") }}
        </p>
      </div>

      <div
        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4"
      >
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
          {{ t("settings.affiliate_referrer_one_time_warning") }}
        </p>
      </div>

      <div>
        <label
          for="referral-code"
          class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
        >
          {{ t("settings.affiliate_referrer_code_label") }}
        </label>
        <input
          id="referral-code"
          v-model="referralInput"
          type="text"
          :disabled="saving"
          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
          :placeholder="t('settings.affiliate_referrer_placeholder_extended')"
        >

        <!-- Detected code preview -->
        <p
          v-if="referralInput && referralCode && referralInput !== referralCode"
          class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center"
        >
          <svg
            class="w-4 h-4 mr-1"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
          {{ t("settings.affiliate_referrer_detected_code") }}:
          <span class="font-mono font-bold ml-1">{{ referralCode }}</span>
        </p>

        <p
          v-if="error"
          class="mt-2 text-sm text-red-600 dark:text-red-400"
        >
          {{ error }}
        </p>
      </div>

      <button
        :disabled="!referralCode || saving"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
        @click="setReferrer"
      >
        <span v-if="saving"> {{ t("settings.saving") }}... </span>
        <span v-else>
          {{ t("settings.affiliate_referrer_set_button") }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/services/api";

const { t } = useI18n();
const toast = useToast();

const props = defineProps({
  hasReferrer: {
    type: Boolean,
    default: false,
  },
  referrerDetails: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["referrer-set"]);

const referralInput = ref("");
const referralCode = ref("");
const saving = ref(false);
const error = ref(null);

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// Automatically extract code from URL if pasted
watch(referralInput, (newVal) => {
  if (!newVal) {
    referralCode.value = "";
    return;
  }

  // Check if input looks like a URL
  if (newVal.includes("http") || newVal.includes("filkx.com")) {
    try {
      // Try to extract the last segment
      const url = new URL(
        newVal.startsWith("http") ? newVal : `https://${newVal}`,
      );
      const pathSegments = url.pathname.split("/").filter(Boolean);
      if (pathSegments.length > 0) {
        // Assume the last segment is the code (e.g. /partners/code or ?ref=code)
        // If query param 'ref' exists, use that instead
        if (url.searchParams.has("ref")) {
          referralCode.value = url.searchParams.get("ref");
        } else {
          referralCode.value = pathSegments[pathSegments.length - 1];
        }
      } else {
        referralCode.value = newVal; // Fallback
      }
    } catch (e) {
      // If URL parsing fails, just use the input as is
      referralCode.value = newVal;
    }
  } else {
    referralCode.value = newVal;
  }
});

const setReferrer = async () => {
  if (!referralCode.value) return;

  error.value = null;
  saving.value = true;

  try {
    await api.post("/user/settings/referrer", {
      referral_code: referralCode.value,
    });

    toast.success(t("settings.affiliate_referrer_success"));
    emit("referrer-set");
  } catch (e) {
    error.value =
      e.response?.data?.message ?? t("settings.affiliate_referrer_error");
    toast.error(error.value);
  } finally {
    saving.value = false;
  }
};
</script>
