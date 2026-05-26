<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900"
  >
    <div class="text-center">
      <div
        v-if="error"
        class="max-w-md mx-auto p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg"
      >
        <div class="text-red-500 mb-4">
          <svg
            class="w-12 h-12 mx-auto"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
          Authentication Failed
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          {{ error }}
        </p>
        <div class="space-y-3">
          <router-link
            to="/login"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full"
          >
            Back to Login
          </router-link>
          <p class="text-xs text-gray-400">
            Need help? Contact support
          </p>
        </div>
      </div>
      <div
        v-else-if="showRestorationModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      >
        <div
          class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full"
        >
          <div class="p-6">
            <div class="text-center mb-4">
              <div
                class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4"
              >
                <svg
                  class="w-8 h-8 text-green-600 dark:text-green-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                Welcome Back!
              </h3>
              <p class="text-gray-500 dark:text-gray-400">
                Your account has been successfully restored.
              </p>
            </div>
            <div class="flex justify-center">
              <button
                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors"
                @click="confirmRestoration"
              >
                Continue to Dashboard
              </button>
            </div>
          </div>
        </div>
      </div>
      <div
        v-else
        class="flex flex-col items-center"
      >
        <svg
          class="animate-spin h-10 w-10 text-primary-600 mb-4"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="text-gray-500 dark:text-gray-400">
          Completing sign in...
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useToast } from "vue-toastification";
import { useSubscriptionStore } from "@/stores/subscription";

const route = useRoute();
const router = useRouter();
const store = useAuthStore();
const toast = useToast();

const error = ref(null);
const showRestorationModal = ref(false);

const confirmRestoration = () => {
  showRestorationModal.value = false;
  const redirect = route.query.redirect || "/dashboard";
  router.push(redirect);
};

onMounted(async () => {
  const token = route.query.token;
  const wasRestored = route.query.restored === "true";
  const errorMessage = route.query.error;

  if (errorMessage) {
    error.value = decodeURIComponent(errorMessage);
    return;
  }

  if (!token) {
    error.value = "Authentication failed. No token received.";
    return;
  }

  try {
    store.setToken(token, 30 * 24 * 60 * 60); // 30 days

    await new Promise((resolve) => setTimeout(resolve, 100));
    await store.fetchUser();

    await new Promise((resolve) => setTimeout(resolve, 1000)); // Wait 1 second
    await store.fetchUser();

    const subscription = useSubscriptionStore();
    await subscription.init();

    toast.success("Successfully signed in with Google!");

    if (wasRestored) {
      showRestorationModal.value = true;
    } else {
      const redirect = route.query.redirect || "/dashboard";
      router.push(redirect);
    }
  } catch (err) {
    console.error("OAuth callback error:", err);
    error.value = err.message || "Failed to complete authentication";
    store.clear(); // Clear any partial auth state
  }
});
</script>
