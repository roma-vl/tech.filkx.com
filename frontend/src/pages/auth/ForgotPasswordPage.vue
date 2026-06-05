<template>
  <AuthLayout size="md">
    <div class="w-full">
      <h1
        class="text-3xl font-extrabold mb-2 text-gray-900 dark:text-white tracking-tight"
      >
        {{ $t("auth.forgotPassword.title") }}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">
        {{ $t("auth.forgotPassword.subtitle") }}
      </p>

      <form v-if="!sent" class="space-y-6" @submit.prevent="handleSubmit">
        <AppInput
          v-model="email"
          :label="$t('auth.forgotPassword.emailLabel')"
          type="email"
          :placeholder="
            $t('auth.forgotPassword.emailPlaceholder', {
              email: 'name@company.com',
            })
          "
          required
          :error="error"
          :disabled="loading"
        />

        <div class="pt-2">
          <AppButton
            type="submit"
            variant="primary"
            size="lg"
            class="w-full !bg-gradient-to-r !from-[#00a046] !to-[#00b050] !text-white hover:!from-[#00b050] hover:!to-[#00c060] !rounded-xl !py-4 text-lg font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 border-none"
            :loading="loading"
          >
            {{ $t("auth.forgotPassword.submit") }}
          </AppButton>
        </div>

        <router-link
          to="/login"
          class="block text-center text-sm font-semibold text-[#00a046] hover:text-[#00b050] transition-colors"
        >
          {{ $t("auth.forgotPassword.backToLogin") }}
        </router-link>
      </form>

      <div v-else class="text-center py-4">
        <div
          class="mb-6 p-6 bg-green-50 dark:bg-green-900/20 rounded-[2rem] border border-green-100 dark:border-green-800/30"
        >
          <p class="text-lg font-medium text-green-800 dark:text-green-200">
            {{ $t("auth.forgotPassword.sentMessage") }}
          </p>
        </div>

        <router-link
          to="/login"
          class="inline-flex items-center gap-2 text-[#00a046] hover:text-[#00b050] font-bold transition-colors"
        >
          <span>{{ $t("auth.forgotPassword.returnToLogin") }}</span>
          <ArrowRightIcon class="w-5 h-5" />
        </router-link>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useToast } from "vue-toastification";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useI18n } from "vue-i18n";
import AuthLayout from "@/layouts/auth/AuthLayout.vue";
import { AppInput, AppButton } from "@/shared/ui";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";

const toast = useToast();
const store = useAuthStore();
const { t } = useI18n();

const email = ref("");
const error = ref<string>();
const loading = ref(false);
const sent = ref(false);

async function handleSubmit() {
  error.value = undefined;
  loading.value = true;

  const result: any = await store.forgotPassword(email.value);

  loading.value = false;

  if (result.ok) {
    sent.value = true;
    toast.success(t("auth.forgotPassword.toastSuccess"));
  } else {
    if (result.errors?.email) {
      error.value = result.errors.email[0];
    }
    toast.error(result.error || t("auth.forgotPassword.toastError"));
  }
}
</script>
