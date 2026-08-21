<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200"
  >
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center"
        >
          <SubscriptionIcon
            class="w-5 h-5 text-purple-600 dark:text-purple-400"
          />
        </div>
        <div>
          <h3 class="text-lg font-semibold">
            {{ $t("settings.subscription") }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t("settings.subscription_description") }}
          </p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-6">
      <router-link
        to="/account/subscription"
        class="group block"
      >
        <div
          class="bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg p-6 border border-primary-200 dark:border-primary-800 hover:shadow-lg transition-all duration-200 hover:-translate-y-1"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div
                class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center group-hover:scale-110 transition-transform duration-200"
              >
                <CreditCardIcon
                  class="h-6 w-6 text-primary-600 dark:text-primary-400"
                />
              </div>
              <div>
                <h4
                  class="font-semibold text-primary-900 dark:text-primary-100"
                >
                  {{ $t("settings.manage_subscription") }}
                </h4>
                <p class="text-sm text-primary-700 dark:text-primary-300">
                  {{ $t("settings.subscription_description") }}
                </p>
              </div>
            </div>
            <ArrowRightIcon
              class="w-6 h-6 text-primary-600 dark:text-primary-400 group-hover:translate-x-1 transition-transform duration-200"
            />
          </div>
        </div>
      </router-link>

      <div
        v-if="subscriptionActive"
        class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800"
      >
        <div class="flex items-center">
          <div
            class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center mr-4"
          >
            <VerifyIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <h4 class="font-medium text-blue-800 dark:text-blue-200">
              {{ $t("settings.subscription_active") }}
            </h4>
            <p class="text-sm text-blue-700 dark:text-blue-300">
              {{ $t("settings.next_billing") }}: {{ nextBillingDate }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SubscriptionIcon from "@/components/Icon/SubscriptionIcon.vue";
import CreditCardIcon from "@/components/Icon/CreditCardIcon.vue";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";
import VerifyIcon from "@/components/Icon/VerifyIcon.vue";

defineProps({
  subscriptionActive: {
    type: Boolean,
    default: false,
  },
  nextBillingDate: {
    type: String,
    default: null,
  },
  paymentCard: {
    type: Object,
    default: null,
  },
});

defineEmits(["update-subscription", "cancel-subscription", "update-card"]);
</script>
