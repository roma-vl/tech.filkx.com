<template>
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
            v-for="order in orders"
            :key="order.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-bold text-gray-900 dark:text-white">
                #{{ order.orderNumber }}
              </div>
              <div class="text-xs text-gray-400">
                {{ order.items?.length || 0 }} товари(ів)
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
                <button
                  class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold transition-all"
                  @click="$emit('view', order)"
                >
                  Деталі
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="orders.length === 0">
            <td
              colspan="6"
              class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
            >
              <span v-if="isLoading">Завантаження замовлень...</span>
              <span v-else>Замовлень не знайдено за вашим запитом.</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Order } from "@/entities/order/types";

defineProps<{
  orders: Order[];
  isLoading: boolean;
  formatDate: (d: string) => string;
  formatPrice: (p: number) => string;
  getStatusLabel: (s: string) => string;
  getStatusClass: (s: string) => string;
}>();

defineEmits<{
  (e: "view", order: Order): void;
}>();
</script>
