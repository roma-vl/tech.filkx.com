<template>
  <AuthLayout size="md">
    <div class="w-full">
      <h1
        class="text-3xl font-extrabold mb-2 text-gray-900 dark:text-white tracking-tight"
      >
        {{ $t("auth.resetPassword.title") }}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">
        {{ $t("auth.resetPassword.subtitle") }}
      </p>

      <form
        class="space-y-6"
        @submit.prevent="handleSubmit"
      >
        <AppInput
          v-model="form.email"
          :label="$t('auth.resetPassword.emailLabel')"
          type="email"
          :placeholder="
            $t('auth.resetPassword.emailPlaceholder', {
              email: 'name@company.com',
            })
          "
          required
          :error="errors.email"
          :disabled="loading"
        />

        <AppInput
          v-model="form.password"
          :label="$t('auth.resetPassword.passwordLabel')"
          type="password"
          :placeholder="$t('auth.resetPassword.passwordPlaceholder')"
          required
          minlength="8"
          :error="errors.password"
          :disabled="loading"
        />

        <AppInput
          v-model="form.password_confirmation"
          :label="$t('auth.resetPassword.confirmPasswordLabel')"
          type="password"
          :placeholder="$t('auth.resetPassword.confirmPasswordPlaceholder')"
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
            class="w-full !bg-gradient-to-r !from-[#00a046] !to-[#00b050] !text-white hover:!from-[#00b050] hover:!to-[#00c060] !rounded-xl !py-4 text-lg font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 border-none"
            :loading="loading"
          >
            {{ $t("auth.resetPassword.submit") }}
          </AppButton>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import { AppInput, AppButton } from "@/shared/ui";

const router = useRouter();
const route = useRoute();
const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const form = reactive<Record<string, any>>({
  email: "",
  password: "",
  password_confirmation: "",
  token: "",
});

const errors = reactive<Record<string, any>>({
  email: null,
  password: null,
  password_confirmation: null,
  token: null,
});

const loading = ref(false);

onMounted(() => {
  form.token = (route.query.token as string) || "";
  form.email = (route.query.email as string) || "";

  if (!form.token) {
    toast.error(t("auth.resetPassword.invalidLink"));
    router.push("/forgot-password");
  }
});

function clearErrors() {
  errors.email = null;
  errors.password = null;
  errors.password_confirmation = null;
  errors.token = null;
}

async function handleSubmit() {
  clearErrors();

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t("auth.register.passwordsMismatch");
    toast.error(t("auth.register.passwordsMismatch"));
    return;
  }

  loading.value = true;

  const result: any = await store.resetPassword({
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
    token: form.token,
  });

  loading.value = false;

  if (result.ok) {
    toast.success(t("auth.resetPassword.toastSuccess"));
    router.push("/login");
  } else {
    if (result.errors) {
      Object.keys(result.errors).forEach((key) => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = result.errors[key][0];
        }
      });

      const errValues: any = Object.values(result.errors);
      const firstError = errValues[0][0];
      toast.error(firstError);
    } else {
      toast.error(result.error || t("auth.resetPassword.toastError"));
    }
  }
}
</script>
