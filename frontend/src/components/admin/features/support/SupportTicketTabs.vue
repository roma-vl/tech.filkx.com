<template>
  <div class="flex items-center gap-2">
    <div
      v-for="tab in tabs"
      :key="tab.value"
      :class="[
        'px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest cursor-pointer transition-all duration-300 active:scale-95',
        activeTab === tab.value
          ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25 translate-y-[-1px]'
          : 'text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50/80 dark:hover:bg-gray-700/50',
      ]"
      @click="$emit('update:activeTab', tab.value)"
    >
      {{ t(tab.labelKey) }}
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  activeTab: {
    type: String,
    required: true,
  },
});

defineEmits(["update:activeTab"]);

const tabs = [
  { value: "All", labelKey: "admin.support.tabs.all" },
  { value: "New", labelKey: "admin.support.tabs.new" },
  { value: "Accepted", labelKey: "admin.support.tabs.accepted" },
  { value: "Done", labelKey: "admin.support.tabs.done" },
  { value: "Archived", labelKey: "admin.support.tabs.archived" },
];
</script>
