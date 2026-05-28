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
  MegaphoneIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  RocketLaunchIcon,
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
    labelKey: "admin.marketing.promotions.stats.active",
    value: props.data.activePromotions || "0",
    trend: props.data.activeTrend,
    icon: MegaphoneIcon,
  },
  {
    labelKey: "admin.marketing.promotions.stats.conversions",
    value: props.data.totalConversions || "0",
    trend: props.data.conversionsTrend,
    icon: RocketLaunchIcon,
  },
  {
    labelKey: "admin.marketing.promotions.stats.revenue",
    value: props.data.totalRevenue || "$0",
    trend: props.data.revenueTrend,
    icon: CurrencyDollarIcon,
  },
  {
    labelKey: "admin.marketing.promotions.stats.audience",
    value: props.data.reachedAudience || "0",
    trend: props.data.audienceTrend,
    icon: UserGroupIcon,
  },
]);
</script>
