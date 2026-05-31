<template>
  <AuthLayout size="lg">
    <template #left>
      <div
        ref="container"
        class="w-full"
      />
    </template>

    <div class="max-w-md mx-auto md:mx-0 w-full">
      <h1
        class="text-3xl md:text-4xl font-extrabold mb-2 text-gray-900 dark:text-white tracking-tight"
      >
        {{ $t("auth.login.title") }}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">
        {{ $t("auth.login.subtitle") }}
      </p>

      <form
        class="space-y-6"
        @submit.prevent="handleSubmit"
      >
        <AppInput
          v-model="form.email"
          :label="$t('auth.login.emailLabel')"
          type="email"
          :placeholder="
            $t('auth.login.emailPlaceholder', { email: 'name@company.com' })
          "
          required
          :error="errors.email"
          :disabled="loading"
        />

        <div>
          <div class="flex justify-between items-center mb-1">
            <label
              class="block text-sm font-medium text-gray-900 dark:text-gray-300"
            >
              {{ $t("auth.login.passwordLabel") }}
            </label>
            <router-link
              to="/forgot-password"
              class="text-sm font-semibold text-primary-600 hover:text-primary-500 transition-colors"
            >
              {{ $t("auth.login.forgotPassword") }}
            </router-link>
          </div>
          <AppInput
            v-model="form.password"
            type="password"
            :placeholder="$t('auth.login.passwordPlaceholder')"
            required
            :error="errors.password"
            :disabled="loading"
          />
        </div>

        <div class="pt-2">
          <AppButton
            type="submit"
            variant="primary"
            size="lg"
            class="w-full !rounded-xl !py-4 text-lg font-bold shadow-xl shadow-primary-500/20 hover:shadow-primary-500/40 transition-all duration-300"
            :loading="loading"
          >
            {{ $t("auth.login.submit") }}
          </AppButton>
        </div>
      </form>

      <div class="relative my-8">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-300 dark:border-gray-700" />
        </div>
        <div class="relative flex justify-center text-sm">
          <span
            class="px-2 bg-white dark:bg-gray-900 text-gray-500 font-medium"
          >
            {{ $t("auth.or_continue_with") }}
          </span>
        </div>
      </div>

      <OAuthButtons @loading="loading = $event" />

      <p class="text-center mt-10 text-gray-600 dark:text-gray-400">
        {{ $t("auth.login.noAccount") }}
        <router-link
          to="/register"
          class="text-primary-600 hover:text-primary-500 font-bold transition-colors"
        >
          {{ $t("auth.login.createAccount") }}
        </router-link>
      </p>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useI18n } from "vue-i18n";
import lottie from "lottie-web";
import CheckedAnimation from "@/assets/animation/Login.json";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import { AppInput, AppButton } from "@/shared/ui";
import OAuthButtons from "@/components/auth/OAuthButtons.vue";
import { useReCaptcha } from "vue-recaptcha-v3";

const { executeRecaptcha, recaptchaLoaded } = useReCaptcha() as any;

const router = useRouter();
const route = useRoute();
const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const container = ref<HTMLElement | null>(null);
const loading = ref(false);

const form = reactive({
  email: "",
  password: "",
  recaptcha_token: "" as string | undefined,
});

const errors = reactive<Record<string, any>>({
  email: null,
  password: null,
});

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

async function handleSubmit() {
  errors.email = null;
  errors.password = null;
  loading.value = true;

  if (store.failedAttempts >= 3) {
    if (recaptchaLoaded) {
      await recaptchaLoaded();
      const token = await executeRecaptcha("login");
      form.recaptcha_token = token;
    }
  }

  const result: any = await store.login(form);

  loading.value = false;

  if (result.ok) {
    toast.success(t("auth.login.successMessage"));
    const redirect = (route.query.redirect as string) || "/dashboard";
    router.push(redirect);
  } else {
    if (result.errors) {
      if (result.errors.email) errors.email = result.errors.email[0];
      if (result.errors.password) errors.password = result.errors.password[0];
    }
    toast.error(result.error || t("auth.login.errorMessage"));
  }
}
</script>
