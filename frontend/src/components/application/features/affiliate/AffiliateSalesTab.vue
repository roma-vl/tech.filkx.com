<template>
  <div class="space-y-6">
    <!-- Filters -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between"
    >
      <div class="flex flex-wrap gap-2 w-full md:w-auto">
        <!-- Sale Type Filter -->
        <div class="relative group">
          <label
            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 ml-1"
          >
            {{ t("affiliate.dashboard.filters.saleType") }}
          </label>
          <select
            v-model="filters.type"
            class="w-full md:w-40 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 outline-none text-sm font-medium transition-all"
          >
            <option value="all">
              {{ t("affiliate.dashboard.filters.all") }}
            </option>
            <option value="subscription">
              {{ t("affiliate.dashboard.filters.subscription") }}
            </option>
            <option value="one_time">
              {{ t("affiliate.dashboard.filters.oneTime") }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="relative group">
          <label
            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 ml-1"
          >
            {{ t("affiliate.dashboard.filters.status") }}
          </label>
          <select
            v-model="filters.status"
            class="w-full md:w-40 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary/20 outline-none text-sm font-medium transition-all"
          >
            <option value="all">
              {{ t("affiliate.dashboard.filters.all") }}
            </option>
            <option value="pending">
              {{ t("affiliate.status.pending") }}
            </option>
            <option value="available">
              {{ t("affiliate.status.available") }}
            </option>
            <option value="paid">
              {{ t("affiliate.status.paid") }}
            </option>
            <option value="cancelled">
              {{ t("affiliate.status.cancelled") }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
    >
      <!-- Desktop View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-gray-50/50 dark:bg-gray-900/50 text-gray-400 text-xs uppercase font-bold tracking-widest"
            >
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.date") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.user") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.subscription") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.type") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.cost") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.level") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.percentage") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.income") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap">
                {{ t("affiliate.dashboard.salesTable.confirmationDate") }}
              </th>
              <th class="px-6 py-4 whitespace-nowrap text-right">
                {{ t("affiliate.dashboard.salesTable.status") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
            <tr
              v-for="sale in filteredSales"
              :key="sale.id"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors cursor-default"
            >
              <td
                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap"
              >
                {{ formatDate(sale.createdAt) }}
              </td>
              <td
                class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white"
              >
                {{ sale.userName }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span
                    class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                  >
                    {{ sale.planName }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ t("affiliate.types." + sale.type) }}
              </td>
              <td
                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
              >
                ${{ (sale.paymentAmount || 0).toFixed(2) }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2.5 py-1 rounded-md bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-300 text-xs font-bold uppercase tracking-wider border border-purple-100 dark:border-purple-800"
                >
                  {{ sale.level || 1 }}
                </span>
              </td>
              <td
                class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-400"
              >
                {{ (sale.commissionRate || 0).toFixed(0) }}%
              </td>
              <td class="px-6 py-4">
                <span class="font-bold text-green-600 dark:text-green-400">
                  +${{ sale.commission.toFixed(2) }}
                </span>
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap"
              >
                {{ sale.confirmedAt ? formatDate(sale.confirmedAt) : "—" }}
              </td>
              <td class="px-6 py-4 text-right">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize"
                  :class="getStatusClasses(sale.status)"
                >
                  {{ t(`affiliate.status.${sale.status}`) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="md:hidden space-y-4 p-4">
        <div
          v-for="sale in filteredSales"
          :key="sale.id"
          class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700 space-y-3"
        >
          <div class="flex justify-between items-start">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-primary-600 font-bold text-sm"
              >
                {{ sale.planName.charAt(0) }}
              </div>
              <div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">
                  {{ sale.userName }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ sale.planName }} • {{ formatDate(sale.createdAt) }}
                </p>
              </div>
            </div>
            <span
              class="px-2 py-1 rounded-lg text-xs font-bold capitalize"
              :class="getStatusClasses(sale.status)"
            >
              {{ t(`affiliate.status.${sale.status}`) }}
            </span>
          </div>

          <div
            class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 dark:border-gray-700"
          >
            <div>
              <p
                class="text-[10px] uppercase font-bold text-gray-400 tracking-wider"
              >
                {{ t("affiliate.dashboard.salesTable.cost") }}
              </p>
              <p class="text-sm font-medium dark:text-gray-300">
                ${{ (sale.paymentAmount || 0).toFixed(2) }}
              </p>
            </div>
            <div>
              <p
                class="text-[10px] uppercase font-bold text-gray-400 tracking-wider text-right"
              >
                {{ t("affiliate.dashboard.salesTable.income") }}
              </p>
              <p
                class="text-sm font-bold text-green-600 dark:text-green-400 text-right"
              >
                +${{ (sale.commission || 0).toFixed(2) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!filteredSales?.length"
        class="py-16 flex flex-col items-center justify-center text-center opacity-50"
      >
        <div
          class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
        >
          <svg
            class="w-8 h-8 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <p class="text-sm font-bold uppercase tracking-widest text-gray-400">
          {{ t("ui.no_data") }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps({
  sales: {
    type: Array,
    default: () => [],
  },
});

const { t } = useI18n();
const filters = reactive({
  type: "all",
  status: "all",
});

const filteredSales = computed(() => {
  return props.sales.filter((sale) => {
    // Filter by type
    if (filters.type !== "all") {
      const saleType = (sale.type || "").toLowerCase();
      if (filters.type === "subscription") {
        if (
          saleType !== "subscription" &&
          saleType !== "subscription_commission"
        )
          return false;
      } else if (filters.type === "one_time") {
        if (saleType !== "one_time" && saleType !== "one-time") return false;
      } else if (saleType !== filters.type) {
        return false;
      }
    }

    // Filter by status
    if (filters.status !== "all") {
      const saleStatus = (sale.status || "").toLowerCase();
      // Handle "on_hold" vs "onHold" discrepancy if any, though usually consistent
      if (saleStatus !== filters.status.toLowerCase()) {
        return false;
      }
    }

    return true;
  });
});

const formatDate = (date) => {
  if (!date) return "—";
  const d = new Date(date);
  if (isNaN(d.getTime())) return "—";

  return d.toLocaleDateString("uk-UA", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const getStatusClasses = (status) => {
  if (!status) return "bg-gray-100 text-gray-600";

  const map = {
    pending:
      "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800",
    frozen:
      "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800",
    rejected:
      "bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800",
    on_hold:
      "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800",
    available:
      "bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-800",
    paid: "bg-teal-50 text-teal-700 dark:bg-teal-900/20 dark:text-teal-400 border border-teal-100 dark:border-teal-800",
    cancelled:
      "bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800",
  };
  return map[status] || "bg-gray-100 text-gray-600";
};
</script>
