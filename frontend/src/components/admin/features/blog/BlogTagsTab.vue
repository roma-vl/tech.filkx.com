<template>
  <div class="space-y-4">
    <!-- Top actions -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
      <AppInput
        v-model="tagSearch"
        placeholder="Пошук тегів..."
        class="flex-1 max-w-md"
      />
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="$emit('add-tag')"
      >
        <PlusIcon class="w-4 h-4" />
        Новий тег
      </AppButton>
    </div>

    <!-- Tag list content -->
    <div class="flex flex-wrap gap-3 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
      <div
        v-for="tag in paginatedTags"
        :key="tag.id"
        class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2.5 shadow-sm"
      >
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ tag.nameUk || tag.nameEn }}</span>
        <span class="text-xs text-gray-400">({{ tag.postsCount }})</span>
        <div class="flex items-center gap-1 ml-1">
          <AppButton
            variant="ghost"
            size="sm"
            class="!p-1 text-blue-600 dark:text-blue-400"
            @click="$emit('edit-tag', tag)"
          >
            <PencilIcon class="w-3.5 h-3.5" />
          </AppButton>
          <AppButton
            variant="ghost"
            size="sm"
            class="!p-1 text-red-600 dark:text-red-400"
            @click="$emit('delete-tag', tag)"
          >
            <TrashIcon class="w-3.5 h-3.5" />
          </AppButton>
        </div>
      </div>
      <div
        v-if="filteredTags.length === 0"
        class="text-sm text-gray-400 py-4 w-full text-center font-medium"
      >
        Тегів не знайдено за вашим запитом
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="tagPaginationMeta.last_page > 1"
      class="px-6 py-4 border-t border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30"
    >
      <AppPagination
        :pagination="tagPaginationMeta"
        @page-change="onTagPageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import {
  PlusIcon,
  PencilIcon,
  TrashIcon
} from "@heroicons/vue/24/outline";

const props = defineProps({
  tags: {
    type: Array,
    default: () => []
  }
});

defineEmits(["add-tag", "edit-tag", "delete-tag"]);

const tagSearch = ref("");
const currentPageTags = ref(1);
const perPageTags = ref(15);

const filteredTags = computed(() => {
  return props.tags.filter((tag) => {
    const query = (tagSearch.value || "").toLowerCase().trim();
    return (
      !query ||
      (tag.nameUk || "").toLowerCase().includes(query) ||
      (tag.nameEn || "").toLowerCase().includes(query)
    );
  });
});

const paginatedTags = computed(() => {
  const start = (currentPageTags.value - 1) * perPageTags.value;
  return filteredTags.value.slice(start, start + perPageTags.value);
});

const tagPaginationMeta = computed(() => ({
  current_page: currentPageTags.value,
  last_page: Math.ceil(filteredTags.value.length / perPageTags.value),
  per_page: perPageTags.value,
  total: filteredTags.value.length
}));

const onTagPageChange = (page) => {
  currentPageTags.value = page;
};

watch(tagSearch, () => {
  currentPageTags.value = 1;
});
</script>
