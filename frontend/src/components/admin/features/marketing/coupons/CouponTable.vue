<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-10 transition-all duration-300"
  >
    <div
      class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gray-50/50 dark:bg-gray-900/20"
    >
      <AppInput
        v-model="internalSearch"
        :placeholder="t('admin.marketing.coupons.search')"
        class="flex-1"
        @input="onSearch"
      >
        <template #prepend>
          <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
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
      :items="coupons"
      :loading="loading"
      :loading-text="t('admin.marketing.coupons.loading')"
      :empty-text="t('admin.marketing.coupons.empty')"
    >
      <template #row="{ item }">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            <span
              class="px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 font-mono font-black text-xs border border-primary-100 dark:border-primary-900/30 shadow-sm group-hover:scale-105 transition-transform"
            >
              {{ item.code }}
            </span>
            <button
              class="text-gray-300 hover:text-primary-500 transition-colors p-1 rounded-lg hover:bg-white dark:hover:bg-gray-800 shadow-sm opacity-0 group-hover:opacity-100 transition-all font-sans"
              @click="copyToClipboard(item.code)"
            >
              <ClipboardIcon class="w-4 h-4" />
            </button>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span class="font-black text-gray-900 dark:text-white text-base">
              {{ formatDiscount(item) }}
            </span>
            <span
              class="text-[10px] font-bold text-gray-400 uppercase tracking-tight"
            >
              {{
                item.type === "percent"
                  ? t("admin.marketing.coupons.types.percent")
                  : t("admin.marketing.coupons.types.fixed")
              }}
            </span>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex flex-col gap-1.5 min-w-[120px]">
            <div class="flex items-center justify-between">
              <span
                class="text-[10px] font-black text-gray-400 uppercase font-sans"
              >Usage</span>
              <span
                class="text-[10px] font-black text-gray-900 dark:text-white font-sans"
              >{{ item.usedCount || 0 }}/{{ item.usageLimit || "∞" }}</span>
            </div>
            <div
              class="w-full h-1.5 bg-gray-100 dark:bg-gray-900 rounded-full overflow-hidden"
            >
              <div
                class="h-full bg-primary-500 rounded-full"
                :style="{
                  width: item.usageLimit
                    ? Math.min(
                      ((item.usedCount || 0) / item.usageLimit) * 100,
                      100,
                    ) + '%'
                    : (item.usedCount || 0) > 0
                      ? '100%'
                      : '0%',
                }"
              />
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex flex-col">
            <span
              class="text-xs font-bold text-gray-700 dark:text-gray-300 font-sans"
            >{{ formatDate(item.expiresAt) }}</span>
            <span
              v-if="isExpired(item.expiresAt)"
              class="text-[10px] font-bold text-red-500/60 uppercase tracking-tighter font-sans"
            >
              {{ t("admin.marketing.coupons.expired") }}
            </span>
          </div>
        </td>
        <td class="px-6 py-4">
          <AdminBadge :variant="item.isActive ? 'success' : 'neutral'">
            {{
              item.isActive
                ? t("admin.marketing.coupons.active")
                : t("admin.marketing.coupons.inactive")
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
import { computed, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
  ClipboardIcon,
  MagnifyingGlassIcon,
  PencilSquareIcon,
  TrashIcon,
} from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AdminTable from "@/components/admin/ui/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/AdminBadge.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import dayjs from "dayjs";
import { useToast } from "vue-toastification";

const { t } = useI18n();
const toast = useToast();

const props = defineProps({
  coupons: { type: Array, required: true },
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
  { id: "", name: t("admin.marketing.coupons.filters.all") || "All Statuses" },
  { id: "active", name: t("admin.marketing.coupons.filters.active") },
  { id: "inactive", name: t("admin.marketing.coupons.filters.inactive") },
]);

const headers = [
  {
    key: "code",
    label: t("admin.marketing.coupons.table.code"),
  },
  {
    key: "discount",
    label: t("admin.marketing.coupons.table.discount"),
  },
  {
    key: "usage",
    label: t("admin.marketing.coupons.table.usage"),
  },
  {
    key: "expiry",
    label: t("admin.marketing.coupons.table.expiry"),
  },
  {
    key: "status",
    label: t("admin.marketing.coupons.table.status"),
  },
  {
    key: "actions",
    label: t("admin.marketing.coupons.table.actions"),
    class: "text-right",
  },
];

const formatDiscount = (coupon) => {
  return coupon.type === "percent"
    ? `${coupon.amount}% OFF`
    : `$${coupon.amount}`;
};

const formatDate = (dateStr) => {
  if (!dateStr) return "Lifetime";
  return dayjs(dateStr).format("MMM DD, YYYY");
};

const isExpired = (dateStr) => {
  if (!dateStr) return false;
  return dayjs(dateStr).isBefore(dayjs());
};

const copyToClipboard = (code) => {
  navigator.clipboard.writeText(code);
  toast.success(t("admin.marketing.coupons.alerts.copied"));
};

let searchTimeout;
const onSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit("update:search", internalSearch.value);
  }, 500);
};

const onFilter = () => {
  emit("update:statusFilter", internalStatus.value);
};

watch(
  () => props.search,
  (val) => (internalSearch.value = val),
);
watch(
  () => props.statusFilter,
  (val) => (internalStatus.value = val),
);
</script>
