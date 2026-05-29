<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <AppStatCard
      :label="$t('admin.affiliates.stats.total')"
      :value="stats.totalAffiliates || 0"
      bg-class="bg-blue-600"
      :icon="UsersIcon"
    />

    <AppStatCard
      :label="$t('admin.affiliates.stats.total_referrals')"
      :value="stats.totalReferrals || 0"
      bg-class="bg-purple-600"
      :icon="LinkIcon"
    />

    <AppStatCard
      :label="$t('admin.affiliates.stats.total_earned')"
      :value="`$${(stats.totalEarnings || 0).toFixed(2)}`"
      bg-class="bg-green-600"
      :icon="CurrencyDollarIcon"
    />

    <AppStatCard
      :label="$t('admin.affiliates.stats.pending_payouts')"
      :value="`$${(stats.pendingPayouts || 0).toFixed(2)}`"
      bg-class="bg-yellow-600"
      :icon="ArrowPathIcon"
    >
      <template #default>
        <p
          v-if="stats.processingPayouts"
          class="text-xs text-yellow-600 dark:text-yellow-400 mt-1"
        >
          ${{ (stats.processingPayouts || 0).toFixed(2) }}
          {{ $t("admin.affiliates.stats.processing") }}
        </p>
      </template>
    </AppStatCard>
  </div>
</template>

<script setup>
import AppStatCard from "@/components/admin/ui/Data/AppStatCard.vue";
import {ArrowPathIcon, CurrencyDollarIcon, LinkIcon, UsersIcon,} from "@heroicons/vue/24/outline";

defineProps({
  stats: {
    type: Object,
    required: true,
  },
});
</script>
