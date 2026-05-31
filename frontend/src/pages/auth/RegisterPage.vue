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
        {{ $t("auth.register.title") }}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">
        {{ $t("auth.register.subtitle") }}
      </p>

      <form
        class="space-y-5"
        @submit.prevent="handleSubmit"
      >
        <AppInput
          v-model="form.name"
          :label="$t('auth.register.nameLabel')"
          :placeholder="$t('auth.register.namePlaceholder')"
          required
          :error="errors.name"
          :disabled="loading"
        />

        <AppInput
          v-model="form.email"
          :label="$t('auth.register.emailLabel')"
          type="email"
          :placeholder="
            $t('auth.register.emailPlaceholder', { email: 'name@company.com' })
          "
          required
          :error="errors.email"
          :disabled="loading"
        />

        <AppInput
          v-model="form.password"
          :label="$t('auth.register.passwordLabel')"
          type="password"
          :placeholder="$t('auth.register.passwordPlaceholder')"
          required
          minlength="8"
          :error="errors.password"
          :disabled="loading"
        />

        <AppInput
          v-model="form.password_confirmation"
          :label="$t('auth.register.confirmPasswordLabel')"
          type="password"
          :placeholder="$t('auth.register.confirmPasswordPlaceholder')"
          required
          minlength="8"
          :error="errors.password_confirmation"
          :disabled="loading"
        />

        <div class="pt-4">
          <AppButton
            type="submit"
            variant="primary"
            size="lg"
            class="w-full !rounded-xl !py-4 text-lg font-bold shadow-xl shadow-primary-500/20 hover:shadow-primary-500/40 transition-all duration-300"
            :loading="loading"
          >
            {{ $t("auth.register.submit") }}
          </AppButton>
        </div>
      </form>

      <p class="text-center mt-10 text-gray-600 dark:text-gray-400">
        {{ $t("auth.register.hasAccount") }}
        <router-link
          to="/login"
          class="text-primary-600 hover:text-primary-500 font-bold transition-colors"
        >
          {{ $t("auth.register.signIn") }}
        </router-link>
      </p>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useI18n } from "vue-i18n";
import lottie from "lottie-web";
import CheckedAnimation from "@/assets/animation/Login.json";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import { AppInput, AppButton } from "@/shared/ui";
import { useReCaptcha } from "vue-recaptcha-v3";

const { executeRecaptcha, recaptchaLoaded } = useReCaptcha() as any;

const router = useRouter();
const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const container = ref<HTMLElement | null>(null);

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

const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  recaptcha_token: "" as string | undefined,
});

const errors = reactive<Record<string, any>>({
  name: null,
  email: null,
  password: null,
  password_confirmation: null,
});

const loading = ref(false);

function clearErrors() {
  errors.name = null;
  errors.email = null;
  errors.password = null;
  errors.password_confirmation = null;
}

async function handleSubmit() {
  clearErrors();

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t("auth.register.passwordsMismatch");
    toast.error(t("auth.register.passwordsMismatch"));
    return;
  }

  loading.value = true;

  if (store.failedAttempts >= 3) {
    if (recaptchaLoaded) {
      await recaptchaLoaded();
      const token = await executeRecaptcha("register");
      form.recaptcha_token = token;
    }
  }

  const result: any = await store.register(form);
  loading.value = false;

  if (result.ok) {
    toast.success(t("auth.register.successMessage"));
    router.push("/verify-email-notice");
  } else {
    if (result.errors) {
      Object.keys(result.errors).forEach((key) => {
        if (errors.hasOwnProperty(key)) errors[key] = result.errors[key][0];
      });
      const errValues: any = Object.values(result.errors);
      toast.error(errValues[0][0]);
    } else {
      toast.error(result.error || t("auth.register.errorMessage"));
    }
  }
}
</script>
