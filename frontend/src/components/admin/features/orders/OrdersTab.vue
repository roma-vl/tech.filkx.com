<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <div class="flex flex-1 items-center gap-3">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <AppInput
            :model-value="searchQuery"
            :placeholder="t('admin.orders.filters.searchPlaceholder')"
            @update:model-value="$emit('update:searchQuery', $event)"
          />
        </div>

        <!-- Filters toggle -->
        <AppButton
          variant="secondary"
          class="!p-2.5 relative h-[38px] flex items-center justify-center shrink-0 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gray-400"
          :class="{
            'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
              showFilters,
          }"
          :title="t('admin.orders.filters.toggleTitle')"
          @click="$emit('update:showFilters', !showFilters)"
        >
          <svg
            class="w-5 h-5 transition-colors"
            :class="showFilters ? 'text-[#00a046]' : 'text-gray-500'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            />
          </svg>
          <span
            v-if="activeFiltersCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-[#00a046] text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-emerald-500/30 ring-2 ring-white dark:ring-gray-800"
          >
            {{ activeFiltersCount }}
          </span>
        </AppButton>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <AppButton
          variant="secondary"
          class="flex items-center gap-2 h-[38px] !py-0 text-gray-700 dark:text-gray-300 hover:text-[#00a046] dark:hover:text-[#00b050]"
          @click="$emit('export')"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
          {{ t("admin.orders.actions.exportCsv") }}
        </AppButton>
      </div>
    </div>

    <!-- Toggleable Filters Panel -->
    <transition name="expand">
      <div
        v-if="showFilters"
        class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6 animate-in slide-in-from-top-2 duration-300"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <AppSelect
            :model-value="statusFilter"
            :label="t('admin.orders.filters.statusLabel')"
            :placeholder="t('admin.orders.status.all')"
            :options="statusOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:statusFilter', $event)"
          />
          <AppSelect
            :model-value="paymentFilter"
            :label="t('admin.orders.filters.paymentLabel')"
            :placeholder="t('admin.orders.filters.allMethods')"
            :options="paymentOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:paymentFilter', $event)"
          />
          <AppSelect
            :model-value="deliveryFilter"
            :label="t('admin.orders.filters.deliveryLabel')"
            :placeholder="t('admin.orders.filters.allMethods')"
            :options="deliveryOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:deliveryFilter', $event)"
          />
          <AppSelect
            :model-value="sortFilter"
            :label="t('admin.orders.filters.sortLabel')"
            :placeholder="t('admin.orders.filters.defaultSortPlaceholder')"
            :options="sortOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:sortFilter', $event)"
          />
        </div>

        <div
          class="flex items-center justify-end pt-4 border-t border-gray-150 dark:border-gray-700"
        >
          <AppButton
            variant="text"
            class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
            @click="$emit('reset')"
          >
            {{ t("admin.orders.filters.reset") }}
          </AppButton>
        </div>
      </div>
    </transition>

    <!-- Orders Table -->
    <div
      class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead
            class="bg-gray-50/70 dark:bg-gray-700/30 border-b border-gray-200 dark:border-gray-700"
          >
            <tr>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.order") }}
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.date") }}
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.customer") }}
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.total") }}
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.status") }}
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.orders.table.columns.actions") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
              v-for="order in paginatedOrders"
              :key="order.id"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  #{{ order.orderNumber }}
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  {{
                    t("admin.orders.table.itemsCount", {
                      count: order.items?.length || 0,
                    })
                  }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"
              >
                {{ formatDate(order.createdAt) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ order.customerName }}
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  {{ order.customerEmail }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white"
              >
                {{ formatPrice(order.totalPrice) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold',
                    getStatusClass(order.status),
                  ]"
                >
                  {{ getStatusLabel(order.status) }}
                </span>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="secondary"
                    size="sm"
                    class="font-semibold text-xs bg-gray-50 dark:bg-gray-700/50 hover:bg-[#00a046]/10 text-gray-655 dark:text-gray-400 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors"
                    @click="$emit('view', order)"
                  >
                    {{ t("admin.orders.table.viewDetails") }}
                  </AppButton>
                </div>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-medium"
              >
                <span v-if="isLoading">{{
                  t("admin.orders.table.loading")
                }}</span>
                <span v-else>{{ t("admin.orders.table.empty") }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="paginationMeta.last_page > 1"
        class="px-6 py-4 border-t border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30"
      >
        <AppPagination
          :pagination="paginationMeta"
          @page-change="currentPage = $event"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import { ArrowDownTrayIcon } from "@heroicons/vue/24/outline";

const { t } = useI18n();

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  isLoading: {
    type: Boolean,
    required: true,
  },
  searchQuery: {
    type: String,
    required: true,
  },
  showFilters: {
    type: Boolean,
    required: true,
  },
  statusFilter: {
    type: String,
    required: true,
  },
  paymentFilter: {
    type: String,
    required: true,
  },
  deliveryFilter: {
    type: String,
    required: true,
  },
  sortFilter: {
    type: String,
    required: true,
  },
  activeFiltersCount: {
    type: Number,
    required: true,
  },
  formatDate: {
    type: Function,
    required: true,
  },
  formatPrice: {
    type: Function,
    required: true,
  },
  getStatusLabel: {
    type: Function,
    required: true,
  },
  getStatusClass: {
    type: Function,
    required: true,
  },
});

defineEmits([
  "update:searchQuery",
  "update:showFilters",
  "update:statusFilter",
  "update:paymentFilter",
  "update:deliveryFilter",
  "update:sortFilter",
  "reset",
  "export",
  "view",
]);

// Client-side pagination state
const currentPage = ref(1);
const perPage = ref(15);

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return props.orders.slice(start, start + perPage.value);
});

