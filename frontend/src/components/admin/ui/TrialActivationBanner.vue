<template>
  <div
    v-if="showBanner"
    class="p-4 mx-2 mt-auto mb-4 rounded-xl bg-gradient-to-br from-primary-500/10 to-primary-600/20 border border-primary-500/20"
  >
    <div class="flex items-start gap-3">
      <div
        class="p-2 rounded-lg bg-primary-500/20 text-primary-600 dark:text-primary-400"
      >
        <SparklesIcon class="w-5 h-5" />
      </div>
      <div class="flex-1">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ t("dashboard.banners.trialActivation.title") }}
        </h4>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
          {{ t("dashboard.banners.trialActivation.description") }}
        </p>
        <router-link
          to="/pricing-plans"
          class="mt-3 block w-full text-center px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors"
        >
          {{ t("dashboard.banners.trialActivation.action") }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { SparklesIcon } from "@heroicons/vue/24/outline";
import { useSubscriptionStore } from "@/stores/subscription";

const { t } = useI18n();
const subscriptionStore = useSubscriptionStore();

const showBanner = computed(() => {
  // Не показуємо, якщо підписка вже активна (включаючи тріал)
  if (subscriptionStore.isActive) return false;

  // Не показуємо, якщо тріал вже був використаний і закінчився
  if (
    subscriptionStore.isTrial &&
    (subscriptionStore.isTrialExpired ||
      subscriptionStore.subscriptionStatus === "trial_expired")
  )
    return false;

  // Показуємо тільки якщо немає ніякої підписки або вона не активна (і це не прострочений тріал)
  return !subscriptionStore.subscription;
});
</script>
