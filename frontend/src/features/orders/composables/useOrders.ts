import { ref, computed } from "vue";
import { orderApi } from "@/shared/services/api/orderApi";
import { Mappers } from "@/shared/utils/mappers";
import { Order } from "@/entities/order/types";

export function useOrders() {
  const orders = ref<Order[]>([]);
  const isLoading = ref(false);
  const searchQuery = ref("");
  const statusFilter = ref("");
  const paymentFilter = ref("");
  const deliveryFilter = ref("");
  const sortFilter = ref("created-desc");
  const showFilters = ref(false);
  const selectedOrder = ref<Order | null>(null);

  const activeFiltersCount = computed(() => {
    let count = 0;
    if (statusFilter.value) count++;
    if (paymentFilter.value) count++;
    if (deliveryFilter.value) count++;
    if (sortFilter.value !== "created-desc") count++;
    return count;
  });

  function resetFilters() {
    statusFilter.value = "";
    paymentFilter.value = "";
    deliveryFilter.value = "";
    sortFilter.value = "created-desc";
    searchQuery.value = "";
  }

  async function fetchOrders() {
    isLoading.value = true;
    try {
      const response = await orderApi.adminGetOrders();
      if (response.data && response.data.status === "success") {
        const rawList = response.data.data || [];
        orders.value = rawList.map((item: any) => Mappers.order(item));
      }
    } catch (error) {
      console.error("Помилка завантаження замовлень:", error);
    } finally {
      isLoading.value = false;
    }
  }

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
        (order) => order.paymentMethod === paymentFilter.value
      );
    }

    // Delivery method filter
    if (deliveryFilter.value) {
      result = result.filter(
        (order) => order.deliveryMethod === deliveryFilter.value
      );
    }

    // Sorting
    if (sortFilter.value) {
      result.sort((a, b) => {
        if (sortFilter.value === "created-desc") {
          return (
            new Date(b.createdAt).getTime() -
            new Date(a.createdAt).getTime()
          );
        }
        if (sortFilter.value === "created-asc") {
          return (
            new Date(a.createdAt).getTime() -
            new Date(b.createdAt).getTime()
          );
        }
        if (sortFilter.value === "price-desc") {
          return (b.totalPrice || 0) - (a.totalPrice || 0);
        }
        if (sortFilter.value === "price-asc") {
          return (a.totalPrice || 0) - (b.totalPrice || 0);
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

  const getStatusLabel = (status: string) => {
    const map: Record<string, string> = {
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

  const getStatusClass = (status: string) => {
    const map: Record<string, string> = {
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

  function viewOrder(order: Order) {
    selectedOrder.value = { ...order };
  }

  function closeModal() {
    selectedOrder.value = null;
  }

  async function updateStatus(status: string) {
    if (!selectedOrder.value) return;
    try {
      const response = await orderApi.adminUpdateOrderStatus(
        selectedOrder.value.id,
        status
      );
      if (response.data && response.data.status === "success") {
        await fetchOrders();
        const updated = orders.value.find((o) => o.id === selectedOrder.value?.id);
        if (updated) {
          selectedOrder.value = { ...updated };
        }
      }
    } catch (error) {
      console.error("Помилка оновлення статусу:", error);
    }
  }

  const formatDate = (dateStr: string) => {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleString("uk-UA", {
      dateStyle: "medium",
      timeStyle: "short",
    });
  };

  const formatPrice = (val: number) => {
    return new Intl.NumberFormat("uk-UA", {
      style: "currency",
      currency: "UAH",
      maximumFractionDigits: 0,
    }).format(val);
  };

  function exportCsv() {
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
      order.totalPrice,
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
      `orders_export_${new Date().toISOString().slice(0, 10)}.csv`
    );
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  return {
    orders,
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
  };
}
