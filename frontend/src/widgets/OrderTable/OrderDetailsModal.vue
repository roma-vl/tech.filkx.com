<template>
  <div
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
            Замовлення #{{ order.orderNumber }}
          </h3>
          <p class="text-xs text-gray-400 mt-1">
            {{ formatDate(order.createdAt) }}
          </p>
        </div>
        <button
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
          @click="$emit('close')"
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
                getStatusClass(order.status),
              ]"
            >
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <select
              :value="order.status"
              class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs px-3 py-2 w-full sm:w-auto focus:ring-primary-500 focus:border-primary-500 transition-colors font-bold"
              @change="$emit('updateStatus', ($event.target as HTMLSelectElement).value)"
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
                {{ order.customerName }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400">
                Email
              </p>
              <p class="font-bold text-gray-800 dark:text-gray-200">
                {{ order.customerEmail }}
              </p>
            </div>
            <div class="sm:col-span-2">
              <p class="text-xs text-gray-400">
                Адреса доставки
              </p>
              <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">
                {{ order.shippingAddress }}
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
              v-for="item in order.items"
              :key="item.id"
              class="flex justify-between items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0 text-sm font-bold text-gray-500"
                >
                  {{ item.quantity || (item as any).qty }}x
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
                {{ formatPrice(item.price * (item.quantity || (item as any).qty)) }}
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
              >{{ formatPrice(order.totalPrice) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div
        class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end"
      >
        <button
          class="px-5 py-2 bg-gradient-to-r from-primary-500 to-purple-600 text-white font-bold rounded-xl text-xs shadow-md transition-colors"
          @click="$emit('close')"
        >
          Закрити
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Order } from "@/entities/order/types";

defineProps<{
  order: Order;
  formatDate: (d: string) => string;
  formatPrice: (p: number) => string;
  getStatusLabel: (s: string) => string;
  getStatusClass: (s: string) => string;
}>();

defineEmits<{
  (e: "close"): void;
  (e: "updateStatus", status: string): void;
}>();
</script>
