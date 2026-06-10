<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <AppStatCard
      v-for="stat in stats"
      :key="stat.label"
      :label="stat.label"
      :value="stat.value"
      :icon="stat.icon"
      :trend="stat.trend"
      :bg-class="stat.bgClass"
    />
  </div>
</template>

<script setup>
import AppStatCard from "@/components/admin/ui/AppStatCard.vue";

defineProps({
  stats: {
    type: Array,
    required: true,
  },
});
</script>
