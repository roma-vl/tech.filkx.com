<script setup lang="ts">
import { computed, ref } from "vue";
import QRCode from "qrcode";
import { useAuthStore } from "@/entities/user/model/authStore";

defineProps<{
  expanded: boolean;
}>();
defineEmits<{
  toggle: [];
}>();

const authStore = useAuthStore();

const isEnabled = computed(() => !!authStore.user?.twoFactorEnabled);

const inputClass =
  "w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-800 dark:text-zinc-100 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none transition-all";

// Enrollment (enable) flow — shown inline inside the accordion body
const isEnrolling = ref(false);
const enrolling = ref(false);
const secret = ref("");
const qrCodeDataUrl = ref("");
const confirmCode = ref("");
const confirmError = ref("");
const confirming = ref(false);

// Recovery codes modal — shown once after confirm/regenerate
const isRecoveryCodesModalOpen = ref(false);
const recoveryCodes = ref<string[]>([]);

// Disable modal
const isDisableModalOpen = ref(false);
const disablePassword = ref("");
const disableError = ref("");
const disabling = ref(false);

// Regenerate modal
const isRegenerateModalOpen = ref(false);
const regenerateCode = ref("");
const regenerateError = ref("");
const regenerating = ref(false);

async function startEnrollment() {
  enrolling.value = true;
  confirmError.value = "";
  const result: any = await authStore.enableTwoFactor();
  enrolling.value = false;

  if (!result.ok) {
    confirmError.value = result.error || "Не вдалося розпочати налаштування.";
    return;
  }

  secret.value = result.secret;
  qrCodeDataUrl.value = await QRCode.toDataURL(result.qrCodeUrl);
  confirmCode.value = "";
  isEnrolling.value = true;
}

function cancelEnrollment() {
  isEnrolling.value = false;
  secret.value = "";
  qrCodeDataUrl.value = "";
  confirmCode.value = "";
  confirmError.value = "";
}

async function confirmEnrollment() {
  confirmError.value = "";
  confirming.value = true;
  const result: any = await authStore.confirmTwoFactor(confirmCode.value);
  confirming.value = false;

  if (!result.ok) {
    confirmError.value =
      result.errors?.code?.[0] || "Невірний код підтвердження.";
    return;
  }

  isEnrolling.value = false;
  secret.value = "";
  qrCodeDataUrl.value = "";
  confirmCode.value = "";
  recoveryCodes.value = result.recoveryCodes || [];
  isRecoveryCodesModalOpen.value = true;
}

async function submitDisable() {
  disableError.value = "";
  disabling.value = true;
  const result: any = await authStore.disableTwoFactor(disablePassword.value);
  disabling.value = false;

  if (!result.ok) {
    disableError.value =
      result.errors?.password?.[0] || "Невірний пароль.";
    return;
  }

  isDisableModalOpen.value = false;
  disablePassword.value = "";
}

function openDisableModal() {
  disablePassword.value = "";
  disableError.value = "";
  isDisableModalOpen.value = true;
}

function openRegenerateModal() {
  regenerateCode.value = "";
  regenerateError.value = "";
  isRegenerateModalOpen.value = true;
}

async function submitRegenerate() {
  regenerateError.value = "";
  regenerating.value = true;
  const result: any = await authStore.regenerateTwoFactorRecoveryCodes(
    regenerateCode.value,
  );
  regenerating.value = false;

  if (!result.ok) {
    regenerateError.value = result.errors?.code?.[0] || "Невірний код.";
    return;
  }

  isRegenerateModalOpen.value = false;
  recoveryCodes.value = result.recoveryCodes || [];
  isRecoveryCodesModalOpen.value = true;
}

function copyRecoveryCodes() {
  const text = recoveryCodes.value.join("\n");
  if (navigator?.clipboard) {
    navigator.clipboard.writeText(text).catch(() => {});
  }
}
</script>

