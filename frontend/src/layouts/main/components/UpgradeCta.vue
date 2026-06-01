<template>
  <router-link
    to="/pricing-plans"
    class="hidden sm:inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-emerald-600 border-2 border-emerald-500 border-dashed hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-150 active:scale-[0.97]"
  >
    <CreditCardIcon class="h-5 w-5 text-emerald-500 dark:text-emerald-500" />
    {{ t("subscription.upgrade_btn") }}
  </router-link>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import CreditCardIcon from "@/components/Icon/CreditCardIcon.vue";

const { t } = useI18n();
</script>
