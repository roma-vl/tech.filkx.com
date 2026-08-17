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

    <div
      v-if="showNovaPoshtaAutocomplete"
      class="grid grid-cols-1 md:grid-cols-2 gap-4"
    >
      <div class="flex flex-col gap-1.5 relative">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="shipping_city_search"
          >{{ t("cart.form.city") }}</label
        >
        <input
          id="shipping_city_search"
          v-model="citySearchQuery"
          type="text"
          autocomplete="off"
          :placeholder="t('cart.form.novaPoshtaCitySearchPlaceholder')"
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          required
          @input="onCityInput"
          @focus="isCityDropdownOpen = true"
          @blur="closeCityDropdownSoon"
        />
        <ul
          v-if="
            isCityDropdownOpen &&
            (isCitySearching ||
              cityResults.length ||
              citySearchQuery.trim().length >= 2)
          "
          class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-900 rounded-lg shadow-2xl border border-zinc-200 dark:border-zinc-800 z-20 overflow-hidden max-h-56 overflow-y-auto"
        >
          <li
            v-if="isCitySearching"
            class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400"
          >
            {{ t("cart.form.searching") }}
          </li>
          <template v-else-if="cityResults.length">
            <li
              v-for="city in cityResults"
              :key="city.ref"
              class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer transition-colors"
              @mousedown.prevent="selectCity(city)"
            >
              {{ city.name }}
            </li>
          </template>
          <li v-else class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ t("cart.form.noResults") }}
          </li>
        </ul>
      </div>
      <div class="flex flex-col gap-1.5 relative">
        <label
          class="text-zinc-600 dark:text-zinc-400 text-sm font-semibold"
          for="shipping_warehouse_search"
          >{{ t("cart.form.warehouse") }}</label
        >
        <input
          id="shipping_warehouse_search"
          v-model="warehouseSearchQuery"
          type="text"
          autocomplete="off"
          :disabled="!selectedCityRef"
          :placeholder="
            selectedCityRef
              ? t('cart.form.novaPoshtaWarehouseSearchPlaceholder')
              : t('cart.form.selectCityFirst')
          "
          class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
          required
          @input="onWarehouseInput"
          @focus="isWarehouseDropdownOpen = true"
          @blur="closeWarehouseDropdownSoon"
        />
        <ul
          v-if="isWarehouseDropdownOpen && selectedCityRef"
          class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-zinc-900 rounded-lg shadow-2xl border border-zinc-200 dark:border-zinc-800 z-20 overflow-hidden max-h-56 overflow-y-auto"
        >
          <li
            v-if="isWarehouseSearching"
            class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400"
          >
            {{ t("cart.form.searching") }}
          </li>
          <template v-else-if="warehouseResults.length">
            <li
              v-for="warehouse in warehouseResults"
              :key="warehouse.ref"
              class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer transition-colors"
              @mousedown.prevent="selectWarehouse(warehouse)"
            >
              {{ warehouse.description }}
            </li>
          </template>
          <li v-else class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ t("cart.form.noResults") }}
          </li>
        </ul>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
