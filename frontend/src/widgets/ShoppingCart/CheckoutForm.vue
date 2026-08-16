<template>
  <div
    class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-6"
  >
    <h2
      class="text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-800 pb-3 flex items-center gap-2 font-bold text-lg"
    >
      <span class="material-symbols-outlined text-primary">contact_mail</span>
      {{ t("cart.form.contactInfo") }}
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="customer_name"
          >{{ t("cart.form.fullName") }}</label
        >
        <input
          id="customer_name"
          :value="modelValue.customerName"
          type="text"
          :placeholder="t('cart.form.fullNamePlaceholder')"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          required
          @input="
            updateField(
              'customerName',
              ($event.target as HTMLInputElement).value,
            )
          "
        />
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="customer_phone"
          >{{ t("cart.form.phone") }}</label
        >
        <input
          id="customer_phone"
          :value="modelValue.customerPhone"
          type="tel"
          placeholder="+380991234567"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          required
          @input="
            updateField(
              'customerPhone',
              ($event.target as HTMLInputElement).value,
            )
          "
        />
      </div>
    </div>

    <div class="flex flex-col gap-1.5">
      <label
        class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
        for="customer_email"
        >{{ t("cart.form.email") }}</label
      >
      <input
        id="customer_email"
        :value="modelValue.customerEmail"
        type="email"
        placeholder="ivan@example.com"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
        required
        @input="
          updateField(
            'customerEmail',
            ($event.target as HTMLInputElement).value,
          )
        "
      />
    </div>

    <h2
      class="text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-800 pt-4 pb-3 flex items-center gap-2 font-bold text-lg"
    >
      <span class="material-symbols-outlined text-primary">local_shipping</span>
      {{ t("cart.form.deliveryPaymentDetails") }}
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="delivery_method"
          >{{ t("cart.form.deliveryMethod") }}</label
        >
        <select
          id="delivery_method"
          :value="modelValue.deliveryMethod"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          @change="
            updateField(
              'deliveryMethod',
              ($event.target as HTMLSelectElement).value,
            )
          "
        >
          <option value="nova_poshta">{{ t("cart.form.novaPoshta") }}</option>
          <option value="ukr_poshta">{{ t("cart.form.ukrPoshta") }}</option>
          <option value="courier">{{ t("cart.form.courier") }}</option>
          <option value="pickup">{{ t("cart.form.pickup") }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="payment_method"
          >{{ t("cart.form.paymentMethod") }}</label
        >
        <select
          id="payment_method"
          :value="modelValue.paymentMethod"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          @change="
            updateField(
              'paymentMethod',
              ($event.target as HTMLSelectElement).value,
            )
          "
        >
          <option value="cod">{{ t("cart.paymentMethods.cod") }}</option>
          <option value="card">{{ t("cart.paymentMethods.card") }}</option>
          <option value="bank">{{ t("cart.paymentMethods.bank") }}</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="shipping_city"
          >{{ t("cart.form.city") }}</label
        >
        <input
          id="shipping_city"
          :value="modelValue.shippingCity"
          type="text"
          :placeholder="t('cart.form.cityPlaceholder')"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          required
          @input="
            updateField(
              'shippingCity',
              ($event.target as HTMLInputElement).value,
            )
          "
        />
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="shipping_address"
          >{{ t("cart.form.address") }}</label
        >
        <input
          id="shipping_address"
          :value="modelValue.shippingAddress"
          type="text"
          :placeholder="t('cart.form.addressPlaceholder')"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          required
          @input="
            updateField(
              'shippingAddress',
              ($event.target as HTMLInputElement).value,
            )
          "
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps<{
  modelValue: {
    customerName: string;
    customerPhone: string;
    customerEmail: string;
    shippingCountry: string;
    shippingCity: string;
    shippingAddress: string;
    deliveryMethod: string;
    paymentMethod: string;
  };
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: typeof props.modelValue): void;
}>();

function updateField(key: keyof typeof props.modelValue, val: string) {
  emit("update:modelValue", {
    ...props.modelValue,
    [key]: val,
  });
}
</script>
