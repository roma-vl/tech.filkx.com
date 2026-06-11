<template>
  <div class="md:hidden text-xs text-gray-400 px-4">
    ← Проведіть пальцем, щоб побачити більше
  </div>

  <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-4 grid">
    <div
      v-if="headerEnabled"
      class="flex flex-col md:flex-row md:justify-between md:items-center gap-2"
    >
      <slot name="header">
        <div
          v-if="searchable"
          class="flex-1 flex gap-2 items-center"
        >
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="flex-1 pl-3 pr-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-200"
          >
        </div>

        <div class="flex gap-2 items-center">
          <Dropdown v-if="headings?.length">
            <template #trigger>
              <button class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded">
                Columns
              </button>
            </template>
            <template #content>
              <label
                v-for="col in headings"
                :key="col.key"
                class="flex items-center gap-2 px-2 py-1"
              >
                <input
                  v-model="visibleColumns"
                  type="checkbox"
                  :value="col.key"
                >
                {{ col.value }}
              </label>
            </template>
          </Dropdown>

          <Dropdown v-if="paginationEnabled">
            <template #trigger>
              <button class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded">
                {{ perPage }}
              </button>
            </template>
            <template #content>
              <label
                v-for="n in perPageOptions"
                :key="n"
                class="flex items-center gap-2 px-2 py-1"
              >
                <input
                  v-model="perPage"
                  type="radio"
                  :value="n"
                >
                {{ n }}
              </label>
            </template>
          </Dropdown>
        </div>
      </slot>
    </div>

    <div class="overflow-x-auto -mx-4 md:mx-0">
      <table
        class="w-full table-auto border-collapse text-left text-sm text-gray-600 dark:text-gray-200 min-w-[900px]"
      >
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th
              v-for="col in visibleHeadings"
              :key="col.key"
              class="px-4 py-2 cursor-pointer select-none"
              @click="sort(col.key)"
            >
              <div class="flex items-center gap-1">
                {{ col.value }}
                <ArrowUpDownIcon
                  v-if="col.sortable"
                  class="w-3 h-3"
                />
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in processedItems"
            :key="item[uniqueKey]"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          >
            <td
              v-for="col in visibleHeadings"
              :key="col.key"
              class="px-4 py-2"
            >
              <slot
                :name="`column-${col.key}`"
                :row="item"
              >
                {{ item[col.key] }}
              </slot>
            </td>
          </tr>
          <tr v-if="processedItems.length === 0">
            <td
              :colspan="visibleHeadings.length"
              class="text-center py-4 text-gray-400 dark:text-gray-300"
            >
              {{ emptyText || $t("ui.no_data") }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination
      v-if="paginationEnabled && pagination"
      :pagination="pagination"
      @page-change="changePage"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import ArrowUpDownIcon from "@/components/Icon/ArrowUpDownIcon.vue";
import Dropdown from "./AppDropdown.vue";
import Pagination from "./AppPagination.vue";

const props = defineProps({
  items: { type: Array, required: true },
  headings: { type: Array, required: true },
  uniqueKey: { type: String, default: "id" },

  /* Feature flags */
  searchable: { type: Boolean, default: true },
  sortable: { type: Boolean, default: true },
  paginationEnabled: { type: Boolean, default: true },
  headerEnabled: { type: Boolean, default: true },

  /* Pagination */
  pagination: { type: Object, default: null },
  perPageOptions: { type: Array, default: () => [5, 10, 20, 50] },

  /* UX */
  /* UX */
  emptyText: { type: String, default: null },
});
/* Note: We will handle default inside template or computed if null using $t */

const emit = defineEmits([
  "sort",
  "update:perPage",
  "update:searchQuery",
  "changePage",
]);

const searchQuery = ref("");
const perPage = ref(props.perPageOptions[0]);
const visibleColumns = ref(props.headings.map((h) => h.key));
const sortField = ref("");
const sortOrder = ref("asc");

const visibleHeadings = computed(() =>
  props.headings.filter((h) => visibleColumns.value.includes(h.key)),
);

const processedItems = computed(() => {
  let data = [...props.items];
  if (searchQuery.value) {
    data = data.filter((item) =>
      Object.values(item).some((v) =>
        String(v).toLowerCase().includes(searchQuery.value.toLowerCase()),
      ),
    );
  }
  if (sortField.value) {
    data.sort((a, b) => {
      if (a[sortField.value] < b[sortField.value])
        return sortOrder.value === "asc" ? -1 : 1;
      if (a[sortField.value] > b[sortField.value])
        return sortOrder.value === "asc" ? 1 : -1;
      return 0;
    });
  }
  return data;
});

watch(searchQuery, (val) => emit("update:searchQuery", val));
watch(perPage, (val) => emit("update:perPage", val));

const sort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortOrder.value = "asc";
  }
  emit("sort", field);
};

const changePage = (url) => {
  emit("changePage", url);
};
</script>
