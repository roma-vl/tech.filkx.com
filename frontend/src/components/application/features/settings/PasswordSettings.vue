<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200"
  >
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"
        >
          <Keyicon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">
            {{ $t("settings.account") }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t("settings.account_description") }}
          </p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-6">
      <AppInput
        v-if="hasPassword"
        v-model="localForm.currentPassword"
        :type="showCurrentPassword ? 'text' : 'password'"
        :label="$t('settings.current_password') + ' *'"
        :placeholder="$t('settings.current_password_placeholder')"
        :error="errors.current_password"
      >
        <template #prepend>
          <Keyicon class="h-5 w-5 text-gray-400" />
        </template>
        <template #append>
          <AppButton
            variant="text"
            size="sm"
            class="!p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            @click="showCurrentPassword = !showCurrentPassword"
          >
            <EyeIcon
              v-if="showCurrentPassword"
              class="h-5 w-5"
            />
            <svg
              v-else
              class="h-5 w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
              />
            </svg>
          </AppButton>
        </template>
      </AppInput>

      <div
        v-if="!hasPassword"
        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 rounded-xl mb-4"
      >
        <p class="text-sm text-blue-700 dark:text-blue-300">
          <span class="font-bold">{{
            $t("settings.security_notice") || "Secutiry Notice:"
          }}</span>
          {{
            $t("settings.set_password_notice") ||
              "You are currently using Google to sign in. Set a password to be able to sign in with your email directly."
          }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <AppInput
            v-model="localForm.newPassword"
            :type="showNewPassword ? 'text' : 'password'"
            :label="$t('settings.new_password') + ' *'"
            :placeholder="$t('settings.new_password_placeholder')"
            :error="errors.newPassword"
            @input="calculatePasswordStrength"
          >
            <template #prepend>
              <Keyicon class="h-5 w-5 text-gray-400" />
            </template>
            <template #append>
              <AppButton
                variant="text"
                size="sm"
                class="!p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="showNewPassword = !showNewPassword"
              >
                <EyeIcon
                  v-if="showNewPassword"
                  class="h-5 w-5"
                />
                <svg
                  v-else
                  class="h-5 w-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                  />
                </svg>
              </AppButton>
            </template>
          </AppInput>

          <div
            v-if="localForm.newPassword"
            class="mt-2"
          >
            <div class="flex gap-1 mb-1">
              <div
                v-for="i in 4"
                :key="i"
                class="h-1 flex-1 rounded-full transition-all duration-300"
                :class="
                  i <= passwordStrength.score
                    ? passwordStrength.color
                    : 'bg-gray-200 dark:bg-gray-700'
                "
              />
            </div>
            <p
              class="text-xs font-medium"
              :class="passwordStrength.textColor"
            >
              {{ passwordStrength.label }}
            </p>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ $t("settings.password_requirements") }}
          </p>
        </div>

        <AppInput
          v-model="localForm.confirmPassword"
          :type="showConfirmPassword ? 'text' : 'password'"
          :label="$t('settings.confirm_password') + ' *'"
          :placeholder="$t('settings.confirm_password_placeholder')"
          :error="
            errors.newPasswordConfirmation || errors.password_confirmation
          "
        >
          <template #prepend>
            <Keyicon class="h-5 w-5 text-gray-400" />
          </template>
          <template #append>
            <AppButton
              variant="text"
              size="sm"
              class="!p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <EyeIcon
                v-if="showConfirmPassword"
                class="h-5 w-5"
              />
              <svg
                v-else
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                />
              </svg>
            </AppButton>
          </template>
        </AppInput>
      </div>

      <AppButton
        :disabled="updatingPassword"
        :loading="updatingPassword"
        variant="primary"
        @click="$emit('update', localForm)"
      >
        {{
          hasPassword
            ? $t("settings.update_password")
            : $t("settings.set_password") || "Set Password"
        }}
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import Keyicon from "@/components/Icon/Keyicon.vue";
import EyeIcon from "@/components/Icon/EyeIcon.vue";

const { t } = useI18n();

const props = defineProps({
  passwordForm: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  updatingPassword: {
    type: Boolean,
    default: false,
  },
  hasPassword: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["update"]);

const localForm = ref({ ...props.passwordForm });
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordStrengthScore = ref(0);

watch(
  () => props.passwordForm,
  (newVal) => {
    localForm.value = { ...newVal };
  },
  { deep: true },
);

const calculatePasswordStrength = () => {
  const password = localForm.value.newPassword;
  let score = 0;

  if (password.length >= 8) score++;
  if (password.length >= 12) score++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
  if (/\d/.test(password)) score++;
  if (/[^a-zA-Z0-9]/.test(password)) score++;

  passwordStrengthScore.value = Math.min(score, 4);
};

const passwordStrength = computed(() => {
  const score = passwordStrengthScore.value;

  if (score === 0) {
    return { score: 0, label: "", color: "", textColor: "" };
  } else if (score === 1) {
    return {
      score: 1,
      label: t("settings.password_strength_weak") || "Weak",
      color: "bg-red-500",
      textColor: "text-red-600 dark:text-red-400",
    };
  } else if (score === 2) {
    return {
      score: 2,
      label: t("settings.password_strength_fair") || "Fair",
      color: "bg-orange-500",
      textColor: "text-orange-600 dark:text-orange-400",
    };
  } else if (score === 3) {
    return {
      score: 3,
      label: t("settings.password_strength_good") || "Good",
      color: "bg-yellow-500",
      textColor: "text-yellow-600 dark:text-yellow-400",
    };
  } else {
    return {
      score: 4,
      label: t("settings.password_strength_strong") || "Strong",
      color: "bg-green-500",
      textColor: "text-green-600 dark:text-green-400",
    };
  }
});
</script>
