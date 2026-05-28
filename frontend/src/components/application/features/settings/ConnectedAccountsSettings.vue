<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/50 p-6"
  >
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
      {{ $t("settings.connected_accounts") || "Connected Accounts" }}
    </h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
      Connect your social accounts to sign in faster.
    </p>

    <div class="space-y-4">
      <!-- Google -->
      <div
        class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center"
          >
            <svg
              class="w-5 h-5"
              viewBox="0 0 24 24"
              width="24"
              height="24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                <path
                  fill="#4285F4"
                  d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"
                />
                <path
                  fill="#34A853"
                  d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"
                />
                <path
                  fill="#FBBC05"
                  d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.734 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"
                />
                <path
                  fill="#EA4335"
                  d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"
                />
              </g>
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">
              Google
            </p>
            <p
              v-if="isConnected('google')"
              class="text-xs text-green-600 dark:text-green-400 font-medium"
            >
              Connected
            </p>
            <p
              v-else
              class="text-xs text-gray-500"
            >
              Not connected
            </p>
          </div>
        </div>

        <AppButton
          v-if="isConnected('google')"
          variant="danger"
          size="sm"
          :loading="processing === 'google'"
          @click="disconnect('google')"
        >
          Disconnect
        </AppButton>
        <AppButton
          v-else
          variant="secondary"
          size="sm"
          :loading="processing === 'google'"
          @click="connect('google')"
        >
          Connect
        </AppButton>
      </div>

      <!-- Facebook (Example/Soon) -->
      <div
        class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg opacity-70"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center"
          >
            <svg
              class="w-5 h-5 text-blue-600"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
              />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">
              Facebook
            </p>
            <p class="text-xs text-gray-500">
              Soon
            </p>
          </div>
        </div>
        <AppButton
          disabled
          size="sm"
          variant="secondary"
        >
          Connect
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useToast } from "vue-toastification";
import api from "@/services/api";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const store = useAuthStore();
const toast = useToast();
const processing = ref(null);

const oauthAccounts = computed(() => {
  const user = store.user;
  return user?.oauth_accounts || user?.oauthAccounts || [];
});

const isConnected = (provider) => {
  return oauthAccounts.value.some((acc) => acc.provider === provider);
};

const connect = async (provider) => {
  processing.value = provider;
  try {
    const { data } = await api.get(`/v1/auth/oauth/${provider}/redirect`);
    window.location.href = data.data.url;
  } catch (e) {
    console.error(`OAuth connect ${provider} error:`, e);
    toast.error(e.response?.data?.message || "Failed to initiate connection");
    processing.value = null;
  }
};

const disconnect = async (provider) => {
  if (!confirm("Are you sure you want to disconnect this account?")) return;

  processing.value = provider;
  try {
    await api.delete(`/v1/auth/oauth/${provider}/disconnect`);
    toast.success("Account disconnected");
    await store.fetchUser();
  } catch (e) {
    toast.error(e.response?.data?.message || "Failed to disconnect");
  } finally {
    processing.value = null;
  }
};
</script>
