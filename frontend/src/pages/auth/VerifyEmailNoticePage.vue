<template>
  <AuthLayout size="md">
    <div class="text-center w-full">
      <div class="mb-8">
        <div
          ref="container"
          class="w-full max-w-[200px] mx-auto"
        />
      </div>

      <h1
        class="text-3xl font-extrabold mb-4 text-gray-900 dark:text-white tracking-tight"
      >
        {{ $t("auth.verifyEmailNotice.title") }}
      </h1>

      <p class="text-gray-500 dark:text-gray-400 mb-6 text-lg max-w-sm mx-auto">
        {{ $t("auth.verifyEmailNotice.subtitle") }}
      </p>

      <div
        class="inline-block px-4 py-2 bg-primary-50 dark:bg-primary-900/20 rounded-xl mb-8"
      >
        <p class="font-bold text-primary-600">
          {{ store.user?.email }}
        </p>
      </div>

      <p class="text-gray-500 dark:text-gray-400 mb-10 text-sm">
        {{ $t("auth.verifyEmailNotice.instructions") }}
      </p>

      <div class="space-y-4 max-w-xs mx-auto">
        <AppButton
          v-if="!sent"
          variant="primary"
          size="lg"
          class="w-full !rounded-xl shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 transition-all duration-300"
          :loading="loading"
          @click="resendEmail"
        >
          {{ $t("auth.verifyEmailNotice.resendButton") }}
        </AppButton>

        <div
          v-else
          class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-800/20"
        >
          <p class="text-sm font-medium text-green-800 dark:text-green-200">
            {{ $t("auth.verifyEmailNotice.resendSuccess") }}
          </p>
        </div>

        <button
          class="w-full py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
          @click="logout"
        >
          {{ $t("auth.verifyEmailNotice.logout") }}
        </button>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import { AppButton } from "@/shared/ui";
import lottie from "lottie-web";
import CheckedAnimation from "@/assets/animation/Checked.json";

const router = useRouter();
const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const container = ref<HTMLElement | null>(null);
const loading = ref(false);
const sent = ref(false);

onMounted(() => {
  if (container.value) {
    lottie.loadAnimation({
      container: container.value,
      renderer: "svg",
      loop: true,
      autoplay: true,
      animationData: CheckedAnimation,
    });
  }
});

async function resendEmail() {
  if (!store.user?.email) {
    toast.error(t("auth.verifyEmailNotice.toastNoEmail"));
    return;
  }

  loading.value = true;

  const result: any = await store.resendVerification(store.user.email);

  loading.value = false;

  if (result.ok) {
    sent.value = true;
    toast.success(t("auth.verifyEmailNotice.resendSuccess"));

    setTimeout(() => {
      sent.value = false;
    }, 30000);
  } else {
    toast.error(result.error || t("auth.verifyEmailNotice.toastError"));
  }
}

async function logout() {
  await store.logout();
  router.push("/login");
}
</script>
