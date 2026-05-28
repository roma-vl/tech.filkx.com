<template>
  <div class="mb-8">
    <h3 class="text-xl font-semibold mb-4">
      {{ t("dashboard.quickStart.title") }}
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <QuickStartCard
        v-for="action in actions"
        :key="action.id"
        :action="action"
        @click="handleAction(action)"
      />
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import QuickStartCard from "@/components/application/features/dashboard/QuickStartCard.vue";

const { t } = useI18n();

defineProps({
  actions: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["action"]);

const handleAction = (action) => {
  emit("action", action);
};
</script>
