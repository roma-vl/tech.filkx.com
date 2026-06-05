<template>
  <div
    class="bg-white dark:bg-zinc-955 rounded-lg border border-zinc-200/80 dark:border-zinc-800/80 shadow-md divide-y divide-zinc-150/60 dark:divide-zinc-800/60 backdrop-blur-md"
  >
    <!-- Categories Filter Header/Item -->
    <div v-if="showCategories" class="p-5">
      <h3
        class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 mb-3.5 flex items-center gap-1.5"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Категорія
      </h3>
      <div class="space-y-1">
        <a
          :class="
            !selectedCategory
              ? 'bg-emerald-500/10 text-[#00a046] font-extrabold'
              : 'text-zinc-555 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900 font-semibold'
          "
          class="flex items-center justify-between px-3 py-2 transition-all text-[15px] font-semibold cursor-pointer rounded-md"
          @click.prevent="emit('select-category', '')"
        >
          <span class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">list</span>
            <span>Всі товари</span>
          </span>
        </a>
        <a
          v-for="cat in categoriesList"
          :key="cat.id"
          :class="
            selectedCategory === cat.slug
              ? 'bg-emerald-500/10 text-[#00a046] font-extrabold'
              : 'text-zinc-555 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900 font-semibold'
          "
          class="flex items-center justify-between px-3 py-2 transition-all text-[15px] font-semibold cursor-pointer rounded-md"
          @click.prevent="emit('select-category', cat.slug)"
        >
          <span class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">
              {{
                cat.slug === "laptops"
                  ? "laptop_mac"
                  : cat.slug === "phones"
                    ? "smartphone"
                    : cat.slug === "audio"
                      ? "headphones"
                      : "category"
              }}
            </span>
            <span>{{ cat.name ? cat.name.uk || cat.name.en : "" }}</span>
          </span>
        </a>
      </div>
    </div>

    <!-- Quick Switches (Stock & Promo) -->
    <div class="p-5 space-y-3">
      <h3
        class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 mb-3 flex items-center gap-1.5"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Швидкий вибір
      </h3>
      <label
        class="flex items-center justify-between group cursor-pointer px-3 py-2 rounded-md bg-zinc-50/50 dark:bg-zinc-900/30 border border-zinc-150/40 dark:border-zinc-800/40 hover:border-emerald-500/25 hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 transition-all duration-200"
      >
        <span
          class="text-[15px] font-bold text-zinc-700 dark:text-zinc-250 group-hover:text-[#00a046] transition-colors"
          >Тільки в наявності</span
        >
        <input
          v-model="localStock"
          type="checkbox"
          class="w-4 h-4 rounded border-zinc-350 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer transition-colors"
        />
      </label>
      <label
        class="flex items-center justify-between group cursor-pointer px-3 py-2 rounded-md bg-zinc-50/50 dark:bg-zinc-900/30 border border-zinc-150/40 dark:border-zinc-800/40 hover:border-emerald-500/25 hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 transition-all duration-200"
      >
        <span
          class="text-[15px] font-bold text-zinc-700 dark:text-zinc-250 group-hover:text-[#00a046] transition-colors"
          >Акційні пропозиції</span
        >
        <input
          v-model="localDiscounts"
          type="checkbox"
          class="w-4 h-4 rounded border-zinc-355 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer transition-colors"
        />
      </label>
    </div>

    <!-- Price Range Slider component integration -->
    <div class="p-5">
      <h3
        class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 mb-4 flex items-center gap-1.5"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Ціновий діапазон
      </h3>
      <PriceRangeSlider
        v-model:min-val="localPriceMin"
        v-model:max-val="localPriceMax"
        :min="0"
        :max="200000"
        :step="1000"
      />
    </div>

    <!-- Brand Filter -->
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <h3
          class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 flex items-center gap-1.5"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          Бренд
        </h3>
        <button
          v-if="localBrands.length > 0"
          class="text-[9px] text-zinc-400 hover:text-rose-500 font-bold transition-colors uppercase tracking-wider flex items-center gap-0.5"
          type="button"
          @click="localBrands = []"
        >
          <span class="material-symbols-outlined text-[10px]">close</span>
          Очистити
        </button>
      </div>
      <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar pr-1">
        <label
          v-for="brand in brands"
          :key="brand.slug"
          class="flex items-center justify-between group cursor-pointer px-2 py-1.5 rounded border border-transparent hover:border-emerald-500/10 hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 transition-all duration-200"
        >
          <div class="flex items-center gap-2.5">
            <input
              v-model="localBrands"
              :value="brand.slug"
              class="w-4 h-4 rounded border-zinc-350 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer transition-colors"
              type="checkbox"
            />
            <span
              class="text-[15px] font-bold text-zinc-700 dark:text-zinc-250 group-hover:text-[#00a046] transition-colors"
              >{{ brand.name }}</span
            >
          </div>
          <span
            class="text-[9px] font-black text-zinc-450 dark:text-zinc-500 bg-zinc-150/40 dark:bg-zinc-800/40 group-hover:bg-[#00a046]/10 group-hover:text-[#00a046] px-1.5 py-0.5 rounded font-mono transition-colors"
            >{{ brand.count }}</span
          >
        </label>
      </div>
    </div>

    <!-- Dynamic Attributes Filter -->
    <div v-for="attr in dynamicAttributes" :key="attr.id" class="p-5">
      <div class="flex items-center justify-between mb-4">
        <h3
          class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 flex items-center gap-1.5"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          {{
            attr.name ? attr.name.uk || attr.name.en || attr.name : attr.code
          }}
        </h3>
        <button
          v-if="selectedAttrs[attr.code]"
          class="text-[9px] text-zinc-400 hover:text-rose-500 font-bold transition-colors uppercase tracking-wider flex items-center gap-0.5"
          type="button"
          @click="toggleAttr(attr.code, '')"
        >
          <span class="material-symbols-outlined text-[10px]">close</span>
          Очистити
        </button>
      </div>

      <!-- Color swatches -->
      <div v-if="attr.type === 'color'" class="flex flex-wrap gap-2.5">
        <button
          v-for="val in attr.values"
          :key="val.id"
          :style="{ backgroundColor: val.value }"
          :class="[
            selectedAttrs[attr.code] === val.value
              ? 'ring-2 ring-offset-2 ring-emerald-500 dark:ring-offset-zinc-950 scale-110 shadow-lg shadow-emerald-500/20'
              : 'border border-zinc-200 dark:border-zinc-800 hover:scale-105 hover:border-zinc-400 dark:hover:border-zinc-650',
          ]"
          class="w-6 h-6 rounded-sm transition-all focus:outline-none"
          type="button"
          :title="val.value"
          @click="toggleAttr(attr.code, val.value)"
        />
      </div>

      <!-- Text/Select Options -->
      <div v-else class="grid grid-cols-2 gap-2">
        <button
          v-for="val in attr.values"
          :key="val.id"
          :class="
            selectedAttrs[attr.code] ===
            (val.value.uk || val.value.en || val.value)
              ? 'bg-emerald-500/10 border border-emerald-500/60 text-[#00a046] shadow-sm'
              : 'border border-zinc-200 dark:border-zinc-855 text-zinc-650 dark:text-zinc-300 hover:border-emerald-500/30 hover:text-[#00a046] hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 bg-zinc-50/30 dark:bg-zinc-900/30'
          "
          class="py-2 px-1.5 rounded text-sm font-bold transition-all truncate"
          type="button"
          @click="
            toggleAttr(attr.code, val.value.uk || val.value.en || val.value)
          "
        >
          {{ val.value ? val.value.uk || val.value.en || val.value : "" }}
        </button>
      </div>
    </div>

    <!-- Rating filter -->
    <div class="p-5">
      <div class="flex items-center justify-between mb-4">
        <h3
          class="font-black text-xs uppercase tracking-wider text-zinc-650 dark:text-zinc-350 flex items-center gap-1.5"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          Оцінка покупців
        </h3>
        <button
          v-if="localRating"
          class="text-[9px] text-zinc-450 hover:text-rose-500 font-bold transition-colors uppercase tracking-wider flex items-center gap-0.5"
          type="button"
          @click="localRating = ''"
        >
          <span class="material-symbols-outlined text-[10px]">close</span>
          Очистити
        </button>
      </div>
      <div class="space-y-1">
        <label
          v-for="rate in ['4.8', '4.5', '4.0']"
          :key="rate"
          class="flex items-center gap-2.5 cursor-pointer px-2 py-1.5 rounded border border-transparent hover:border-emerald-500/10 hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 transition-all duration-200"
        >
          <input
            v-model="localRating"
            type="radio"
            :value="rate"
            class="w-3.5 h-3.5 border-zinc-350 text-[#00a046] focus:ring-0 cursor-pointer transition-colors"
          />
          <span
            class="text-[15px] font-bold text-zinc-700 dark:text-zinc-300 flex items-center gap-1"
          >
            {{ rate }}+
            <span
              class="material-symbols-outlined text-[14px] text-amber-400"
              style="font-variation-settings: &quot;FILL&quot; 1"
              >star</span
            >
          </span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import PriceRangeSlider from "./PriceRangeSlider.vue";

