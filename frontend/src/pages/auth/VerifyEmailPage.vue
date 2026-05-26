<template>
  <AuthLayout size="md">
    <div class="text-center w-full py-4">
      <div
        v-if="loading"
        class="flex flex-col items-center"
      >
        <div class="relative w-20 h-20 mb-6">
          <div
            class="absolute inset-0 border-4 border-primary-200 rounded-full"
          />
          <div
            class="absolute inset-0 border-4 border-primary-600 rounded-full border-t-transparent animate-spin"
          />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          {{ $t("auth.verifyEmail.verifying") }}
        </h2>
        <p class="text-gray-500 dark:text-gray-400">
          {{ $t("auth.verifyEmail.verifyingSubtitle") }}
        </p>
      </div>
      <div
        v-else-if="verified"
        class="flex flex-col items-center"
      >
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
          {{ $t("auth.verifyEmail.successTitle") }}
        </h1>
        <p
          class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto text-lg leading-relaxed"
        >
          {{ $t("auth.verifyEmail.successSubtitle") }}
        </p>
        <AppButton
          to="/dashboard"
          variant="primary"
          size="lg"
          class="min-w-[200px] !rounded-xl shadow-xl shadow-primary-500/20 hover:shadow-primary-500/40 transition-all duration-300 font-bold"
        >
          {{ $t("auth.verifyEmail.cta") }}
        </AppButton>
      </div>
      <div
        v-else
        class="flex flex-col items-center"
      >
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
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </div>
        <h1
          class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight"
        >
          {{ $t("auth.verifyEmail.errorTitle") }}
        </h1>
        <p class="text-red-500 mb-8 max-w-sm mx-auto font-medium">
          {{ errorMessage }}
        </p>
        <AppButton
          to="/verify-email-notice"
          variant="secondary"
          size="lg"
          class="min-w-[240px] !rounded-xl font-bold"
        >
          {{ $t("auth.verifyEmail.resendButton") }}
        </AppButton>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const route = useRoute();
const store = useAuthStore();
const { t } = useI18n();

const loading = ref(true);
const verified = ref(false);
const errorMessage = ref(t("auth.verifyEmail.invalidLink"));

onMounted(async () => {
  const { id, hash, expires, signature } = route.query;

  if (!id || !hash || !expires || !signature) {
    loading.value = false;
    errorMessage.value = t("auth.verifyEmail.invalidLink");
    return;
  }

  try {
    const result = await store.verifyEmailByParams({
      id,
      hash,
      expires,
      signature,
    });

    verified.value = true;
    errorMessage.value = "";
  } catch (err) {
    errorMessage.value =
      err?.message || t("auth.verifyEmail.verifyingSubtitle"); // Fallback
  } finally {
    loading.value = false;
  }
});
</script>
