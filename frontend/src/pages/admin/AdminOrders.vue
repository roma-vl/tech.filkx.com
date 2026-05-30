<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="space-y-4">
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
      >
        <div class="flex flex-1 items-center gap-3">
          <div class="flex-1 max-w-md">
            <AppInput
              v-model="searchQuery"
              placeholder="Пошук за номером замовлення, клієнтом або email..."
            >
              <template #prepend>
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </template>
            </AppInput>
          </div>

          <AppButton
            variant="secondary"
            class="!p-2.5 relative h-[46px]"
            :class="{
              'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
                showFilters,
            }"
            title="Фільтри"
            @click="showFilters = !showFilters"
          >
            <svg
              class="w-5 h-5 transition-colors"
              :class="showFilters ? 'text-primary-600' : 'text-gray-500'"
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
              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-primary-500/30 ring-2 ring-white dark:ring-gray-800"
            >
              {{ activeFiltersCount }}
            </span>
          </AppButton>
        </div>

        <div class="flex items-center gap-3">
          <AppButton
            variant="secondary"
            class="flex items-center gap-2 shrink-0 h-[46px]"
            @click="exportCsv"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
              />
            </svg>
            Експорт CSV
          </AppButton>
        </div>
      </div>

      <!-- Toggleable Filters Panel -->
      <transition name="expand">
        <div
          v-if="showFilters"
          class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6 animate-in slide-in-from-top-2 duration-300"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <AppSelect
              v-model="statusFilter"
              label="Статус"
              placeholder="Усі статуси"
              :options="[
                { id: '', name: 'Усі статуси' },
                { id: 'pending_payment', name: 'Очікує оплати' },
                { id: 'paid', name: 'Оплачено' },
                { id: 'processing', name: 'Обробляється' },
                { id: 'packed', name: 'Запаковано' },
                { id: 'shipped', name: 'Відправлено' },
                { id: 'delivered', name: 'Доставлено' },
                { id: 'completed', name: 'Виконано' },
                { id: 'cancelled', name: 'Скасовано' },
                { id: 'refunded', name: 'Повернуто кошти' },
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="paymentFilter"
              label="Спосіб оплати"
              placeholder="Усі способи"
              :options="[
                { id: '', name: 'Усі способи' },
                { id: 'cod', name: 'Оплата при отриманні' },
                { id: 'card', name: 'Карткою онлайн' },
                { id: 'bank', name: 'Банківський переказ' },
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="deliveryFilter"
              label="Спосіб доставки"
              placeholder="Усі способи"
              :options="[
                { id: '', name: 'Усі способи' },
                { id: 'nova_poshta', name: 'Нова Пошта' },
                { id: 'ukrposhta', name: 'Укрпошта' },
                { id: 'courier', name: 'Кур\'єр' },
                { id: 'pickup', name: 'Самовивіз' },
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="sortFilter"
              label="Сортування"
              placeholder="За замовчуванням"
              :options="[
                { id: 'created-desc', name: 'Спочатку нові' },
                { id: 'created-asc', name: 'Спочатку старі' },
                { id: 'price-desc', name: 'Сума (від більшої)' },
                { id: 'price-asc', name: 'Сума (від меншої)' },
                { id: 'order-asc', name: 'Номер (А-Я)' },
                { id: 'order-desc', name: 'Номер (Я-А)' },
              ]"
              option-value="id"
              option-label="name"
            />
          </div>

          <div
            class="flex items-center justify-between pt-6 border-t border-gray-150 dark:border-gray-700"
          >
            <AppButton
              variant="secondary"
              @click="resetFilters"
            >
              Скинути фільтри
            </AppButton>
            <AppButton
              variant="primary"
              @click="showFilters = false"
            >
              Застосувати
            </AppButton>
          </div>
        </div>
      </transition>
    </div>

    <!-- Orders Table -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
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
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="order in filteredOrders"
              :key="order.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  #{{ order.orderNumber }}
                </div>
                <div class="text-xs text-gray-400">
                  {{ order.items.length }} товари(ів)
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
                <div class="text-xs text-gray-400">
                  {{ order.customerEmail }}
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white"
              >
                {{ formatPrice(order.total) }}
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
                  <button
                    class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold transition-all"
                    @click="viewOrder(order)"
                  >
                    Деталі
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                Замовлень не знайдено за вашим запитом.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all"
      >
        <div
          class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50"
        >
          <div>
            <h3
              class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider"
            >
              Замовлення #{{ selectedOrder.orderNumber }}
            </h3>
            <p class="text-xs text-gray-400 mt-1">
              {{ formatDate(selectedOrder.createdAt) }}
            </p>
          </div>
          <button
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
            @click="closeModal"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Status Changer -->
          <div
            class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-150 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
          >
            <div>
              <span class="block text-xs font-bold text-gray-400 uppercase">Поточний статус</span>
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold mt-1',
                  getStatusClass(selectedOrder.status),
                ]"
              >
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <select
                v-model="selectedOrder.status"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs px-3 py-2 w-full sm:w-auto focus:ring-primary-500 focus:border-primary-500 transition-colors font-bold"
                @change="updateStatus"
              >
                <option value="pending_payment">
                  Очікує оплати
                </option>
                <option value="paid">
                  Оплачено
                </option>
                <option value="processing">
                  Обробляється
                </option>
                <option value="packed">
                  Запаковано
                </option>
                <option value="shipped">
                  Відправлено
                </option>
                <option value="delivered">
                  Доставлено
                </option>
                <option value="completed">
                  Виконано
                </option>
                <option value="cancelled">
                  Скасовано
                </option>
                <option value="refunded">
                  Повернуто кошти
                </option>
              </select>
            </div>
          </div>

          <!-- Customer Info -->
          <div>
            <h4
              class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3"
            >
              Інформація про клієнта
            </h4>
            <div
              class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-gray-50 dark:bg-gray-900/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50"
            >
              <div>
                <p class="text-xs text-gray-400">
                  Ім'я
                </p>
                <p class="font-bold text-gray-800 dark:text-gray-200">
                  {{ selectedOrder.customerName }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-400">
                  Email
                </p>
                <p class="font-bold text-gray-800 dark:text-gray-200">
                  {{ selectedOrder.customerEmail }}
                </p>
              </div>
              <div class="sm:col-span-2">
                <p class="text-xs text-gray-400">
                  Адреса доставки
                </p>
                <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">
                  {{ selectedOrder.shippingAddress }}
                </p>
              </div>
            </div>
          </div>

          <!-- Items List -->
          <div>
            <h4
              class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3"
            >
              Товари у замовленні
            </h4>
            <div
              class="divide-y divide-gray-100 dark:divide-gray-700 border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden"
            >
              <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="flex justify-between items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0 text-sm font-bold text-gray-500"
                  >
                    {{ item.qty }}x
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                      {{ item.name }}
                    </p>
                    <p class="text-xs text-gray-400">
                      SKU: {{ item.sku }}
                    </p>
                  </div>
                </div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ formatPrice(item.price * item.qty) }}
                </div>
              </div>
              <div
                class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900/50"
              >
                <span
                  class="text-sm font-black uppercase text-gray-500 dark:text-gray-400"
                >Разом</span>
                <span
                  class="text-lg font-black text-gray-900 dark:text-white"
                >{{ formatPrice(selectedOrder.total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div
          class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end"
        >
          <button
            class="px-5 py-2 bg-gradient-to-r from-primary-500 to-purple-600 text-white font-bold rounded-xl text-xs shadow-md transition-colors"
            @click="closeModal"
          >
            Закрити
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const orders = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const statusFilter = ref("");
const paymentFilter = ref("");
const deliveryFilter = ref("");
const sortFilter = ref("created-desc");
const showFilters = ref(false);
const selectedOrder = ref(null);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (statusFilter.value) count++;
  if (paymentFilter.value) count++;
  if (deliveryFilter.value) count++;
  if (sortFilter.value !== "created-desc") count++;
  return count;
});

const resetFilters = () => {
  statusFilter.value = "";
  paymentFilter.value = "";
  deliveryFilter.value = "";
  sortFilter.value = "created-desc";
  searchQuery.value = "";
};

const fetchOrders = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/admin/orders");
    if (response.data && response.data.status === "success") {
      orders.value = response.data.data;
    }
  } catch (error) {
    console.error("Помилка завантаження замовлень:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
});

