<template>
  <div
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6"
  >
    <AppStatCard
      v-for="stat in quickStats"
      :key="stat.labelKey"
      :label="t(stat.labelKey)"
      :value="stat.value"
      :icon="stat.icon"
      :bg-class="stat.bg"
    />
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppStatCard from "@/components/admin/ui/Data/AppStatCard.vue";
import {
  ArrowTrendingUpIcon,
  BanknotesIcon,
  ClockIcon,
  CreditCardIcon,
  InboxIcon,
  UserGroupIcon,
} from "@heroicons/vue/24/outline";

const { t } = useI18n();

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
});

const quickStats = computed(() => [
  {
    labelKey: "admin.billing.stats.pending",
    value: props.stats.pendingPayments || 0,
    icon: InboxIcon,
    bg: "bg-amber-600",
  },
  {
    labelKey: "admin.billing.stats.active",
    value: props.stats.activeSubscriptions || 0,
    icon: UserGroupIcon,
    bg: "bg-green-600",
  },
  {
    labelKey: "admin.billing.stats.mrr",
    value: `$${props.stats.mrr?.toFixed(2) || "0.00"}`,
    icon: BanknotesIcon,
    bg: "bg-blue-600",
  },
  {
    labelKey: "admin.billing.stats.revenue",
    value: `$${props.stats.totalRevenueThisMonth || 0}`,
    icon: ArrowTrendingUpIcon,
    bg: "bg-purple-600",
  },
  {
    labelKey: "admin.billing.stats.trials",
    value: props.stats.trialSubscriptions || 0,
    icon: ClockIcon,
    bg: "bg-indigo-600",
  },
  {
    labelKey: "admin.billing.stats.expired",
    value: props.stats.expiredTrials || 0,
    icon: CreditCardIcon,
    bg: "bg-gray-600",
  },
]);
</script>
