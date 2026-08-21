<template>
  <div v-bind="$attrs">
    <div
      class="md:hidden text-xs text-gray-500 dark:text-gray-400 px-4 mb-2 text-center"
    >
      {{ $t("ui.swipe_hint", "← Swipe to see more →") }}
    </div>

    <div
      class="rounded-[2.5rem] border border-white/60 dark:border-white/10 bg-white/40 dark:bg-gray-800/20 backdrop-blur-xl pt-4 grid shadow-sm overflow-hidden"
    >
      <div
        v-if="headerEnabled"
        class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 px-6 mb-4"
      >
        <slot name="header">
          <div
            v-if="searchable"
            class="flex-1 flex gap-2 items-center max-w-sm"
          >
            <AppInput
              v-model="searchQuery"
              placeholder="Search..."
            />
          </div>

          <div class="flex gap-2 items-center">
            <AppDropdown v-if="headings?.length">
              <template #trigger>
                <AppButton
                  variant="secondary"
                  size="sm"
                >
                  Columns
                </AppButton>
              </template>
              <template #content>
                <div class="p-2 space-y-1 max-h-60 overflow-y-auto">
                  <label
                    v-for="col in headings"
                    :key="col.key"
                    class="flex items-center gap-2 px-2 py-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 rounded"
                    @click.stop
                  >
                    <AppCheckbox
                      v-model="visibleColumns"
                      :value="col.key"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-200">{{
                      col.value
                    }}</span>
                  </label>
                </div>
              </template>
            </AppDropdown>

            <AppDropdown v-if="paginationEnabled">
              <template #trigger>
                <AppButton
                  variant="secondary"
                  size="sm"
                >
                  {{ perPage }} / page
                </AppButton>
              </template>
              <template #content>
                <div class="p-2 space-y-1">
                  <label
                    v-for="n in perPageOptions"
                    :key="n"
                    class="flex items-center gap-2 px-2 py-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 rounded"
                    @click.stop
                  >
                    <AppRadio
                      v-model="perPageSelected"
                      :value="n"
                      name="perPage"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-200">{{
                      n
                    }}</span>
                  </label>
                </div>
              </template>
            </AppDropdown>
          </div>
        </slot>
      </div>

      <div class="overflow-x-auto custom-scrollbar w-full">
        <table
          class="w-full table-auto border-collapse text-left text-sm text-gray-600 dark:text-gray-200 min-w-[900px]"
        >
          <thead
            class="bg-gray-50/50 dark:bg-gray-700/40 text-gray-500 dark:text-gray-400 font-semibold text-sm border-b border-gray-200 dark:border-gray-700"
          >
            <tr>
              <th
                v-for="col in visibleHeadings"
                :key="col.key"
                class="px-4 py-3 select-none"
                :class="{
                  'cursor-pointer hover:text-gray-700 dark:hover:text-gray-200':
                    col.sortable,
                }"
                @click="col.sortable && sort(col.key)"
              >
                <div class="flex items-center gap-1">
                  {{ col.value }}
                  <ArrowUpDownIcon
                    v-if="col.sortable"
                    class="w-3 h-3"
                    :class="{
                      'text-primary-500': sortField === col.key,
                    }"
                  />
                </div>
              </th>
            </tr>
          </thead>
          <tbody
            class="divide-y divide-gray-200/50 dark:divide-gray-700/50 bg-transparent"
          >
            <tr
              v-for="item in processedItems"
              :key="item[uniqueKey]"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-700/40 transition-colors duration-200"
            >
              <td
                v-for="col in visibleHeadings"
                :key="col.key"
                class="px-6 py-4"
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
              <td :colspan="visibleHeadings.length">
                <AppEmptyState
                  :title="emptyText || $t('ui.no_data')"
                  :description="$t('ui.no_data_filters')"
                  class="py-12"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="paginationEnabled && pagination"
        class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50"
      >
        <AppPagination
          :pagination="pagination"
          @page-change="changePage"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import ArrowUpDownIcon from "@/components/Icon/ArrowUpDownIcon.vue";
import AppButton from "./AppButton.vue";
import AppInput from "./AppInput.vue";
import AppCheckbox from "./AppCheckbox.vue";
import AppRadio from "./AppRadio.vue";
import AppDropdown from "./AppDropdown.vue";
import AppPagination from "./AppPagination.vue";
import AppEmptyState from "./AppEmptyState.vue";

defineOptions({
  inheritAttrs: false,
});

const props = defineProps({
  items: { type: Array, required: true },
  headings: { type: Array, required: true },
  uniqueKey: { type: String, default: "id" },

  searchable: { type: Boolean, default: true },
  sortable: { type: Boolean, default: true },
  paginationEnabled: { type: Boolean, default: true },
  headerEnabled: { type: Boolean, default: true },

  pagination: { type: Object, default: null },
  perPageOptions: { type: Array, default: () => [5, 10, 20, 50] },

  emptyText: { type: String, default: null },
});

const emit = defineEmits([
  "sort",
  "update:perPage",
  "update:searchQuery",
  "changePage",
]);

const searchQuery = ref("");
const perPageSelected = ref(props.perPageOptions[0]);
const visibleColumns = ref([]);

onMounted(() => {
  visibleColumns.value = props.headings.map((h) => h.key);
});

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
watch(perPageSelected, (val) => emit("update:perPage", val));

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