const paginationMeta = computed(() => ({
  current_page: currentPage.value,
  last_page: Math.ceil(props.orders.length / perPage.value),
  per_page: perPage.value,
  total: props.orders.length,
}));

// Reset page to 1 when filters or search change
watch(
  () => [
    props.searchQuery,
    props.statusFilter,
    props.paymentFilter,
    props.deliveryFilter,
    props.sortFilter,
  ],
  () => {
    currentPage.value = 1;
  },
);

const statusOptions = computed(() => [
  { id: "", name: t("admin.orders.status.all") },
  { id: "pending_payment", name: t("admin.orders.status.pending_payment") },
  { id: "paid", name: t("admin.orders.status.paid") },
  { id: "processing", name: t("admin.orders.status.processing") },
  { id: "packed", name: t("admin.orders.status.packed") },
  { id: "shipped", name: t("admin.orders.status.shipped") },
  { id: "delivered", name: t("admin.orders.status.delivered") },
  { id: "completed", name: t("admin.orders.status.completed") },
  { id: "cancelled", name: t("admin.orders.status.cancelled") },
  { id: "refunded", name: t("admin.orders.status.refunded") },
]);

const paymentOptions = computed(() => [
  { id: "", name: t("admin.orders.filters.allMethods") },
  { id: "cod", name: t("admin.orders.payment.cod") },
  { id: "card", name: t("admin.orders.payment.card") },
  { id: "bank", name: t("admin.orders.payment.bank") },
]);

const deliveryOptions = computed(() => [
  { id: "", name: t("admin.orders.filters.allMethods") },
  { id: "nova_poshta", name: t("admin.orders.delivery.nova_poshta") },
  { id: "ukrposhta", name: t("admin.orders.delivery.ukrposhta") },
  { id: "courier", name: t("admin.orders.delivery.courier") },
  { id: "pickup", name: t("admin.orders.delivery.pickup") },
]);

const sortOptions = computed(() => [
  { id: "created-desc", name: t("admin.orders.filters.sort.newestFirst") },
  { id: "created-asc", name: t("admin.orders.filters.sort.oldestFirst") },
  { id: "price-desc", name: t("admin.orders.filters.sort.priceDesc") },
  { id: "price-asc", name: t("admin.orders.filters.sort.priceAsc") },
  { id: "order-asc", name: t("admin.orders.filters.sort.orderAsc") },
  { id: "order-desc", name: t("admin.orders.filters.sort.orderDesc") },
]);
</script>
