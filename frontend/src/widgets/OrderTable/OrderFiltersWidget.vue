<template>
  <div class="space-y-4">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <div class="flex flex-1 items-center gap-3">
        <div class="flex-1 max-w-md">
          <AppInput
            :model-value="searchQuery"
            placeholder="Пошук за номером замовлення, клієнтом або email..."
            @update:model-value="$emit('update:searchQuery', $event)"
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
          @click="$emit('update:showFilters', !showFilters)"
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
          @click="$emit('export')"
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
        class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <AppSelect
            :model-value="statusFilter"
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
            @update:model-value="$emit('update:statusFilter', $event)"
          />

          <AppSelect
            :model-value="paymentFilter"
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
            @update:model-value="$emit('update:paymentFilter', $event)"
          />

          <AppSelect
            :model-value="deliveryFilter"
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
            @update:model-value="$emit('update:deliveryFilter', $event)"
          />

          <AppSelect
            :model-value="sortFilter"
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
            @update:model-value="$emit('update:sortFilter', $event)"
          />
        </div>

        <div
          class="flex items-center justify-between pt-6 border-t border-gray-150 dark:border-gray-700"
        >
          <AppButton
            variant="secondary"
            @click="$emit('reset')"
          >
            Скинути фільтри
          </AppButton>
          <AppButton
            variant="primary"
            @click="$emit('update:showFilters', false)"
          >
            Застосувати
          </AppButton>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { AppInput, AppSelect, AppButton } from "@/shared/ui";

defineProps<{
  searchQuery: string;
  showFilters: boolean;
  statusFilter: string;
  paymentFilter: string;
  deliveryFilter: string;
  sortFilter: string;
  activeFiltersCount: number;
}>();

defineEmits<{
  (e: "update:searchQuery", value: string): void;
  (e: "update:showFilters", value: boolean): void;
  (e: "update:statusFilter", value: string): void;
  (e: "update:paymentFilter", value: string): void;
  (e: "update:deliveryFilter", value: string): void;
  (e: "update:sortFilter", value: string): void;
  (e: "reset"): void;
  (e: "export"): void;
}>();
</script>
