<template>
  <div class="space-y-6">
    <!-- Filters Panel Widget -->
    <OrderFiltersWidget
      v-model:searchQuery="searchQuery"
      v-model:showFilters="showFilters"
      v-model:statusFilter="statusFilter"
      v-model:paymentFilter="paymentFilter"
      v-model:deliveryFilter="deliveryFilter"
      v-model:sortFilter="sortFilter"
      :active-filters-count="activeFiltersCount"
      @reset="resetFilters"
      @export="exportCsv"
    />

    <!-- Orders Table Widget -->
    <OrderTableWidget
      :orders="filteredOrders"
      :is-loading="isLoading"
      :format-date="formatDate"
      :format-price="formatPrice"
      :get-status-label="getStatusLabel"
      :get-status-class="getStatusClass"
      @view="viewOrder"
    />

    <!-- Order Detail Modal Widget -->
    <OrderDetailsModal
      v-if="selectedOrder"
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
import OrderFiltersWidget from "@/widgets/OrderTable/OrderFiltersWidget.vue";
import OrderTableWidget from "@/widgets/OrderTable/OrderTableWidget.vue";
import OrderDetailsModal from "@/widgets/OrderTable/OrderDetailsModal.vue";

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
