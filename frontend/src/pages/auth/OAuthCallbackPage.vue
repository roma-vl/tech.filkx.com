<template>
  <AuthLayout size="md">
    <div class="text-center w-full py-4">
      <div v-if="error" class="flex flex-col items-center">
        <div
          class="w-24 h-24 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6 text-red-600"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="3"
              d="M12 9v3.75m0 3.75h.008M4.062 18h15.876c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L2.33 15c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <h1
          class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight"
        >
          {{ $t("auth.oauthCallback.errorTitle") }}
        </h1>
        <p
          class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto text-lg leading-relaxed"
        >
          {{ error }}
        </p>
        <UiButton
          to="/login"
          variant="primary"
          size="lg"
          class="w-full !bg-gradient-to-r !from-[#00a046] !to-[#00b050] !rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all duration-300 font-bold"
        >
          {{ $t("auth.oauthCallback.backToLogin") }}
        </UiButton>
        <p class="text-gray-400 dark:text-gray-500 text-xs mt-6">
          {{ $t("auth.oauthCallback.needHelp") }}
        </p>
      </div>

      <div v-else-if="showRestorationModal" class="flex flex-col items-center">
        <div
          class="w-24 h-24 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-6 text-green-600"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="3"
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
        <h1
          class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight"
        >
          {{ $t("auth.oauthCallback.restoredTitle") }}
        </h1>
        <p
          class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto text-lg leading-relaxed"
        >
          {{ $t("auth.oauthCallback.restoredSubtitle") }}
        </p>
        <UiButton
          variant="primary"
          size="lg"
          class="w-full !bg-gradient-to-r !from-[#00a046] !to-[#00b050] !rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all duration-300 font-bold"
          @click="confirmRestoration"
        >
          {{ $t("auth.oauthCallback.continueToDashboard") }}
        </UiButton>
      </div>

      <div v-else class="flex flex-col items-center">
        <div class="relative w-20 h-20 mb-6">
          <div
            class="absolute inset-0 border-4 border-primary-200 rounded-full"
          />
          <div
            class="absolute inset-0 border-4 border-primary-600 rounded-full border-t-transparent animate-spin"
          />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          {{ $t("auth.oauthCallback.completing") }}
        </h2>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import UiButton from "@/shared/ui/UiButton.vue";

const route = useRoute();
const router = useRouter();
const store = useAuthStore();
const toast = useToast();
const { t } = useI18n();

const error = ref<string | null>(null);
const showRestorationModal = ref(false);

const confirmRestoration = () => {
  showRestorationModal.value = false;
  const redirect = (route.query.redirect as string) || "/dashboard";
  router.push(redirect);
};

onMounted(async () => {
  const token = route.query.token as string | undefined;
  const wasRestored = route.query.restored === "true";
  const errorMessage = route.query.error as string | undefined;

  if (errorMessage) {
    error.value = decodeURIComponent(errorMessage);
    return;
  }

  if (!token) {
    error.value = t("auth.oauthCallback.noToken");
    return;
  }

  try {
    store.setToken(token, 30 * 24 * 60 * 60); // 30 days

    await new Promise((resolve) => setTimeout(resolve, 100));
    await store.fetchUser();

    await new Promise((resolve) => setTimeout(resolve, 1000)); // Wait 1 second
    await store.fetchUser();

    toast.success(t("auth.oauthCallback.successMessage"));

    if (wasRestored) {
      showRestorationModal.value = true;
    } else {
      const redirect = (route.query.redirect as string) || "/dashboard";
      router.push(redirect);
    }
  } catch (err: any) {
    console.error("OAuth callback error:", err);
    error.value = err.message || t("auth.oauthCallback.genericError");
    store.clear(); // Clear any partial auth state
  }
});
</script>
