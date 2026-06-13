<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <OrdersTab
      v-model:search-query="searchQuery"
      v-model:show-filters="showFilters"
      v-model:status-filter="statusFilter"
      v-model:payment-filter="paymentFilter"
      v-model:delivery-filter="deliveryFilter"
      v-model:sort-filter="sortFilter"
      :orders="filteredOrders"
      :is-loading="isLoading"
      :active-filters-count="activeFiltersCount"
      :format-date="formatDate"
      :format-price="formatPrice"
      :get-status-label="getStatusLabel"
      :get-status-class="getStatusClass"
      @reset="resetFilters"
      @export="exportCsv"
      @view="viewOrder"
    />

    <!-- Order Detail Modal -->
    <OrderDetailsModal
      v-if="selectedOrder"
      :model-value="!!selectedOrder"
      :order="selectedOrder"
      :format-date="formatDate"
      :format-price="formatPrice"
      :get-status-label="getStatusLabel"
      :get-status-class="getStatusClass"
      @close="closeModal"
      @update-status="updateStatus"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import { useOrders } from "@/features/orders/composables/useOrders";
import OrdersTab from "@/components/admin/features/orders/OrdersTab.vue";
import OrderDetailsModal from "@/components/admin/features/orders/OrderDetailsModal.vue";

const {
  isLoading,
  searchQuery,
  statusFilter,
  paymentFilter,
  deliveryFilter,
  sortFilter,
  showFilters,
  selectedOrder,
  activeFiltersCount,
  resetFilters,
  fetchOrders,
  filteredOrders,
  getStatusLabel,
  getStatusClass,
  viewOrder,
  closeModal,
  updateStatus,
  formatDate,
  formatPrice,
  exportCsv,
} = useOrders();

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped></style>
