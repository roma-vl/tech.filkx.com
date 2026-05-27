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
            class="w-full !rounded-xl !py-4 text-lg font-bold shadow-xl shadow-primary-500/20 hover:shadow-primary-500/40 transition-all duration-300"
            :loading="loading"
          >
            {{ $t("auth.resetPassword.submit") }}
          </AppButton>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/stores/auth";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppButton from "@/components/ui/AppButton.vue";

const router = useRouter();
const route = useRoute();
const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const form = reactive({
  email: "",
  password: "",
  password_confirmation: "",
  token: "",
});

const errors = reactive({
  email: null,
  password: null,
  password_confirmation: null,
  token: null,
});

const loading = ref(false);

onMounted(() => {
  form.token = route.query.token || "";
  form.email = route.query.email || "";

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

  const result = await store.resetPassword({
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

      const firstError = Object.values(result.errors)[0][0];
      toast.error(firstError);
    } else {
      toast.error(result.error || t("auth.resetPassword.toastError"));
    }
  }
}
</script>
