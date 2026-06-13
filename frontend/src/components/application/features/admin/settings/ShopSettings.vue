<template>
  <section
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
  >
    <div
      class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-2"
    >
      <ShoppingBagIcon class="w-5 h-5 text-violet-600" />
      <h2 class="font-bold text-lg">
        {{ $t("admin.settings.shop.title") }}
      </h2>
    </div>
    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <AppSelect
          v-model="modelValue.currency"
          :label="$t('admin.settings.shop.currency')"
          :options="currencyOptions"
          option-label="label"
          option-value="value"
        />
        <AppInput
          v-model="modelValue.defaultTaxRate"
          type="number"
          :label="$t('admin.settings.shop.tax_rate')"
          :placeholder="'20'"
        >
          <template #append>
            <span class="text-gray-400 text-sm">%</span>
          </template>
        </AppInput>
        <AppInput
          v-model="modelValue.minOrderAmount"
          type="number"
          :label="$t('admin.settings.shop.min_order')"
          :placeholder="'0'"
        />
        <AppInput
          v-model="modelValue.freeShippingThreshold"
          type="number"
          :label="$t('admin.settings.shop.free_shipping')"
          :placeholder="'0'"
        />
      </div>
      <div
        class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800"
      >
        <AppToggle
          v-model="modelValue.allowGuestCheckout"
          :label="$t('admin.settings.shop.guest_checkout')"
          :description="$t('admin.settings.shop.guest_checkout_desc')"
          active-color="violet"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ShoppingBagIcon } from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppToggle from "@/components/admin/ui/AppToggle.vue";

defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});

const currencyOptions = [
  { label: "UAH — Гривня", value: "UAH" },
  { label: "USD — Dollar", value: "USD" },
  { label: "EUR — Euro", value: "EUR" },
  { label: "GBP — Pound", value: "GBP" },
  { label: "PLN — Złoty", value: "PLN" },
];
</script>
