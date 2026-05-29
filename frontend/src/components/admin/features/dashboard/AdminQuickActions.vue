<template>
  <AppCard :title="$t('admin.dashboard.quickActions.title')">
    <div class="space-y-4">
      <button
        v-for="action in actions"
        :key="action.label"
        class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group text-left"
        @click="$emit('action', action.to)"
      >
        <div
          :class="[
            'w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-lg transition-transform group-hover:scale-110 duration-300',
            action.bg,
          ]"
        >
          <component
            :is="action.icon"
            class="w-5 h-5"
          />
        </div>
        <div>
          <p
            class="font-semibold text-sm text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
          >
            {{ action.label }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ action.desc }}
          </p>
        </div>
      </button>
    </div>
  </AppCard>
</template>

<script setup>
import AppCard from "@/components/application/ui/Data/AppCard.vue";

defineProps({
  actions: {
    type: Array,
    required: true,
  },
});

defineEmits(["action"]);
</script>
