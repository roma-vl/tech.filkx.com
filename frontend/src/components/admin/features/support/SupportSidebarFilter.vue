<template>
  <div
    class="p-5 space-y-4 bg-gray-50/50 dark:bg-gray-900/10 border-b border-gray-100 dark:border-gray-700/50"
  >
    <!-- Search Input -->
    <div class="relative group">
      <div
        class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors duration-300"
      >
        <MagnifyingGlassIcon class="w-4 h-4" />
      </div>
      <input
        :value="search"
        type="text"
        :placeholder="t('admin.support.actions.search_tickets')"
        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl text-sm focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 outline-none transition-all placeholder:text-gray-400 font-medium"
        @input="$emit('update:search', $event.target.value)"
      >
    </div>

    <!-- Tag Filter -->
    <AppSelect
      :model-value="tag"
      :options="tagOptions"
      :placeholder="t('admin.support.filters.all_tags')"
      @update:model-value="(value) => $emit('update:tag', value)"
    >
      <template #prepend>
        <TagIcon class="w-4 h-4 text-gray-400" />
      </template>
    </AppSelect>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { MagnifyingGlassIcon, TagIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppSelect from "@/components/admin/ui/AppSelect.vue";

const { t } = useI18n();

const props = defineProps({
  search: String,
  tag: String,
  availableTags: {
    type: Array,
    required: true,
  },
});

const tagOptions = computed(() => [
  { id: "", name: t("admin.support.filters.all_tags") },
  ...props.availableTags.map((tag) => ({ id: tag, name: tag })),
]);

defineEmits(["update:search", "update:tag"]);
</script>
