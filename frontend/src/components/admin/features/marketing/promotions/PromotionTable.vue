<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-10 transition-all duration-300"
  >
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
              <CalendarDaysIcon class="w-3.5 h-3.5" />
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
              <PencilSquareIcon class="w-4 h-4" />
            </button>
            <button
              class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-red-500 hover:border-red-200 dark:hover:border-red-900 shadow-sm transition-all hover:scale-105 active:scale-95"
              @click="$emit('delete', item)"
            >
              <TrashIcon class="w-4 h-4" />
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
import { useI18n } from "vue-i18n";
import {
  CalendarDaysIcon,
  PencilSquareIcon,
  TrashIcon,
} from "@heroicons/vue/24/outline";
import AdminTable from "@/components/admin/ui/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/AdminBadge.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);

const { t } = useI18n();

defineProps({
  promotions: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  pagination: { type: Object, required: true },
});

defineEmits([
  "edit",
  "delete",
  "sort",
  "changePage",
]);

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
