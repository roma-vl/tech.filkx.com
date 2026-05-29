<template>
  <div
    class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-2 border-red-200 dark:border-red-900"
  >
    <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">
      {{ t("settings.danger_zone_title") }}
    </h3>

    <div class="space-y-4">
      <div>
        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">
          {{ t("settings.delete_account_title") }}
        </h4>
        <div
          class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4"
        >
          <p class="text-red-800 dark:text-red-200 text-sm">
            {{ t("settings.delete_account_warning") }}
          </p>
          <p class="text-red-700 dark:text-red-300 text-xs mt-2">
            {{ t("settings.delete_account_grace_period") }}
          </p>
        </div>
        <button
          :disabled="deleting"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
          @click="showDeleteModal = true"
        >
          {{ t("settings.delete_account_button") }}
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showDeleteModal = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
      >
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          {{ t("settings.delete_account_confirm_title") }}
        </h3>

        <div class="space-y-4 mb-6">
          <p class="text-gray-700 dark:text-gray-300">
            {{ t("settings.delete_account_confirm_message") }}
          </p>

          <div
            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
          >
            <p class="text-yellow-800 dark:text-yellow-200 text-sm">
              {{ t("settings.delete_account_restore_info") }}
            </p>
          </div>

          <div>
            <label
              for="confirm-password"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
              {{ t("settings.delete_account_password_label") }}
            </label>
            <input
              id="confirm-password"
              v-model="confirmPassword"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              :placeholder="t('settings.delete_account_password_placeholder')"
            >
          </div>
        </div>

        <div class="flex space-x-3">
          <button
            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg font-medium transition-colors"
            @click="showDeleteModal = false"
          >
            {{ t("settings.cancel") }}
          </button>
          <button
            :disabled="!confirmPassword || deleting"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
            @click="deleteAccount"
          >
            <span v-if="deleting"> {{ t("settings.deleting") }}... </span>
            <span v-else>
              {{ t("settings.delete_account_confirm_button") }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();

const showDeleteModal = ref(false);
const confirmPassword = ref("");
const deleting = ref(false);

const deleteAccount = async () => {
  if (!confirmPassword.value) return;

  deleting.value = true;

  try {
    // First verify password (optional - backend should do this)
    await api.post("/user/delete");

    toast.success(t("settings.delete_account_success"));

    // Logout and redirect
    authStore.logout();
    router.push("/login");
  } catch (e) {
    toast.error(
      e.response?.data?.message ?? t("settings.delete_account_error"),
    );
  } finally {
    deleting.value = false;
    showDeleteModal.value = false;
    confirmPassword.value = "";
  }
};
</script>
