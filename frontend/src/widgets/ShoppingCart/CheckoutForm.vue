<template>
  <div
    class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant space-y-6"
  >
    <h2
      class="font-title-lg text-title-lg text-zinc-900 dark:text-white border-b border-outline-variant pb-3 flex items-center gap-2 font-bold text-lg"
    >
      <span class="material-symbols-outlined text-primary">contact_mail</span>
      Контактна інформація
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="customer_name"
          >ПІБ / Повне ім'я *</label
        >
        <input
          id="customer_name"
          :value="modelValue.customerName"
          type="text"
          placeholder="Іванов Іван"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
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
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="customer_phone"
          >Номер телефону *</label
        >
        <input
          id="customer_phone"
          :value="modelValue.customerPhone"
          type="tel"
          placeholder="+380991234567"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
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
        class="font-label-md text-on-surface-variant text-sm font-semibold"
        for="customer_email"
        >Електронна пошта *</label
      >
      <input
        id="customer_email"
        :value="modelValue.customerEmail"
        type="email"
        placeholder="ivan@example.com"
        class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
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
      class="font-title-lg text-title-lg text-zinc-900 dark:text-white border-b border-outline-variant pt-4 pb-3 flex items-center gap-2 font-bold text-lg"
    >
      <span class="material-symbols-outlined text-primary">local_shipping</span>
      Деталі доставки та оплати
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="delivery_method"
          >Спосіб доставки *</label
        >
        <select
          id="delivery_method"
          :value="modelValue.deliveryMethod"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary dark:text-white"
          @change="
            updateField(
              'deliveryMethod',
              ($event.target as HTMLSelectElement).value,
            )
          "
        >
          <option value="nova_poshta">Нова Пошта (у відділення)</option>
          <option value="ukr_poshta">Укрпошта (у відділення)</option>
          <option value="courier">Адресна доставка кур'єром</option>
          <option value="pickup">Самовивіз із магазину</option>
        </select>
      </div>
      <div class="flex flex-col gap-1.5">
        <label
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="payment_method"
          >Спосіб оплати *</label
        >
        <select
          id="payment_method"
          :value="modelValue.paymentMethod"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary dark:text-white"
          @change="
            updateField(
              'paymentMethod',
              ($event.target as HTMLSelectElement).value,
            )
          "
        >
          <option value="cod">Оплата при отриманні (післяплата)</option>
          <option value="card">Онлайн-оплата (Visa/Mastercard, Monobank)</option>
          <option value="bank">Банківський переказ (на рахунок IBAN)</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="flex flex-col gap-1.5">
        <label
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="shipping_city"
          >Місто *</label
        >
        <input
          id="shipping_city"
          :value="modelValue.shippingCity"
          type="text"
          placeholder="Київ"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
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
          class="font-label-md text-on-surface-variant text-sm font-semibold"
          for="shipping_address"
          >Адреса або номер відділення *</label
        >
        <input
          id="shipping_address"
          :value="modelValue.shippingAddress"
          type="text"
          placeholder="Відділення №14"
          class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
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
