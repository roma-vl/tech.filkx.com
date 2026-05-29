<template>
  <div
    v-if="showBanner"
    class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 py-3 shadow-lg"
  >
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-3">
        <svg
          class="w-6 h-6 animate-pulse"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
            clip-rule="evenodd"
          />
        </svg>
        <div>
          <p class="font-semibold">
            {{ countdownText }}
          </p>
          <p class="text-sm text-white/90">
            {{ t("dashboard.banners.trialWarning.description") }}
          </p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <router-link
          to="/pricing-plans"
          class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors text-sm whitespace-nowrap"
        >
          {{ t("dashboard.banners.trialWarning.action") }}
        </router-link>
        <button
          class="p-2 hover:bg-white/10 rounded-lg transition-colors"
          @click="dismissBanner"
        >
          <svg
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import { useSubscriptionStore } from "@/stores/subscription";

const { t } = useI18n();
const subStore = useSubscriptionStore();
const sub = computed(() => subStore.subscription);

const dismissed = ref(false);

const trialEndsUtc = computed(() =>
  subStore.subscription?.trialEndsAt
    ? new Date(subStore.subscription.trialEndsAt).getTime()
    : 0,
);
const remainingMs = ref(0);
const countdown = ref("");

// Форматування в hh:mm:ss
function formatCountdown(ms) {
  const totalSeconds = Math.max(Math.floor(ms / 1000), 0);
  const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, "0");
  const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(
    2,
    "0",
  );
  const seconds = String(totalSeconds % 60).padStart(2, "0");
  return `${hours}:${minutes}:${seconds}`;
}

let timer = null;
onMounted(() => {
  remainingMs.value = trialEndsUtc.value - Date.now();
  if (trialEndsUtc.value && remainingMs.value > 0) {
    countdown.value = formatCountdown(remainingMs.value);
    timer = setInterval(() => {
      remainingMs.value = trialEndsUtc.value - Date.now();
      countdown.value = formatCountdown(remainingMs.value);
    }, 1000);
  } else if (trialEndsUtc.value) {
    countdown.value = formatCountdown(0);
  }

  // Перевіряємо dismissed сьогодні
  const lastDismissed = localStorage.getItem("trialBannerDismissed");
  if (lastDismissed === new Date().toDateString()) dismissed.value = true;
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const countdownText = computed(() => {
  if (!subStore.isTrial) return "";
  if (remainingMs.value <= 0)
    return t("dashboard.banners.trialWarning.expired");
  return t("dashboard.banners.trialWarning.expiresIn", {
    countdown: countdown.value,
  });
});

const showBanner = computed(() => {
  if (dismissed.value) return false;
  if (!subStore.subscription) return false;

  // Якщо тріал закінчився, завжди показуємо банер (поки не натиснуть "закрити")
  if (remainingMs.value <= 0 && subStore.isTrial) return true;

  return (
    subStore.isTrial &&
    remainingMs.value > 0 &&
    remainingMs.value <= 3 * 24 * 60 * 60 * 1000
  );
});

const dismissBanner = () => {
  dismissed.value = true;
  localStorage.setItem("trialBannerDismissed", new Date().toDateString());
};
</script>
