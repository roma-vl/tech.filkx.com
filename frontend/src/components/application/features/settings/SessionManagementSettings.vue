<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      {{ t("settings.sessions_title") }}
    </h3>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
      {{ t("settings.sessions_description") }}
    </p>

    <div
      v-if="loading"
      class="text-center py-8"
    >
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
      />
    </div>

    <div
      v-else-if="sessions.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      {{ t("settings.sessions_none") }}
    </div>

    <div
      v-else
      class="space-y-3"
    >
      <div
        v-for="session in displayedSessions"
        :key="session.id"
        class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
      >
        <div class="flex items-center space-x-4">
          <div class="flex-shrink-0">
            <div
              class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center"
            >
              <svg
                class="w-5 h-5 text-blue-600 dark:text-blue-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
            </div>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ getSessionLabel(session) }}
              <span
                v-if="session.isCurrent"
                class="ml-2 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 px-2 py-1 rounded-full"
              >
                {{ t("settings.sessions_current") }}
              </span>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ session.platform }} • {{ session.device }}
              <span
                v-if="session.ipAddress"
                class="ml-2"
              >• {{ t("settings.sessions_ip") }}:
                {{ session.ipAddress }}</span>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ t("settings.sessions_last_active") }}:
              {{ formatDate(session.lastActive) }}
            </p>
          </div>
        </div>
      </div>

      <div
        v-if="sessions.length > 3"
        class="text-center pt-2"
      >
        <button
          class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium focus:outline-none"
          @click="showAllSessions = !showAllSessions"
        >
          {{
            showAllSessions
              ? t("settings.sessions_show_less")
              : t("settings.sessions_show_more", { count: sessions.length - 3 })
          }}
        </button>
      </div>
    </div>

    <div
      v-if="sessions.length > 1"
      class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
    >
      <button
        :disabled="loggingOut"
        class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
        @click="confirmLogoutAll"
      >
        <span v-if="loggingOut"> {{ t("settings.logging_out") }}... </span>
        <span v-else>
          {{ t("settings.sessions_logout_all") }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/services/api";

const { t } = useI18n();
const toast = useToast();

const sessions = ref([]);
const loading = ref(true);
const loggingOut = ref(false);
const showAllSessions = ref(false);

const displayedSessions = computed(() => {
  if (showAllSessions.value) {
    return sessions.value;
  }
  return sessions.value.slice(0, 3);
});

const loadSessions = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/user/sessions");
    sessions.value = data.data.sessions;
  } catch (e) {
    toast.error(t("settings.sessions_load_error"));
  } finally {
    loading.value = false;
  }
};

const confirmLogoutAll = () => {
  if (confirm(t("settings.sessions_confirm_logout"))) {
    logoutAll();
  }
};

const logoutAll = async () => {
  loggingOut.value = true;
  try {
    await api.post("/user/sessions/logout-all");
    toast.success(t("settings.sessions_logout_success"));
    await loadSessions(); // Reload sessions
  } catch (e) {
    toast.error(t("settings.sessions_logout_error"));
  } finally {
    loggingOut.value = false;
  }
};

const getSessionLabel = (session) => {
  const browser = session.browser || "Unknown Browser";

  // Show real browser name, fallback to generic label if it's an API token
  if (
    browser.toLowerCase().includes("api") ||
    browser.toLowerCase().includes("access") ||
    browser.toLowerCase().includes("token")
  ) {
    return t("settings.sessions_web_browser");
  }

  return browser;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return t("settings.sessions_just_now");
  if (diff < 3600)
    return t("settings.sessions_minutes_ago", { count: Math.floor(diff / 60) });
  if (diff < 86400)
    return t("settings.sessions_hours_ago", { count: Math.floor(diff / 3600) });
  return t("settings.sessions_days_ago", { count: Math.floor(diff / 86400) });
};

onMounted(() => {
  loadSessions();
});
</script>
