<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm"
  >
    <h3
      class="font-black text-xl mb-6 text-gray-900 dark:text-white tracking-tight"
    >
      {{ t("admin.analytics.content_mix") }}
    </h3>
    <div class="space-y-5">
      <div
        v-for="item in content"
        :key="item.label"
        class="space-y-2"
      >
        <div
          class="flex justify-between text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400"
        >
          <span>{{ item.label }}</span>
          <span>{{ item.value }}</span>
        </div>
        <div
          class="h-3 w-full bg-gray-100 dark:bg-gray-900 rounded-full overflow-hidden"
        >
          <div
            class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-1000"
            :style="{ width: `${(item.value / maxValue) * 100}%` }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  content: {
    type: Array,
    required: true,
  },
});

const maxValue = computed(() => {
  if (!props.content?.length) return 1;
  return Math.max(...props.content.map((i) => i.value));
});
</script>
