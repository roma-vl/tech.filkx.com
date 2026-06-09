<template>
  <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200/80 dark:border-zinc-800 overflow-hidden divide-y divide-zinc-100 dark:divide-zinc-800">

    <!-- Categories -->
    <div v-if="showCategories" class="p-4">
      <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3">
        Категорія
      </h3>
      <div class="space-y-0.5">
        <a
          :class="!selectedCategory
            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] font-bold'
            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-medium'"
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-sm cursor-pointer transition-all"
          @click.prevent="emit('select-category', '')"
        >
          <span class="material-symbols-outlined text-[16px]">list</span>
          Всі товари
        </a>
        <a
          v-for="cat in categoriesList"
          :key="cat.id"
          :class="selectedCategory === cat.slug
            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] font-bold'
            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-medium'"
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-sm cursor-pointer transition-all"
          @click.prevent="emit('select-category', cat.slug)"
        >
          <span class="material-symbols-outlined text-[16px]">
            {{ cat.slug === 'laptops' ? 'laptop_mac' : cat.slug === 'phones' ? 'smartphone' : cat.slug === 'audio' ? 'headphones' : 'category' }}
          </span>
          {{ cat.name ? cat.name.uk || cat.name.en : '' }}
        </a>
      </div>
    </div>

    <!-- Quick Switches -->
    <div class="p-4 space-y-2">
      <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3">
        Швидкий вибір
      </h3>
      <label class="flex items-center justify-between cursor-pointer px-2.5 py-2 rounded-md border border-transparent hover:border-zinc-100 dark:hover:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-all group">
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">
          В наявності
        </span>
        <input v-model="localStock" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
      </label>
      <label class="flex items-center justify-between cursor-pointer px-2.5 py-2 rounded-md border border-transparent hover:border-zinc-100 dark:hover:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-all group">
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">
          Зі знижкою
        </span>
        <input v-model="localDiscounts" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
      </label>
    </div>

    <!-- Price Range -->
    <div class="p-4">
      <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-4">
        Ціна
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
    <div class="p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Бренд</h3>
        <button
          v-if="localBrands.length"
          class="text-[10px] text-zinc-400 hover:text-rose-500 font-bold uppercase tracking-wider transition-colors flex items-center gap-0.5"
          @click="localBrands = []"
        >
          <span class="material-symbols-outlined text-[11px]">close</span>
          Скинути
        </button>
      </div>
      <div class="space-y-0.5 max-h-44 overflow-y-auto custom-scrollbar pr-1">
        <label
          v-for="brand in brands"
          :key="brand.slug"
          class="flex items-center justify-between group cursor-pointer px-2 py-1.5 rounded-md border border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-100 dark:hover:border-zinc-700 transition-all"
        >
          <div class="flex items-center gap-2.5">
            <input
              v-model="localBrands"
              :value="brand.slug"
              class="w-3.5 h-3.5 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer"
              type="checkbox"
            />
            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
              {{ brand.name }}
            </span>
          </div>
          <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded font-mono">
            {{ brand.count }}
          </span>
        </label>
      </div>
    </div>

    <!-- Dynamic Attributes -->
    <div v-for="attr in dynamicAttributes" :key="attr.id" class="p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
          {{ attr.name ? attr.name.uk || attr.name.en || attr.name : attr.code }}
        </h3>
        <button
          v-if="selectedAttrs[attr.code]"
          class="text-[10px] text-zinc-400 hover:text-rose-500 font-bold uppercase tracking-wider transition-colors flex items-center gap-0.5"
          @click="toggleAttr(attr.code, '')"
        >
          <span class="material-symbols-outlined text-[11px]">close</span>
          Скинути
        </button>
      </div>

      <!-- Color swatches -->
      <div v-if="attr.type === 'color'" class="flex flex-wrap gap-2">
        <button
          v-for="val in attr.values"
          :key="val.id"
          :style="{ backgroundColor: val.value }"
          :class="selectedAttrs[attr.code] === val.value
            ? 'ring-2 ring-offset-2 ring-[#00a046] dark:ring-offset-zinc-900 scale-110 shadow-md'
            : 'border border-zinc-200 dark:border-zinc-700 hover:scale-110'"
          class="w-6 h-6 rounded transition-all focus:outline-none"
          :title="val.value"
          @click="toggleAttr(attr.code, val.value)"
        />
      </div>

      <!-- Text Options -->
      <div v-else class="grid grid-cols-2 gap-1.5">
        <button
          v-for="val in attr.values"
          :key="val.id"
          :class="selectedAttrs[attr.code] === (val.value.uk || val.value.en || val.value)
            ? 'bg-emerald-50 dark:bg-emerald-900/20 border-[#00a046]/40 text-[#00a046] font-bold'
            : 'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-[#00a046]/30 hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800 font-medium'"
          class="py-1.5 px-2 rounded-md border text-xs transition-all truncate"
          @click="toggleAttr(attr.code, val.value.uk || val.value.en || val.value)"
        >
          {{ val.value ? val.value.uk || val.value.en || val.value : '' }}
        </button>
      </div>
    </div>

    <!-- Rating Filter -->
    <div class="p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Рейтинг</h3>
        <button
          v-if="localRating"
          class="text-[10px] text-zinc-400 hover:text-rose-500 font-bold uppercase tracking-wider transition-colors flex items-center gap-0.5"
          @click="localRating = ''"
        >
          <span class="material-symbols-outlined text-[11px]">close</span>
          Скинути
        </button>
      </div>
      <div class="space-y-0.5">
        <label
          v-for="rate in ['4.8', '4.5', '4.0']"
          :key="rate"
          class="flex items-center gap-2.5 cursor-pointer px-2 py-1.5 rounded-md border border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-100 dark:hover:border-zinc-700 transition-all"
        >
          <input
            v-model="localRating"
            type="radio"
            :value="rate"
            class="w-3.5 h-3.5 border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer"
          />
          <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
            від {{ rate }}
            <span class="material-symbols-outlined text-[13px] text-amber-400" style="font-variation-settings: &quot;FILL&quot; 1">star</span>
          </span>
        </label>
      </div>
    </div>

    <!-- Clear All -->
    <div class="p-4">
      <button
        class="w-full py-2 text-xs font-bold text-zinc-500 hover:text-rose-500 border border-zinc-200 dark:border-zinc-700 hover:border-rose-300 dark:hover:border-rose-700 rounded-lg transition-all flex items-center justify-center gap-1.5"
        @click="emit('clear-filters')"
      >
        <span class="material-symbols-outlined text-[15px]">filter_list_off</span>
        Скинути всі фільтри
      </button>
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
  { showCategories: false },
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

const localPriceMin = computed({ get: () => props.priceMin, set: (val) => emit("update:priceMin", val) });
const localPriceMax = computed({ get: () => props.priceMax, set: (val) => emit("update:priceMax", val) });
const localBrands = computed({ get: () => props.selectedBrands, set: (val) => emit("update:selectedBrands", val) });
const localRating = computed({ get: () => props.selectedRating, set: (val) => emit("update:selectedRating", val) });
const localDiscounts = computed({ get: () => props.onlyDiscounts, set: (val) => emit("update:onlyDiscounts", val) });
const localStock = computed({ get: () => props.onlyInStock, set: (val) => emit("update:onlyInStock", val) });

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
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a1a1aa; }
</style>
