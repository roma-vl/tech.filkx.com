<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" v-if="stats">
      <AppStatCard
        :label="$t('admin.accounting.stats.revenue')"
        :value="formatMoney(stats.totalRevenueMinor)"
        :icon="BanknotesIcon"
        bgClass="bg-green-500"
      />
      <AppStatCard
        :label="$t('admin.accounting.stats.refunds')"
        :value="formatMoney(stats.totalRefundsMinor)"
        :icon="ArrowUturnLeftIcon"
        bgClass="bg-red-500"
      />
      <AppStatCard
        :label="$t('admin.accounting.stats.net')"
        :value="formatMoney(stats.netRevenueMinor)"
        :icon="ScaleIcon"
        bgClass="bg-blue-500"
      />
    </div>

    <!-- Filters -->
    <AdminLedgerFilters
      v-model:filters="filters"
      :loading="exporting"
      @reset="resetFilters"
      @export="downloadExport"
    />

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <AdminTable
        :headers="columns"
        :items="ledgerData.data"
        :loading="loading"
        :empty-text="$t('common.no_data')"
      >
        <template #row="{ item }">
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
            {{ item.id }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ item.user?.name || '-' }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm">
            <AdminBadge :color="getTypeColor(item.type)">
              {{ item.type }}
            </AdminBadge>
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
          <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
            <span :class="getAmountClass(item)">
              {{ formatMoney(item.amountMinor, item.currency) }}
            </span>
          </td>
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ item.currency }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ item.referenceType }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ formatDate(item.createdAt) }}
          </td>
        </template>
      </AdminTable>
    </div>

  </div>
</template>

<script setup>
import {ref, onMounted, watch, computed} from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AccountingService from '@/services/AccountingService';
import { useI18n } from 'vue-i18n';

import AdminLedgerFilters from '@/components/admin/features/accounting/AdminLedgerFilters.vue';
import AdminTable from '@/components/admin/ui/Data/AdminTable.vue';
import AdminBadge from '@/components/admin/ui/Data/AdminBadge.vue';
import AppStatCard from '@/components/admin/ui/Data/AppStatCard.vue';
import { BanknotesIcon, ArrowUturnLeftIcon, ScaleIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const route = useRoute();

const loading = ref(false);
const exporting = ref(false);
const ledgerData = ref({ data: [], meta: {} });
const stats = ref(null);

const filters = ref({
  user_id: route.query.user_id || '',
  type: route.query.type || '',
  from: route.query.from || '',
  to: route.query.to || '',
  page: parseInt(route.query.page) || 1,
});

const updateUrl = () => {
  const query = {
    ...filters.value,
  };
  Object.keys(query).forEach(key => {
      if (!query[key]) delete query[key];
  });
  router.replace({ query });
};

const resetFilters = () => {
  filters.value = {
    user_id: '',
    type: '',
    from: '',
    to: '',
    page: 1,
  };
  updateUrl();
  fetchLedger(1);
};

let debounceTimer;
watch(filters, (newFilters) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    updateUrl();
    fetchLedger(newFilters.page);
  }, 500);
}, { deep: true });



const columns = computed(() => [
  { key: 'id', label: t('admin.accounting.ledger.columns.id') },
  { key: 'user.name', label: t('admin.accounting.ledger.columns.user'), path: 'user.name' },
  { key: 'type', label: t('admin.accounting.ledger.columns.type') },
  { key: 'amount', label: t('admin.accounting.ledger.columns.amount') },
  { key: 'currency', label: t('admin.accounting.ledger.columns.currency') },
  { key: 'referenceType', label: t('admin.accounting.ledger.columns.ref_type') },
  { key: 'createdAt', label: t('admin.accounting.ledger.columns.date') },
]);

const fetchLedger = async (page = 1) => {
  loading.value = true;
  filters.value.page = page;
  try {
    const response = await AccountingService.getLedger(filters.value);
    ledgerData.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load ledger');
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await AccountingService.getStats();
    stats.value = response.data.data;
  } catch (error) {
    console.error('Failed to load stats');
  }
};

const downloadExport = async () => {
    exporting.value = true;
    try {
        const response = await AccountingService.downloadExport('csv');
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `ledger_export_${new Date().toISOString()}.csv`);
        document.body.appendChild(link);
        link.click();
    } catch (e) {
        toast.error('Export failed');
    } finally {
        exporting.value = false;
    }
};

const formatMoney = (amountMinor, currency = 'USD') => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
  }).format(amountMinor / 100);
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const getAmountClass = (row) => {
    return row.amountMinor < 0 ? 'text-red-600' : 'text-green-600';
};

const getTypeColor = (type) => {
    switch (type) {
        case 'charge': return 'green';
        case 'refund': return 'red';
        case 'adjustment': return 'yellow';
        default: return 'gray';
    }
};

onMounted(() => {
  fetchLedger();
  fetchStats();
});
</script>