const filteredOrders = computed(() => {
  let result = [...orders.value];

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter((order) => {
      return (
        (order.orderNumber || "").toLowerCase().includes(q) ||
        (order.customerName || "").toLowerCase().includes(q) ||
        (order.customerEmail || "").toLowerCase().includes(q) ||
        (order.customerPhone || "").toLowerCase().includes(q)
      );
    });
  }

  // Status filter
  if (statusFilter.value) {
    result = result.filter((order) => order.status === statusFilter.value);
  }

  // Payment method filter
  if (paymentFilter.value) {
    result = result.filter(
      (order) => order.paymentMethod === paymentFilter.value,
    );
  }

  // Delivery method filter
  if (deliveryFilter.value) {
    result = result.filter(
      (order) => order.deliveryMethod === deliveryFilter.value,
    );
  }

  // Sorting
  if (sortFilter.value) {
    result.sort((a, b) => {
      if (sortFilter.value === "created-desc") {
        return (
          new Date(b.createdAt || b.created_at) -
          new Date(a.createdAt || a.created_at)
        );
      }
      if (sortFilter.value === "created-asc") {
        return (
          new Date(a.createdAt || a.created_at) -
          new Date(b.createdAt || b.created_at)
        );
      }
      if (sortFilter.value === "price-desc") {
        return (b.total || 0) - (a.total || 0);
      }
      if (sortFilter.value === "price-asc") {
        return (a.total || 0) - (b.total || 0);
      }
      if (sortFilter.value === "order-asc") {
        return (a.orderNumber || "").localeCompare(b.orderNumber || "");
      }
      if (sortFilter.value === "order-desc") {
        return (b.orderNumber || "").localeCompare(a.orderNumber || "");
      }
      return 0;
    });
  }

  return result;
});