import { ref, computed, watch, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { deliveryApi } from "@/shared/services/api/deliveryApi";
import { useDebounce } from "@/shared/composables/useDebounce";

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

function updateFields(patch: Partial<typeof props.modelValue>) {
  emit("update:modelValue", {
    ...props.modelValue,
    ...patch,
  });
}

function updateField(key: keyof typeof props.modelValue, val: string) {
  updateFields({ [key]: val } as Partial<typeof props.modelValue>);
}

interface NovaPoshtaCity {
  ref: string;
  name: string;
  area: string;
}

interface NovaPoshtaWarehouse {
  ref: string;
  number: string;
  description: string;
}

// Nova Poshta city/warehouse autocomplete is an enhancement over the plain text
// inputs above - it only replaces them once we've confirmed the backend actually
// has an API key configured, so checkout keeps working unchanged when it doesn't.
const novaPoshtaAvailable = ref(false);

onMounted(async () => {
  try {
    const response = await deliveryApi.getAvailability();
    novaPoshtaAvailable.value = !!response.data?.data?.available;
  } catch {
    novaPoshtaAvailable.value = false;
  }
});

const showNovaPoshtaAutocomplete = computed(
  () =>
    props.modelValue.deliveryMethod === "nova_poshta" &&
    novaPoshtaAvailable.value,
);

const citySearchQuery = ref(props.modelValue.shippingCity);
const debouncedCityQuery = useDebounce(citySearchQuery, 350);
const cityResults = ref<NovaPoshtaCity[]>([]);
const isCitySearching = ref(false);
const isCityDropdownOpen = ref(false);
const selectedCityRef = ref<string | null>(null);

const warehouseSearchQuery = ref(props.modelValue.shippingAddress);
const debouncedWarehouseQuery = useDebounce(warehouseSearchQuery, 350);
const warehouseResults = ref<NovaPoshtaWarehouse[]>([]);
const isWarehouseSearching = ref(false);
const isWarehouseDropdownOpen = ref(false);
const selectedWarehouseRef = ref<string | null>(null);

// Re-sync the local search fields whenever the shopper switches into Nova Poshta -
// they may have typed a plain-text city/address while another delivery method was
// selected, and the autocomplete fields start from that text.
watch(
  () => props.modelValue.deliveryMethod,
  (method) => {
    if (method === "nova_poshta") {
      citySearchQuery.value = props.modelValue.shippingCity;
      warehouseSearchQuery.value = props.modelValue.shippingAddress;
      selectedCityRef.value = null;
      selectedWarehouseRef.value = null;
    }
  },
);

function onCityInput() {
  isCityDropdownOpen.value = true;
  if (selectedCityRef.value) {
    selectedCityRef.value = null;
    warehouseSearchQuery.value = "";
    warehouseResults.value = [];
    selectedWarehouseRef.value = null;
    updateFields({ shippingCity: citySearchQuery.value, shippingAddress: "" });
  } else {
    updateField("shippingCity", citySearchQuery.value);
  }
}

function closeCityDropdownSoon() {
  // Delay so a click on a dropdown item (mousedown) still fires before blur closes it.
  setTimeout(() => (isCityDropdownOpen.value = false), 150);
}

function selectCity(city: NovaPoshtaCity) {
  selectedCityRef.value = city.ref;
  citySearchQuery.value = city.name;
  isCityDropdownOpen.value = false;
  cityResults.value = [];
  warehouseSearchQuery.value = "";
  selectedWarehouseRef.value = null;
  updateFields({ shippingCity: city.name, shippingAddress: "" });
}

watch(debouncedCityQuery, async (query) => {
  if (!showNovaPoshtaAutocomplete.value || selectedCityRef.value) {
    return;
  }

  if (!query || query.trim().length < 2) {
    cityResults.value = [];
    return;
  }

  isCitySearching.value = true;
  try {
    const response = await deliveryApi.searchCities(query.trim());
    cityResults.value = response.data?.data || [];
  } catch {
    cityResults.value = [];
  } finally {
    isCitySearching.value = false;
  }
});

function onWarehouseInput() {
  isWarehouseDropdownOpen.value = true;
  if (selectedWarehouseRef.value) {
    selectedWarehouseRef.value = null;
  }
  updateField("shippingAddress", warehouseSearchQuery.value);
}

function closeWarehouseDropdownSoon() {
  setTimeout(() => (isWarehouseDropdownOpen.value = false), 150);
}

function selectWarehouse(warehouse: NovaPoshtaWarehouse) {
  selectedWarehouseRef.value = warehouse.ref;
  warehouseSearchQuery.value = warehouse.description;
  isWarehouseDropdownOpen.value = false;
  warehouseResults.value = [];
  updateField("shippingAddress", warehouse.description);
}

async function fetchWarehouses(cityRef: string, query: string) {
  isWarehouseSearching.value = true;
  try {
    const response = await deliveryApi.searchWarehouses(
      cityRef,
      query.trim() || undefined,
    );
    warehouseResults.value = response.data?.data || [];
  } catch {
    warehouseResults.value = [];
  } finally {
    isWarehouseSearching.value = false;
  }
}

watch(selectedCityRef, (cityRef) => {
  if (cityRef) {
    fetchWarehouses(cityRef, warehouseSearchQuery.value);
  } else {
    warehouseResults.value = [];
  }
});

watch(debouncedWarehouseQuery, (query) => {
  if (
    !showNovaPoshtaAutocomplete.value ||
    !selectedCityRef.value ||
    selectedWarehouseRef.value
  ) {
    return;
  }
  fetchWarehouses(selectedCityRef.value, query);
});
</script>
