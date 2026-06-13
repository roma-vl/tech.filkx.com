<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex flex-1 items-center gap-3">
        <!-- Search -->
        <div class="flex-1 max-w-md">
          <AppInput
            :model-value="searchQuery"
            placeholder="Пошук за номером замовлення, клієнтом або email..."
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
          title="Фільтри"
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
          Експорт CSV
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
            label="Статус"
            placeholder="Всі статуси"
            :options="statusOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:statusFilter', $event)"
          />
          <AppSelect
            :model-value="paymentFilter"
            label="Спосіб оплати"
            placeholder="Всі способи"
            :options="paymentOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:paymentFilter', $event)"
          />
          <AppSelect
            :model-value="deliveryFilter"
            label="Спосіб доставки"
            placeholder="Всі способи"
            :options="deliveryOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:deliveryFilter', $event)"
          />
          <AppSelect
            :model-value="sortFilter"
            label="Сортування"
            placeholder="За замовчуванням"
            :options="sortOptions"
            option-value="id"
            option-label="name"
            @update:model-value="$emit('update:sortFilter', $event)"
          />
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-150 dark:border-gray-700">
          <AppButton
            variant="text"
            class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
            @click="$emit('reset')"
          >
            Скинути фільтри
          </AppButton>
        </div>
      </div>
    </transition>

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50/70 dark:bg-gray-700/30 border-b border-gray-200 dark:border-gray-700">
            <tr>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Замовлення
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Дата
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Клієнт
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Сума
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Статус
              </th>
              <th
                scope="col"
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Дії
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
                  {{ order.items?.length || 0 }} товари(ів)
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
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
              <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
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
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="secondary"
                    size="sm"
                    class="font-semibold text-xs bg-gray-50 dark:bg-gray-700/50 hover:bg-[#00a046]/10 text-gray-655 dark:text-gray-400 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors"
                    @click="$emit('view', order)"
                  >
                    Деталі
                  </AppButton>
                </div>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-medium"
              >
                <span v-if="isLoading">Завантаження замовлень...</span>
                <span v-else>Замовлень не знайдено за вашим запитом.</span>
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
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import { ArrowDownTrayIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  orders: {
    type: Array,
    required: true
  },
  isLoading: {
    type: Boolean,
    required: true
  },
  searchQuery: {
    type: String,
    required: true
  },
  showFilters: {
    type: Boolean,
    required: true
  },
  statusFilter: {
    type: String,
    required: true
  },
  paymentFilter: {
    type: String,
    required: true
  },
  deliveryFilter: {
    type: String,
    required: true
  },
  sortFilter: {
    type: String,
    required: true
  },
  activeFiltersCount: {
    type: Number,
    required: true
  },
  formatDate: {
    type: Function,
    required: true
  },
  formatPrice: {
    type: Function,
    required: true
  },
  getStatusLabel: {
    type: Function,
    required: true
  },
  getStatusClass: {
    type: Function,
    required: true
  }
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
  "view"
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
  total: props.orders.length
}));

// Reset page to 1 when filters or search change
watch(
  () => [props.searchQuery, props.statusFilter, props.paymentFilter, props.deliveryFilter, props.sortFilter],
  () => {
    currentPage.value = 1;
  }
);

const statusOptions = [
  { id: "", name: "Всі статуси" },
  { id: "pending_payment", name: "Очікує оплати" },
  { id: "paid", name: "Оплачено" },
  { id: "processing", name: "Обробляється" },
  { id: "packed", name: "Запаковано" },
  { id: "shipped", name: "Відправлено" },
  { id: "delivered", name: "Доставлено" },
  { id: "completed", name: "Виконано" },
  { id: "cancelled", name: "Скасовано" },
  { id: "refunded", name: "Повернуто кошти" },
];

const paymentOptions = [
  { id: "", name: "Всі способи" },
  { id: "cod", name: "Оплата при отриманні" },
  { id: "card", name: "Карткою онлайн" },
  { id: "bank", name: "Банківський переказ" },
];

const deliveryOptions = [
  { id: "", name: "Всі способи" },
  { id: "nova_poshta", name: "Нова Пошта" },
  { id: "ukrposhta", name: "Укрпошта" },
  { id: "courier", name: "Кур'єр" },
  { id: "pickup", name: "Самовивіз" },
];

const sortOptions = [
  { id: "created-desc", name: "Спочатку нові" },
  { id: "created-asc", name: "Спочатку старі" },
  { id: "price-desc", name: "Сума (від більшої)" },
  { id: "price-asc", name: "Сума (від меншої)" },
  { id: "order-asc", name: "Номер (А-Я)" },
  { id: "order-desc", name: "Номер (Я-А)" },
];
</script>
