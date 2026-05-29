<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <AppStatsCard
      v-for="stat in stats"
      :key="stat.labelKey"
      :label="t(stat.labelKey)"
      :value="stat.value"
      :trend="stat.trend"
      :icon="stat.icon"
    />
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import AppStatsCard from "@/components/application/ui/Data/AppStatsCard.vue";
import {
  TicketIcon,
  CursorArrowRaysIcon,
  CurrencyDollarIcon,
  ReceiptPercentIcon,
} from "@heroicons/vue/24/outline";
import { computed } from "vue";

const { t } = useI18n();

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
});

const stats = computed(() => [
  {
    labelKey: "admin.marketing.coupons.stats.active",
    value: props.data.activeCoupons || "0",
    trend: props.data.activeTrend,
    icon: TicketIcon,
  },
  {
    labelKey: "admin.marketing.coupons.stats.redemptions",
    value: props.data.totalRedemptions || "0",
    trend: props.data.redemptionsTrend,
    icon: CursorArrowRaysIcon,
  },
  {
    labelKey: "admin.marketing.coupons.stats.revenue",
    value: props.data.totalRevenue || "$0",
    trend: props.data.revenueTrend,
    icon: CurrencyDollarIcon,
  },
  {
    labelKey: "admin.marketing.coupons.stats.avg_discount",
    value: props.data.avgDiscount || "0%",
    trend: props.data.discountTrend,
    icon: ReceiptPercentIcon,
  },
]);
</script>