interface BrandInfo {
  name: string;
  slug: string;
  count: number;
}

const props = withDefaults(
  defineProps<{
    priceMin: number;
    priceMax: number;
    selectedBrands: string[];
    selectedAttrs: Record<string, string>;
    selectedRating: string;
    onlyDiscounts: boolean;
    onlyInStock: boolean;
    products: any[];
    brands: BrandInfo[];
    dynamicAttributes: any[];
    categoriesList: any[];
    selectedCategory: string;
    showCategories?: boolean;
  }>(),
  {
    showCategories: false,
  },
);

const emit = defineEmits([
  "update:priceMin",
  "update:priceMax",
  "update:selectedBrands",
  "update:selectedAttrs",
  "update:selectedRating",
  "update:onlyDiscounts",
  "update:onlyInStock",
  "clear-filters",
  "select-category",
]);

const localPriceMin = computed({
  get: () => props.priceMin,
  set: (val) => emit("update:priceMin", val),
});

const localPriceMax = computed({
  get: () => props.priceMax,
  set: (val) => emit("update:priceMax", val),
});

const localBrands = computed({
  get: () => props.selectedBrands,
  set: (val) => emit("update:selectedBrands", val),
});

const localRating = computed({
  get: () => props.selectedRating,
  set: (val) => emit("update:selectedRating", val),
});

const localDiscounts = computed({
  get: () => props.onlyDiscounts,
  set: (val) => emit("update:onlyDiscounts", val),
});

const localStock = computed({
  get: () => props.onlyInStock,
  set: (val) => emit("update:onlyInStock", val),
});

const toggleAttr = (code: string, value: string) => {
  const current = { ...props.selectedAttrs };
  if (!value || current[code] === value) {
    delete current[code];
  } else {
    current[code] = value;
  }
  emit("update:selectedAttrs", current);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d4d4d8;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a1a1aa;
}
</style>
