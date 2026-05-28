<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-10 transition-all duration-300"
  >
    <div
      class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gray-50/50 dark:bg-gray-900/20"
    >
      <AppInput
        v-model="internalSearch"
        :placeholder="t('admin.marketing.promotions.search')"
        class="flex-1"
        @input="onSearch"
      >
        <template #prepend>
          <MagnifyingGlassIcon class="h-4 w-4 text-gray-400"/>
        </template>
      </AppInput>

      <AppSelect
        v-model="internalStatus"
        :options="statusOptions"
        class="sm:w-64"
        @update:model-value="onFilter"
      />
    </div>

    <AdminTable
      :headers="headers"
      :items="promotions"
      :loading="loading"
      :loading-text="t('admin.marketing.promotions.loading')"
      :empty-text="t('admin.marketing.promotions.empty')"
    >
      <template #row="{ item }">
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span
              class="font-black text-gray-900 dark:text-white text-base group-hover:text-primary-600 transition-colors"
            >
              {{ item.name }}
            </span>
            <span
              class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter font-mono"
            >
              {{ item.slug }}
            </span>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span class="font-black text-gray-900 dark:text-white">
              {{ formatDiscount(item) }}
            </span>
            <span
              v-if="item.isSitewide"
              class="text-[9px] font-black text-indigo-500 uppercase tracking-widest uppercase"
            >
              Sitewide
            </span>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex flex-col gap-1">
            <div
              class="flex items-center gap-2 text-[11px] font-bold text-gray-500"
            >
              <CalendarDaysIcon class="w-3.5 h-3.5"/>
              <span>{{ formatDateRange(item) }}</span>
            </div>
            <div
              v-if="isUpcoming(item.startsAt)"
              class="text-[9px] font-black text-blue-500 uppercase tracking-tighter"
            >
              Starts in {{ formatRelative(item.startsAt) }}
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <AdminBadge :variant="item.isActive ? 'success' : 'neutral'">
            {{
              item.isActive
                ? t("admin.marketing.promotions.active")
                : t("admin.marketing.promotions.inactive")
            }}
          </AdminBadge>
        </td>
        <td class="px-6 py-4 text-right">
          <div class="flex justify-end gap-2">
            <button
              class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-primary-500 hover:border-primary-200 dark:hover:border-primary-900 shadow-sm transition-all hover:scale-105 active:scale-95"
              @click="$emit('edit', item)"
            >
              <PencilSquareIcon class="w-4 h-4"/>
            </button>
            <button
              class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-red-500 hover:border-red-200 dark:hover:border-red-900 shadow-sm transition-all hover:scale-105 active:scale-95"
              @click="$emit('delete', item)"
            >
              <TrashIcon class="w-4 h-4"/>
            </button>
          </div>
        </td>
      </template>
    </AdminTable>

    <div
      v-if="pagination.lastPage > 1"
      class="p-6 bg-gray-50/50 dark:bg-gray-900/20 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4"
    >
      <p
        class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-sans"
      >
        Showing Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
      </p>
      <AppPagination
        :pagination="pagination"
        @page-change="$emit('changePage', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import {computed, ref, watch} from "vue";
import {useI18n} from "vue-i18n";
import {CalendarDaysIcon, MagnifyingGlassIcon, PencilSquareIcon, TrashIcon,} from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);

const { t } = useI18n();

const props = defineProps({
  promotions: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  search: { type: String, default: "" },
  statusFilter: { type: String, default: "" },
  pagination: { type: Object, required: true },
});

const emit = defineEmits([
  "update:search",
  "update:statusFilter",
  "edit",
  "delete",
  "sort",
  "changePage",
]);

const internalSearch = ref(props.search);
const internalStatus = ref(props.statusFilter);

const statusOptions = computed(() => [
  {
    id: "",
    name: t("admin.marketing.promotions.filters.all") || "All Promotions",
  },
  {id: "active", name: t("admin.marketing.promotions.filters.active")},
  {id: "inactive", name: t("admin.marketing.promotions.filters.inactive")},
]);

let debounceTimer;
const onSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    emit("update:search", internalSearch.value);
  }, 500);
};

const onFilter = (val) => {
  emit("update:statusFilter", val);
};

watch(
  () => props.search,
  (newVal) => {
    internalSearch.value = newVal;
  },
);

watch(
  () => props.statusFilter,
  (newVal) => {
    internalStatus.value = newVal;
  },
);

const headers = [
  {
    key: "name",
    label: t("admin.marketing.promotions.table.name"),
    sortable: true,
  },
  {
    key: "discount",
    label: t("admin.marketing.promotions.table.discount"),
    sortable: false,
  },
  {
    key: "timing",
    label: t("admin.marketing.promotions.table.timing"),
    sortable: false,
  },
  {
    key: "status",
    label: t("admin.marketing.promotions.table.status"),
    sortable: true,
  },
  {
    key: "actions",
    label: t("admin.marketing.promotions.table.actions"),
    sortable: false,
    align: "right",
  },
];

const formatDiscount = (promo) => {
  return promo.type === "percent"
    ? `${promo.amount}% OFF`
    : `$${promo.amount} Fixed`;
};

const formatDateRange = (promo) => {
  if (!promo.startsAt && !promo.endsAt) return "Always Active";
  const start = promo.startsAt
    ? dayjs(promo.startsAt).format("MMM DD")
    : "Start";
  const end = promo.endsAt ? dayjs(promo.endsAt).format("MMM DD, YYYY") : "End";
  return `${start} - ${end}`;
};

const isUpcoming = (dateStr) => {
  if (!dateStr) return false;
  return dayjs(dateStr).isAfter(dayjs());
};

const formatRelative = (dateStr) => {
  return dayjs(dateStr).fromNow(true);
};
</script>
