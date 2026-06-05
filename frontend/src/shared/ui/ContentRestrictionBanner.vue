<template>
  <div
    class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4"
  >
    <div class="max-w-4xl mx-auto space-y-6">
      <div
        class="w-16 h-16 mx-auto rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400"
      >
        <LockClosedIcon class="w-8 h-8" />
      </div>

      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ displayTitle }}
      </h2>

      <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
        {{ description }}
      </p>

      <div class="flex flex-wrap justify-center gap-4 pt-4">
        <router-link
          to="/pricing-plans"
          class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-lg hover:shadow-primary-500/25"
        >
          {{ buttonText }}
        </router-link>
      </div>

      <div
        class="mt-12 pt-12 border-t border-gray-200 dark:border-gray-800 w-full"
      >
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
          {{ t("dashboard.banners.contentRestriction.tutorialsTitle") }}
        </h3>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 text-left">
          <a
            href="#"
            class="group block p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <div
              class="aspect-video rounded-lg bg-gray-200 dark:bg-gray-700 mb-3 flex items-center justify-center relative overflow-hidden"
            >
              <div
                class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"
              />
              <PlayIcon
                class="w-10 h-10 text-white drop-shadow-md transform group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="font-medium text-gray-900 dark:text-gray-200">
              {{ t("dashboard.banners.contentRestriction.howToCreateStream") }}
            </div>
          </a>

          <a
            href="#"
            class="group block p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <div
              class="aspect-video rounded-lg bg-gray-200 dark:bg-gray-700 mb-3 flex items-center justify-center relative overflow-hidden"
            >
              <div
                class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"
              />
              <PlayIcon
                class="w-10 h-10 text-white drop-shadow-md transform group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="font-medium text-gray-900 dark:text-gray-200">
              {{ t("dashboard.banners.contentRestriction.scheduler") }}
            </div>
          </a>

          <a
            href="#"
            class="group block p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            <div
              class="aspect-video rounded-lg bg-gray-200 dark:bg-gray-700 mb-3 flex items-center justify-center relative overflow-hidden"
            >
              <div
                class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"
              />
              <PlayIcon
                class="w-10 h-10 text-white drop-shadow-md transform group-hover:scale-110 transition-transform"
              />
            </div>
            <div class="font-medium text-gray-900 dark:text-gray-200">
              {{ t("dashboard.banners.contentRestriction.workWithPlaylists") }}
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { LockClosedIcon, PlayIcon } from "@heroicons/vue/24/outline";
import { useSubscriptionStore } from "@/stores/subscription";

const { t } = useI18n();

const props = defineProps({
  title: {
    type: String,
    default: null,
  },
});

const subStore = useSubscriptionStore();

const isExpiredTrial = computed(() => {
  // Перевіряємо через subStore.isTrial і чи статус вказує на закінчення, або за датою
  return (
    subStore.isTrial &&
    (subStore.subscriptionStatus === "trial_expired" || subStore.isTrialExpired)
  );
});

const displayTitle = computed(() => {
  if (props.title) return props.title;
  if (isExpiredTrial.value)
    return t("dashboard.banners.contentRestriction.trialExpiredTitle");
  return t("dashboard.banners.contentRestriction.noSubscriptionTitle");
});

const description = computed(() => {
  if (isExpiredTrial.value) {
    return t("dashboard.banners.contentRestriction.trialExpiredDesc");
  }
  return t("dashboard.banners.contentRestriction.noSubscriptionDesc");
});

const buttonText = computed(() => {
  return isExpiredTrial.value
    ? t("dashboard.banners.contentRestriction.choosePlan")
    : t("dashboard.banners.contentRestriction.activateTrial");
});
</script>
