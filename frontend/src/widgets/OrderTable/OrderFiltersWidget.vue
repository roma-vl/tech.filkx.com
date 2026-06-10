<template>
  <div class="space-y-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
      <div class="flex flex-1 items-center gap-3">
        <div class="flex-1 max-w-md">
          <UiInput
            :model-value="searchQuery"
            placeholder="Пошук за номером замовлення, клієнтом або email..."
            @update:model-value="$emit('update:searchQuery', $event)"
          >
            <template #prepend>
              <span class="material-symbols-outlined text-[18px]">search</span>
            </template>
          </UiInput>
        </div>

        <button
          type="button"
          class="relative h-[42px] px-3 rounded-lg border transition-all"
          :class="showFilters
            ? 'border-[#00a046] bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046]'
            : 'border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-500 hover:border-zinc-400'"
          title="Фільтри"
          @click="$emit('update:showFilters', !showFilters)"
        >
          <span class="material-symbols-outlined text-[20px]">tune</span>
          <span
            v-if="activeFiltersCount > 0"
            class="absolute -top-1 -right-1 w-4 h-4 bg-[#00a046] text-white text-[9px] flex items-center justify-center rounded-full font-black"
          >{{ activeFiltersCount }}</span>
        </button>
      </div>

      <div class="flex items-center gap-3">
        <UiButton variant="secondary" @click="$emit('export')">
          <span class="material-symbols-outlined text-[16px]">download</span>
          Експорт CSV
        </UiButton>
      </div>
    </div>

    <transition name="expand">
      <div
        v-if="showFilters"
        class="p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-6"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <UiSelect
            :model-value="statusFilter"
            label="Статус"
            placeholder="Усі статуси"
            :options="statusOptions"
            @update:model-value="$emit('update:statusFilter', $event)"
          />
          <UiSelect
            :model-value="paymentFilter"
            label="Спосіб оплати"
            placeholder="Усі способи"
            :options="paymentOptions"
            @update:model-value="$emit('update:paymentFilter', $event)"
          />
          <UiSelect
            :model-value="deliveryFilter"
            label="Спосіб доставки"
            placeholder="Усі способи"
            :options="deliveryOptions"
            @update:model-value="$emit('update:deliveryFilter', $event)"
          />
          <UiSelect
            :model-value="sortFilter"
            label="Сортування"
            placeholder="За замовчуванням"
            :options="sortOptions"
            @update:model-value="$emit('update:sortFilter', $event)"
          />
        </div>

        <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
          <UiButton variant="secondary" @click="$emit('reset')">
            Скинути фільтри
          </UiButton>
          <UiButton @click="$emit('update:showFilters', false)">
            Застосувати
          </UiButton>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import UiInput from "@/shared/ui/UiInput.vue";
import UiSelect from "@/shared/ui/UiSelect.vue";
import UiButton from "@/shared/ui/UiButton.vue";

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

const statusOptions = [
  { value: "pending_payment", label: "Очікує оплати" },
  { value: "paid", label: "Оплачено" },
  { value: "processing", label: "Обробляється" },
  { value: "packed", label: "Запаковано" },
  { value: "shipped", label: "Відправлено" },
  { value: "delivered", label: "Доставлено" },
  { value: "completed", label: "Виконано" },
  { value: "cancelled", label: "Скасовано" },
  { value: "refunded", label: "Повернуто кошти" },
];

const paymentOptions = [
  { value: "cod", label: "Оплата при отриманні" },
  { value: "card", label: "Карткою онлайн" },
  { value: "bank", label: "Банківський переказ" },
];

const deliveryOptions = [
  { value: "nova_poshta", label: "Нова Пошта" },
  { value: "ukrposhta", label: "Укрпошта" },
  { value: "courier", label: "Кур'єр" },
  { value: "pickup", label: "Самовивіз" },
];

const sortOptions = [
  { value: "created-desc", label: "Спочатку нові" },
  { value: "created-asc", label: "Спочатку старі" },
  { value: "price-desc", label: "Сума (від більшої)" },
  { value: "price-asc", label: "Сума (від меншої)" },
  { value: "order-asc", label: "Номер (А-Я)" },
  { value: "order-desc", label: "Номер (Я-А)" },
];
</script>
