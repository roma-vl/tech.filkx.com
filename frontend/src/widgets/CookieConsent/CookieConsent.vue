<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const CONSENT_STORAGE_KEY = "cookie_consent";

const isVisible = ref(false);

onMounted(() => {
  if (typeof window === "undefined") return;
  isVisible.value = !localStorage.getItem(CONSENT_STORAGE_KEY);
});

const setConsent = (choice: "accepted" | "essential") => {
  localStorage.setItem(CONSENT_STORAGE_KEY, choice);
  isVisible.value = false;
};

const acceptAll = () => setConsent("accepted");
const rejectNonEssential = () => setConsent("essential");
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform translate-y-4 opacity-0"
    enter-to-class="transform translate-y-0 opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="transform translate-y-0 opacity-100"
    leave-to-class="transform translate-y-4 opacity-0"
  >
    <div
      v-if="isVisible"
      class="fixed inset-x-0 bottom-0 z-[110] p-4 sm:p-6"
      role="dialog"
      aria-live="polite"
      aria-label="Cookie consent"
    >
      <div
        class="mx-auto max-w-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4"
      >
        <div class="flex items-start gap-3 flex-grow">
          <span
            class="material-symbols-outlined text-[#00a046] text-[24px] shrink-0"
            >cookie</span
          >
          <p
            class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed"
          >
            {{ t("cookieConsent.message") }}
            <router-link
              to="/cookies"
              class="font-semibold text-[#00a046] hover:underline"
            >
              {{ t("cookieConsent.policyLink") }} </router-link
            >.
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
          <button
            type="button"
            class="px-4 py-2 text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            @click="rejectNonEssential"
          >
            {{ t("cookieConsent.reject") }}
          </button>
          <button
            type="button"
            class="px-4 py-2 text-xs sm:text-sm font-bold text-white bg-[#00a046] rounded-lg hover:bg-[#00b050] transition-colors shadow-md shadow-[#00a046]/20"
            @click="acceptAll"
          >
            {{ t("cookieConsent.accept") }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