<template>
  <div
    class="border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden bg-white dark:bg-zinc-900 shadow-sm transition-all duration-300"
  >
    <button
      class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors"
      @click="$emit('toggle')"
    >
      <div class="flex items-center gap-4">
        <div
          class="w-10 h-10 rounded-lg bg-[#00a046]/10 text-[#00a046] flex items-center justify-center shrink-0"
        >
          <span class="material-symbols-outlined text-[22px]">shield</span>
        </div>
        <div>
          <h3 class="font-black text-sm md:text-base text-zinc-900 dark:text-white">
            Двофакторна автентифікація
          </h3>
          <p
            v-if="!expanded"
            class="text-xs text-zinc-450 dark:text-zinc-500 mt-0.5 font-extrabold"
          >
            {{ isEnabled ? "Увімкнено" : "Додатковий захист вашого акаунту" }}
          </p>
        </div>
      </div>
      <span
        class="material-symbols-outlined text-zinc-400 transition-transform duration-300"
        :class="{ 'rotate-180': expanded }"
        >keyboard_arrow_down</span
      >
    </button>

    <div
      v-show="expanded"
      class="border-t border-zinc-100 dark:border-zinc-800 p-6 bg-zinc-50/20 dark:bg-zinc-900/40"
    >
      <!-- Enabled state -->
      <div v-if="isEnabled && !isEnrolling" class="space-y-4">
        <div class="flex items-center gap-2 text-sm font-bold text-[#00a046]">
          <span class="material-symbols-outlined text-[18px]">check_circle</span>
          Двофакторна автентифікація увімкнена
        </div>
        <div class="flex flex-wrap gap-3">
          <button
            type="button"
            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 px-5 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider"
            @click="openRegenerateModal"
          >
            Оновити резервні коди
          </button>
          <button
            type="button"
            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm"
            @click="openDisableModal"
          >
            Вимкнути
          </button>
        </div>
      </div>

      <!-- Disabled state, not yet enrolling -->
      <div v-else-if="!isEnrolling" class="space-y-4">
        <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400">
          Захистіть свій акаунт додатковим кроком підтвердження під час входу за
          допомогою застосунку-автентифікатора (Google Authenticator, Authy тощо).
        </p>
        <p v-if="confirmError" class="text-xs text-red-500 font-semibold">
          {{ confirmError }}
        </p>
        <button
          type="button"
          :disabled="enrolling"
          class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm flex items-center justify-center gap-2"
          @click="startEnrollment"
        >
          <span
            v-if="enrolling"
            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
          />
          {{ enrolling ? "Завантаження..." : "Увімкнути 2FA" }}
        </button>
      </div>

      <!-- Enrollment: QR + confirm code -->
      <div v-else class="space-y-4">
        <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400">
          1. Відскануйте QR-код застосунком-автентифікатором.
        </p>
        <div class="flex justify-center">
          <img
            v-if="qrCodeDataUrl"
            :src="qrCodeDataUrl"
            alt="QR-код для двофакторної автентифікації"
            class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white p-2"
            width="180"
            height="180"
          >
        </div>
        <p class="text-xs text-center text-zinc-450 dark:text-zinc-500">
          Або введіть ключ вручну:
          <code
            class="block mt-1 font-mono text-xs bg-zinc-100 dark:bg-zinc-800 rounded px-2 py-1 select-all break-all"
            >{{ secret }}</code
          >
        </p>

        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-550 uppercase tracking-wider"
            >2. Введіть 6-значний код із застосунку</label
          >
          <input
            v-model="confirmCode"
            type="text"
            inputmode="numeric"
            maxlength="6"
            placeholder="123456"
            :class="inputClass"
          >
          <p v-if="confirmError" class="text-xs text-red-500 font-semibold">
            {{ confirmError }}
          </p>
        </div>

        <div class="flex gap-3 justify-end pt-2">
          <button
            type="button"
            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 px-5 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider"
            @click="cancelEnrollment"
          >
            Скасувати
          </button>
          <button
            type="button"
            :disabled="confirming || confirmCode.length !== 6"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg font-black text-xs md:text-sm transition-all uppercase tracking-wider shadow-sm disabled:opacity-50 flex items-center justify-center gap-2"
            @click="confirmEnrollment"
          >
            <span
              v-if="confirming"
              class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
            />
            {{ confirming ? "Підтвердження..." : "Підтвердити" }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Disable modal -->
  <div
    v-if="isDisableModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3 class="font-black text-base text-zinc-900 dark:text-white">
          Вимкнути 2FA
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650"
          @click="isDisableModalOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form class="p-6 space-y-4" @submit.prevent="submitDisable">
        <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400">
          Введіть поточний пароль, щоб підтвердити вимкнення двофакторної
          автентифікації.
        </p>
        <input
          v-model="disablePassword"
          type="password"
          placeholder="Поточний пароль"
          :class="inputClass"
        >
        <p v-if="disableError" class="text-xs text-red-500 font-semibold">
          {{ disableError }}
        </p>
        <div class="flex gap-3 justify-end pt-2">
          <button
            type="button"
            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider"
            @click="isDisableModalOpen = false"
          >
            Скасувати
          </button>
          <button
            type="submit"
            :disabled="disabling || !disablePassword"
            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider shadow-sm disabled:opacity-50"
          >
            {{ disabling ? "Вимкнення..." : "Вимкнути" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Regenerate modal -->
  <div
    v-if="isRegenerateModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3 class="font-black text-base text-zinc-900 dark:text-white">
          Оновити резервні коди
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650"
          @click="isRegenerateModalOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form class="p-6 space-y-4" @submit.prevent="submitRegenerate">
        <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400">
          Старі резервні коди перестануть працювати. Введіть поточний код із
          застосунку-автентифікатора, щоб підтвердити.
        </p>
        <input
          v-model="regenerateCode"
          type="text"
          inputmode="numeric"
          maxlength="6"
          placeholder="123456"
          :class="inputClass"
        >
        <p v-if="regenerateError" class="text-xs text-red-500 font-semibold">
          {{ regenerateError }}
        </p>
        <div class="flex gap-3 justify-end pt-2">
          <button
            type="button"
            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider"
            @click="isRegenerateModalOpen = false"
          >
            Скасувати
          </button>
          <button
            type="submit"
            :disabled="regenerating || regenerateCode.length !== 6"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider shadow-sm disabled:opacity-50"
          >
            {{ regenerating ? "Оновлення..." : "Оновити" }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Recovery codes modal (shown once) -->
  <div
    v-if="isRecoveryCodesModalOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-150 dark:border-zinc-800 px-6 py-5"
      >
        <h3 class="font-black text-base text-zinc-900 dark:text-white">
          Ваші резервні коди
        </h3>
      </div>
      <div class="p-6 space-y-4">
        <p class="text-xs md:text-sm text-amber-600 dark:text-amber-400 font-semibold">
          Збережіть ці коди в надійному місці. Кожен код можна використати лише
          один раз для входу, якщо ви втратите доступ до застосунку-автентифікатора.
          Ми покажемо їх лише зараз.
        </p>
        <div
          class="grid grid-cols-2 gap-2 font-mono text-sm bg-zinc-100 dark:bg-zinc-800 rounded-lg p-4"
        >
          <span v-for="rc in recoveryCodes" :key="rc">{{ rc }}</span>
        </div>
        <div class="flex gap-3 justify-end pt-2">
          <button
            type="button"
            class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider"
            @click="copyRecoveryCodes"
          >
            Скопіювати
          </button>
          <button
            type="button"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-black text-xs transition-all uppercase tracking-wider shadow-sm"
            @click="isRecoveryCodesModalOpen = false"
          >
            Готово
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
