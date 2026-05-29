<template>
  <div class="space-y-6">
    <AdminInvoiceFilters
      v-model:filters="filters"
      @reset="resetFilters"
    />

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <AdminTable
        :headers="columns"
        :items="invoicesData.data"
        :loading="loading"
        :empty-text="$t('common.no_data')"
      >
        <template #row="{ item }">
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
            {{ item.invoiceNumber }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
             {{ item.user?.name || '-' }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            {{ formatMoney(item.totalMinor, item.currency) }}
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm">
            <AdminBadge :color="getStatusColor(item.status)">
              {{ item.status }}
            </AdminBadge>
          </td>
          <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            {{ item.issuedAt || item.createdAt ? formatDate(item.issuedAt || item.createdAt) : '-' }}
          </td>
           <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
            <AppButton
                variant="ghost"
                size="sm"
                @click="viewInvoice(item)"
              >
                {{ $t('admin.common.view') }}
              </AppButton>
          </td>
        </template>
      </AdminTable>
    </div>

    <InvoiceDetailsModal
        v-model="showInvoiceModal"
        :invoice="selectedInvoice"
    />
  </div>
</template>

<script setup>
import {ref, onMounted, watch, computed} from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AccountingService from '@/services/AccountingService';
import { useI18n } from 'vue-i18n';
import AppButton from '@/components/admin/ui/Button/AppButton.vue';
import AdminInvoiceFilters from '@/components/admin/features/accounting/AdminInvoiceFilters.vue';
import AdminTable from '@/components/admin/ui/Data/AdminTable.vue';
import AdminBadge from '@/components/admin/ui/Data/AdminBadge.vue';
import InvoiceDetailsModal from '@/components/admin/features/accounting/InvoiceDetailsModal.vue';
import { useToast } from 'vue-toastification';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const route = useRoute();

const loading = ref(false);
const invoicesData = ref({ data: [], meta: {} });
const showInvoiceModal = ref(false);
const selectedInvoice = ref(null);

const filters = ref({
  search: route.query.search || '',
  status: route.query.status || '',
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
    search: '',
    status: '',
    page: 1,
  };
  updateUrl();
  fetchInvoices(1);
};

let debounceTimer;
watch(filters, (newFilters) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    updateUrl();
    fetchInvoices(newFilters.page);
  }, 500);
}, { deep: true });

const columns = computed(() => [
  { key: 'invoiceNumber', label: t('admin.accounting.invoices.columns.number') },
  { key: 'user.name', label: t('admin.accounting.invoices.columns.user'), path: 'user.name' },
  { key: 'totalMinor', label: t('admin.accounting.invoices.columns.total') },
  { key: 'status', label: t('admin.accounting.invoices.columns.status') },
  { key: 'issuedAt', label: t('admin.accounting.invoices.columns.issued') },
  { key: 'actions', label: t('admin.accounting.invoices.columns.actions') },
]);

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  filters.value.page = page;
  try {
    const response = await AccountingService.getInvoices(filters.value);
    invoicesData.value = response.data.data;
  } catch (error) {
    toast.error('Failed to load invoices');
  } finally {
    loading.value = false;
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

const getStatusColor = (status) => {
    switch (status) {
        case 'paid': return 'green';
        case 'issued': return 'blue';
        case 'draft': return 'gray';
        case 'cancelled': return 'red';
        default: return 'gray';
    }
};

const viewInvoice = (invoice) => {
    selectedInvoice.value = invoice;
    showInvoiceModal.value = true;
};

onMounted(() => {
  fetchInvoices();
});
</script>
