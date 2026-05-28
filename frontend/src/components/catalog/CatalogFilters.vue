<script setup>
import { computed } from 'vue';
import PriceRangeSlider from './PriceRangeSlider.vue';
import { store } from '@/store.js';

const props = defineProps({
  priceMin: {
    type: Number,
    required: true
  },
  priceMax: {
    type: Number,
    required: true
  },
  selectedBrands: {
    type: Array,
    required: true
  },
  selectedRam: {
    type: String,
    required: true
  },
  selectedRating: {
    type: String,
    required: true
  },
  onlyDiscounts: {
    type: Boolean,
    required: true
  },
  onlyInStock: {
    type: Boolean,
    required: true
  },
  products: {
    type: Array,
    required: true
  },
  brands: {
    type: Array,
    required: true
  },
  ramOptions: {
    type: Array,
    required: true
  },
  categoriesList: {
    type: Array,
    default: () => []
  },
  selectedCategory: {
    type: String,
    default: ''
  }
});

const emit = defineEmits([
  'update:priceMin',
  'update:priceMax',
  'update:selectedBrands',
  'update:selectedRam',
  'update:selectedRating',
  'update:onlyDiscounts',
  'update:onlyInStock',
  'clear-filters',
  'select-category'
]);

const localPriceMin = computed({
  get: () => props.priceMin,
  set: (val) => emit('update:priceMin', val)
});

const localPriceMax = computed({
  get: () => props.priceMax,
  set: (val) => emit('update:priceMax', val)
});

const localBrands = computed({
  get: () => props.selectedBrands,
  set: (val) => emit('update:selectedBrands', val)
});

const localRam = computed({
  get: () => props.selectedRam,
  set: (val) => emit('update:selectedRam', val)
});

const localRating = computed({
  get: () => props.selectedRating,
  set: (val) => emit('update:selectedRating', val)
});

const localDiscounts = computed({
  get: () => props.onlyDiscounts,
  set: (val) => emit('update:onlyDiscounts', val)
});

const localStock = computed({
  get: () => props.onlyInStock,
  set: (val) => emit('update:onlyInStock', val)
});
</script>

<template>
  <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm divide-y divide-zinc-100 dark:divide-zinc-800">
    
    <!-- Categories Filter Header/Item -->
    <div class="p-5">
      <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500 mb-3.5">Категорія</h3>
      <div class="space-y-1">
        <a
          :class="!selectedCategory ? 'bg-emerald-500/10 text-[#00a046] font-extrabold' : 'text-zinc-550 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-semibold'"
          class="flex items-center justify-between rounded-lg px-3 py-2 transition-all text-xs cursor-pointer"
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
          :class="selectedCategory === cat.slug ? 'bg-emerald-500/10 text-[#00a046] font-extrabold' : 'text-zinc-550 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-semibold'"
          class="flex items-center justify-between rounded-lg px-3 py-2 transition-all text-xs cursor-pointer"
          @click.prevent="emit('select-category', cat.slug)"
        >
          <span class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">
              {{ cat.slug === 'laptops' ? 'laptop_mac' : cat.slug === 'phones' ? 'smartphone' : cat.slug === 'audio' ? 'headphones' : 'category' }}
            </span>
            <span>{{ cat.name ? (cat.name.uk || cat.name.en) : '' }}</span>
          </span>
        </a>
      </div>
    </div>

    <!-- Quick Switches (Stock & Promo) -->
    <div class="p-5 space-y-3">
      <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500 mb-1">Фільтри швидкого вибору</h3>
      <label class="flex items-center justify-between group cursor-pointer py-0.5">
        <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">Тільки в наявності</span>
        <input v-model="localStock" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" />
      </label>
      <label class="flex items-center justify-between group cursor-pointer py-0.5">
        <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">Акційні пропозиції</span>
        <input v-model="localDiscounts" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" />
      </label>
    </div>

    <!-- Price Range Slider component integration -->
    <div class="p-5">
      <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500 mb-4">Ціновий діапазон</h3>
      <PriceRangeSlider
        v-model:minVal="localPriceMin"
        v-model:maxVal="localPriceMax"
        :min="0"
        :max="200000"
        :step="1000"
      />
    </div>

    <!-- Brand Filter -->
    <div class="p-5">
      <div class="flex items-center justify-between mb-3.5">
        <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500">Бренд</h3>
        <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="localBrands = []">Очистити</button>
      </div>
      <div class="space-y-1.5 max-h-48 overflow-y-auto custom-scrollbar pr-1">
        <label v-for="brand in brands" :key="brand.name" class="flex items-center justify-between group cursor-pointer p-1 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
          <div class="flex items-center gap-2.5">
            <input v-model="localBrands" :value="brand.name" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" type="checkbox" />
            <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">{{ brand.name }}</span>
          </div>
          <span class="text-[10px] font-bold text-zinc-400">{{ brand.count }}</span>
        </label>
      </div>
    </div>

    <!-- RAM Options -->
    <div class="p-5">
      <div class="flex items-center justify-between mb-3.5">
        <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500">Об'єм ОЗУ</h3>
        <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="localRam = ''">Очистити</button>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <button
          v-for="ram in ramOptions"
          :key="ram"
          :class="localRam === ram ? 'bg-[#00a046] text-white shadow-sm' : 'border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-[#00a046] hover:text-[#00a046] bg-zinc-50/50 dark:bg-zinc-900'"
          class="py-2 rounded-lg text-xs font-extrabold transition-all"
          type="button"
          @click="localRam = localRam === ram ? '' : ram"
        >
          {{ ram }}
        </button>
      </div>
    </div>

    <!-- Rating filter -->
    <div class="p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-450 dark:text-zinc-500">Оцінка покупців</h3>
        <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="localRating = ''">Очистити</button>
      </div>
      <div class="space-y-1">
        <label v-for="rate in ['4.8', '4.5', '4.0']" :key="rate" class="flex items-center gap-2 cursor-pointer p-1 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
          <input type="radio" v-model="localRating" :value="rate" class="w-3.5 h-3.5 border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
          <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
            {{ rate }}+ <span class="material-symbols-outlined text-[14px] text-amber-400" style="font-variation-settings: 'FILL' 1;">star</span>
          </span>
        </label>
      </div>
    </div>

  </div>
</template>

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