const getStatusLabel = (status) => {
  const map = {
    pending_payment: "Очікує оплати",
    paid: "Оплачено",
    processing: "Обробляється",
    packed: "Запаковано",
    shipped: "Відправлено",
    delivered: "Доставлено",
    completed: "Виконано",
    cancelled: "Скасовано",
    refunded: "Повернуто кошти",
  };
  return map[status] || status;
};

const getStatusClass = (status) => {
  const map = {
    pending_payment:
      "bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400",
    paid: "bg-emerald-100 text-emerald-850 dark:bg-emerald-950/30 dark:text-emerald-450",
    processing:
      "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
    packed: "bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400",
    shipped:
      "bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400",
    delivered:
      "bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400",
    completed: "bg-emerald-600 text-white dark:bg-emerald-600 dark:text-white",
    cancelled: "bg-red-105 text-red-800 dark:bg-red-900/30 dark:text-red-400",
    refunded: "bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
  };
  return map[status] || "bg-gray-100 text-gray-800";
};

const viewOrder = (order) => {
  selectedOrder.value = { ...order };
};

const closeModal = () => {
  selectedOrder.value = null;
};

const updateStatus = async () => {
  if (!selectedOrder.value) return;
  try {
    const response = await api.put(
      `/admin/orders/${selectedOrder.value.id}/status`,
      {
        status: selectedOrder.value.status,
      },
    );
    if (response.data && response.data.status === "success") {
      await fetchOrders();
      const updated = orders.value.find((o) => o.id === selectedOrder.value.id);
      if (updated) {
        selectedOrder.value = { ...updated };
      }
    }
  } catch (error) {
    console.error("Помилка оновлення статусу:", error);
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleString("uk-UA", {
    dateStyle: "medium",
    timeStyle: "short",
  });
};

const formatPrice = (val) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(val);
};

const exportCsv = () => {
  if (filteredOrders.value.length === 0) {
    alert("Немає замовлень для експорту");
    return;
  }
  const headers = [
    "Order Number",
    "Date",
    "Customer Name",
    "Customer Email",
    "Total (UAH)",
    "Status",
  ];
  const rows = filteredOrders.value.map((order) => [
    order.orderNumber,
    formatDate(order.createdAt),
    order.customerName,
    order.customerEmail,
    order.total,
    getStatusLabel(order.status),
  ]);

  const csvContent =
    "data:text/csv;charset=utf-8," +
    [
      headers.join(","),
      ...rows.map((e) => e.map((val) => `"${val}"`).join(",")),
    ].join("\n");

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute(
    "download",
    `orders_export_${new Date().toISOString().slice(0, 10)}.csv`,
  );
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>
