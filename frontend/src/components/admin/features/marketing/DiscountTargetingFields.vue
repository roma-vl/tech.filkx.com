<template>
  <div class="p-4 bg-gray-50/50 dark:bg-zinc-900/50 rounded-xl border border-gray-200 dark:border-zinc-800 space-y-4">
    <div>
      <span class="text-sm font-semibold text-gray-900 dark:text-white block">
        Цільова аудиторія
      </span>
      <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">
        Нічого не обрано — знижка діє на весь каталог
      </span>
    </div>

    <div>
      <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
        Категорії
      </label>
      <div
        v-if="categories.length"
        class="max-h-36 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
      >
        <label
          v-for="category in categories"
          :key="category.id"
          class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer select-none"
        >
          <input
            v-model="categoryIds"
            type="checkbox"
            :value="category.id"
            class="w-3.5 h-3.5 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600"
          >
          {{ category.nameUk }}
        </label>
      </div>
      <p
        v-else
        class="text-xs text-gray-400 italic"
      >
        Немає доступних категорій.
      </p>
    </div>

    <div>
      <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
        Товари
      </label>
      <div class="flex gap-2">
        <AppSelect
          v-model="productToAdd"
          placeholder="Додати товар..."
          :options="availableProducts"
          option-value="id"
          option-label="nameUk"
          class="flex-1"
        />
        <AppButton
          type="button"
          variant="secondary"
          class="whitespace-nowrap"
          :disabled="!productToAdd"
          @click="addProduct"
        >
          Додати
        </AppButton>
      </div>
      <div
        v-if="selectedProducts.length"
        class="flex flex-wrap gap-2 mt-3"
      >
        <span
          v-for="product in selectedProducts"
          :key="product.id"
          class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full text-xs font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400"
        >
          {{ product.nameUk }}
          <button
            type="button"
            class="hover:text-red-500"
            @click="removeProduct(product.id)"
          >
            &times;
          </button>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const props = defineProps({
  modelValue: {
    type: Object,
    required: true, // { categoryIds: number[], productIds: number[] }
  },
  categories: {
    type: Array,
    default: () => [],
  },
  products: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:modelValue"]);

const categoryIds = computed({
  get: () => props.modelValue.categoryIds || [],
  set: (val) => emit("update:modelValue", { ...props.modelValue, categoryIds: val }),
});

const productIds = computed({
  get: () => props.modelValue.productIds || [],
  set: (val) => emit("update:modelValue", { ...props.modelValue, productIds: val }),
});

const productToAdd = ref("");

const selectedProducts = computed(() =>
  props.products.filter((p) => productIds.value.includes(p.id)),
);

const availableProducts = computed(() =>
  props.products.filter((p) => !productIds.value.includes(p.id)),
);

const addProduct = () => {
  if (!productToAdd.value) return;
  productIds.value = [...productIds.value, Number(productToAdd.value)];
  productToAdd.value = "";
};

const removeProduct = (id) => {
  productIds.value = productIds.value.filter((pid) => pid !== id);
};
</script>
