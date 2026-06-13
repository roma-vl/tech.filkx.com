<template>
  <AppModal
    :model-value="modelValue"
    :title="`Замовлення #${order.orderNumber}`"
    max-width="2xl"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-6">
      <!-- Status Changer -->
      <div class="bg-gray-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">Поточний статус</span>
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
          <AppSelect
            :model-value="order.status"
            :options="statusOptions"
            option-value="id"
            option-label="name"
            class="w-full sm:w-48 font-bold text-xs"
            @update:model-value="$emit('updateStatus', $event)"
          />
        </div>
      </div>

      <!-- Customer Info -->
      <div>
        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">
          Інформація про клієнта
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-gray-50 dark:bg-zinc-900/30 p-4 rounded-xl border border-gray-100 dark:border-zinc-800">
          <div>
            <p class="text-xs text-gray-400 dark:text-gray-500">
              Ім'я
            </p>
            <p class="font-bold text-gray-800 dark:text-gray-200">
              {{ order.customerName }}
            </p>
          </div>
          <div>
            <p class="text-xs text-gray-400 dark:text-gray-500">
              Email
            </p>
            <p class="font-bold text-gray-800 dark:text-gray-200">
              {{ order.customerEmail }}
            </p>
          </div>
          <div class="sm:col-span-2">
            <p class="text-xs text-gray-400 dark:text-gray-500">
              Адреса доставки
            </p>
            <p class="font-medium text-gray-805 dark:text-gray-200 mt-0.5">
              {{ order.shippingAddress }}
            </p>
          </div>
        </div>
      </div>

      <!-- Items List -->
      <div>
        <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">
          Товари у замовленні
        </h4>
        <div class="divide-y divide-gray-100 dark:divide-gray-700 border border-gray-150 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-750 flex items-center justify-center shrink-0 text-sm font-bold text-gray-500 dark:text-gray-450">
                {{ item.quantity || item['qty'] }}x
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ item.name }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                  SKU: {{ item.sku }}
                </p>
              </div>
            </div>
            <div class="text-sm font-bold text-gray-900 dark:text-white">
              {{ formatPrice(item.price * (item.quantity || item['qty'])) }}
            </div>
          </div>
          <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-zinc-900/50">
            <span class="text-sm font-bold uppercase text-gray-500 dark:text-gray-450">Разом</span>
            <span class="text-lg font-black text-gray-900 dark:text-white">
              {{ formatPrice(order.totalPrice) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer action -->
    <template #footer>
      <div class="flex justify-end w-full">
        <AppButton
          variant="primary"
          class="!px-5 !py-2 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
          @click="$emit('close')"
        >
          Закрити
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup lang="ts">
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  order: {
    type: Object,
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

defineEmits(["close", "updateStatus"]);

const statusOptions = [
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
</script>
