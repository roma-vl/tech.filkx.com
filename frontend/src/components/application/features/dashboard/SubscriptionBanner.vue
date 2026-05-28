<template>
  <div
    class="relative bg-gradient-to-br from-primary-600 via-primary-500 to-indigo-600 rounded-3xl p-8 text-white mb-8 shadow-2xl shadow-primary-500/20 overflow-hidden group"
  >
    <div
      class="absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"
    />
    <div
      class="absolute top-1/2 -left-12 w-48 h-48 bg-indigo-400/20 rounded-full blur-[80px]"
    />
    <div
      class="absolute -bottom-16 right-1/4 w-72 h-72 bg-blue-400/10 rounded-full blur-[100px]"
    />

    <SubscriptionIcon
      class="absolute -bottom-8 -right-8 w-48 h-48 text-white/5 transform rotate-12 pointer-events-none group-hover:scale-110 transition-transform duration-700"
    />

    <div
      class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
    >
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-2">
          <SparklesIcon class="w-5 h-5 text-amber-300 animate-pulse" />
          <h3 class="text-xl md:text-2xl font-bold tracking-tight">
            {{ titleMap[status] }}
          </h3>
        </div>
        <p
          class="text-primary-50/90 text-base md:text-lg max-w-2xl leading-relaxed"
        >
          {{ subtitle }}
        </p>
      </div>

      <div class="flex shrink-0">
        <AppButton
          variant="white"
          to="/pricing-plans"
          size="lg"
          class="relative overflow-hidden group/btn font-bold px-8 !rounded-2xl shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300"
          @click="handleSubscribe"
        >
          <span class="relative z-10 flex items-center gap-2 text-primary-600">
            {{ t("dashboard.subscription.button") }}
            <ArrowRightIcon
              class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform"
            />
          </span>
          <div
            class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/80 to-transparent -translate-x-full group-hover/btn:animate-shimmer"
          />
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import SparklesIcon from "@/components/Icon/SparklesIcon.vue";
import SubscriptionIcon from "@/components/Icon/SubscriptionIcon.vue";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  status: { type: String, required: true },
  daysLeft: { type: Number, required: true },
});

const emit = defineEmits(["subscribe"]);

const titleMap = computed(() => ({
  trial: t("dashboard.subscription.trial.title"),
  trial_expired: t("dashboard.subscription.trialExpired.title"),
  expiring_soon: t("dashboard.subscription.expiringSoon.title"),
  expired: t("dashboard.subscription.expired.title"),
}));

const subtitle = computed(() => {
  if (props.status === "trial")
    return t("dashboard.subscription.trial.daysLeft", { days: props.daysLeft });

  if (props.status === "trial_expired")
    return t("dashboard.subscription.trialExpired.subtitle");

  if (props.status === "expiring_soon")
    return t("dashboard.subscription.expiringSoon.subtitle", {
      days: props.daysLeft,
    });

  if (props.status === "expired")
    return t("dashboard.subscription.expired.subtitle");

  return "";
});

const handleSubscribe = () => emit("subscribe");
</script>

<style scoped>
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 1.5s infinite;
}
</style>
