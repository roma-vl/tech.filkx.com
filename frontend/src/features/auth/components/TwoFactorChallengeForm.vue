<template>
  <div>
    <h1
      class="text-3xl md:text-4xl font-extrabold mb-2 text-gray-900 dark:text-white tracking-tight"
    >
      {{ $t("auth.twoFactorChallenge.title") }}
    </h1>
    <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">
      {{
        useRecoveryCode
          ? $t("auth.twoFactorChallenge.recoverySubtitle")
          : $t("auth.twoFactorChallenge.subtitle")
      }}
    </p>

    <form class="space-y-6" @submit.prevent="submit">
      <UiInput
        v-model="code"
        :label="
          useRecoveryCode
            ? $t('auth.twoFactorChallenge.recoveryCodeLabel')
            : $t('auth.twoFactorChallenge.codeLabel')
        "
        :placeholder="
          useRecoveryCode
            ? $t('auth.twoFactorChallenge.recoveryCodePlaceholder')
            : $t('auth.twoFactorChallenge.codePlaceholder')
        "
        :maxlength="useRecoveryCode ? 9 : 6"
        :inputmode="useRecoveryCode ? 'text' : 'numeric'"
        :error="error"
        :disabled="loading"
      />

      <div class="pt-2">
        <UiButton
          type="submit"
          variant="primary"
          size="lg"
          class="w-full !bg-gradient-to-r !from-[#00a046] !to-[#00b050] !text-white hover:!from-[#00b050] hover:!to-[#00c060] !rounded-xl !py-4 text-lg font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 border-none"
          :loading="loading"
        >
          {{ $t("auth.twoFactorChallenge.submit") }}
        </UiButton>
      </div>
    </form>

    <div class="text-center mt-6 space-y-2">
      <button
        type="button"
        class="block w-full text-sm text-[#00a046] hover:text-[#00b050] font-semibold transition-colors"
        @click="toggleRecoveryMode"
      >
        {{
          useRecoveryCode
            ? $t("auth.twoFactorChallenge.useCodeInstead")
            : $t("auth.twoFactorChallenge.useRecoveryInstead")
        }}
      </button>
      <button
        type="button"
        class="block w-full text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
        @click="$emit('back')"
      >
        {{ $t("auth.twoFactorChallenge.backToLogin") }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/entities/user/model/authStore";
import UiInput from "@/shared/ui/UiInput.vue";
import UiButton from "@/shared/ui/UiButton.vue";

const props = defineProps<{
  challengeToken: string;
}>();

const emit = defineEmits<{
  verified: [];
  back: [];
}>();

const store = useAuthStore();

const code = ref("");
const error = ref("");
const loading = ref(false);
const useRecoveryCode = ref(false);

function toggleRecoveryMode() {
  useRecoveryCode.value = !useRecoveryCode.value;
  code.value = "";
  error.value = "";
}

async function submit() {
  error.value = "";
  loading.value = true;
  const result: any = await store.verifyTwoFactor(
    props.challengeToken,
    code.value,
  );
  loading.value = false;

  if (!result.ok) {
    error.value = result.errors?.code?.[0] || result.error;
    return;
  }

  emit("verified");
}
</script>
