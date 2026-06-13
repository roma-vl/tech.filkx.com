<template>
  <div class="space-y-4">
    <!-- Top Bar actions -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
      <AppInput
        v-model="categorySearch"
        placeholder="Пошук категорій за назвою чи slug..."
        class="flex-1 max-w-md"
      />
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="$emit('add-category')"
      >
        <PlusIcon class="w-4 h-4" />
        Нова категорія
      </AppButton>
    </div>

    <!-- Table content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Назва (УК)
            </th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Назва (EN)
            </th>
            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Slug
            </th>
            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Пости
            </th>
            <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Дії
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="filteredCategories.length === 0">
            <td
              colspan="5"
              class="px-5 py-10 text-center text-gray-400 text-sm font-medium"
            >
              Категорій не знайдено за вашим запитом
            </td>
          </tr>
          <tr
            v-for="cat in paginatedCategories"
            :key="cat.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
          >
            <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-200">
              {{ cat.nameUk }}
            </td>
            <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
              {{ cat.nameEn }}
            </td>
            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-500 font-mono text-xs">
              {{ cat.slug }}
            </td>
            <td class="px-5 py-3.5 text-center">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                {{ cat.postsCount }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-2">
                <AppButton
                  variant="ghost"
                  size="sm"
                  class="!p-1.5 text-blue-600 dark:text-blue-400"
                  @click="$emit('edit-category', cat)"
                >
                  <PencilIcon class="w-3.5 h-3.5" />
                </AppButton>
                <AppButton
                  variant="ghost"
                  size="sm"
                  class="!p-1.5 text-red-600 dark:text-red-400"
                  @click="$emit('delete-category', cat)"
                >
                  <TrashIcon class="w-3.5 h-3.5" />
                </AppButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="categoryPaginationMeta.last_page > 1"
      class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30"
    >
      <AppPagination
        :pagination="categoryPaginationMeta"
        @page-change="onCategoryPageChange"
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
  categories: {
    type: Array,
    default: () => []
  }
});

defineEmits(["add-category", "edit-category", "delete-category"]);

const categorySearch = ref("");
const currentPageCategories = ref(1);
const perPageCategories = ref(15);

const filteredCategories = computed(() => {
  return props.categories.filter((cat) => {
    const query = (categorySearch.value || "").toLowerCase().trim();
    return (
      !query ||
      (cat.nameUk || "").toLowerCase().includes(query) ||
      (cat.nameEn || "").toLowerCase().includes(query) ||
      (cat.slug || "").toLowerCase().includes(query)
    );
  });
});

const paginatedCategories = computed(() => {
  const start = (currentPageCategories.value - 1) * perPageCategories.value;
  return filteredCategories.value.slice(start, start + perPageCategories.value);
});

const categoryPaginationMeta = computed(() => ({
  current_page: currentPageCategories.value,
  last_page: Math.ceil(filteredCategories.value.length / perPageCategories.value),
  per_page: perPageCategories.value,
  total: filteredCategories.value.length
}));

const onCategoryPageChange = (page) => {
  currentPageCategories.value = page;
};

watch(categorySearch, () => {
  currentPageCategories.value = 1;
});
</script>
