<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <div class="flex-1 w-full sm:w-auto relative">
        <span
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
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
        </span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Пошук за номером замовлення, клієнтом або email..."
          class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
        >
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <select
          v-model="statusFilter"
          class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500 focus:border-primary-500 transition-colors"
        >
          <option value="">
            Усі статуси
          </option>
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

        <button
          class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-sm hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors"
          @click="exportCsv"
        >
          <svg
            class="w-5 h-5 text-gray-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
            />
          </svg>
          Експорт CSV
        </button>
      </div>
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

const orders = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const statusFilter = ref("");
const selectedOrder = ref(null);

const fetchOrders = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/v1/admin/orders");
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
  return orders.value.filter((order) => {
    const q = searchQuery.value.toLowerCase();
    const matchesSearch =
      (order.orderNumber || "").toLowerCase().includes(q) ||
      (order.customerName || "").toLowerCase().includes(q) ||
      (order.customerEmail || "").toLowerCase().includes(q);
    const matchesStatus =
      !statusFilter.value || order.status === statusFilter.value;

    return matchesSearch && matchesStatus;
  });
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
      `/v1/admin/orders/${selectedOrder.value.id}/status`,
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
