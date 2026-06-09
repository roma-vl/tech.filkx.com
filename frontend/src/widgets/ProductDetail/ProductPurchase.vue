<template>
  <div class="flex flex-col gap-5 text-left">
    <!-- Top row: category + actions -->
    <div class="flex items-center justify-between gap-3">
      <span class="text-xs font-bold text-[#00a046] bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/30 px-2.5 py-1 rounded uppercase tracking-wider">
        {{ product.category }}
      </span>
      <div class="flex gap-1.5">
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-rose-500 hover:border-rose-300 dark:hover:border-rose-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all"
          title="В обране"
          @click="cartStore.toggleWishlist(product)"
        >
          <span
            class="material-symbols-outlined text-[19px]"
            :class="{ 'text-rose-500': cartStore.isInWishlist(product.id) }"
            :style="cartStore.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
          >favorite</span>
        </button>
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-[#00a046] hover:border-[#00a046]/30 transition-all"
          :class="{ 'text-[#00a046] border-[#00a046]/40 bg-emerald-50 dark:bg-emerald-900/20': cartStore.isInCompare(product.id) }"
          title="Порівняти"
          @click="cartStore.toggleCompare(product)"
        >
          <span class="material-symbols-outlined text-[19px]">compare_arrows</span>
        </button>
      </div>
    </div>

    <!-- Product name -->
    <div class="space-y-2">
      <h1 class="text-2xl md:text-[28px] font-extrabold text-zinc-900 dark:text-white tracking-tight leading-tight">
        {{ product.name }}
      </h1>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ product.subtitle }}</p>
    </div>

    <!-- Rating + SKU -->
    <div class="flex flex-wrap items-center gap-3 text-sm pb-1 border-b border-zinc-100 dark:border-zinc-800">
      <div class="flex items-center gap-1.5">
        <div class="flex text-amber-400">
          <span
            v-for="star in 5"
            :key="star"
            class="material-symbols-outlined text-[15px]"
            style="font-variation-settings: &quot;FILL&quot; 1"
          >star</span>
        </div>
        <span class="font-semibold text-zinc-700 dark:text-zinc-300 text-xs hover:text-[#00a046] transition-colors cursor-pointer">
          {{ product.reviews }} відгуків
        </span>
      </div>
      <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700" />
      <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
        КОД: FLX-{{ product.productId || product.id }}
      </span>
    </div>

    <!-- Price box -->
    <div class="p-5 bg-zinc-50 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-5">
      <!-- Price -->
      <div class="space-y-1">
        <div class="flex flex-wrap items-end gap-3">
          <span class="text-3xl md:text-4xl font-black tracking-tight text-[#00a046] leading-none">
            {{ formatPrice(product.price) }}
          </span>
          <div v-if="product.oldPrice" class="flex items-center gap-2 mb-0.5">
            <span class="text-base text-zinc-400 line-through font-semibold">{{ formatPrice(product.oldPrice) }}</span>
            <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wide">
              Акція
            </span>
          </div>
        </div>
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
          Безвідсоткова оплата частинами від банків-партнерів
        </p>
      </div>

      <!-- Color -->
      <div v-if="availableColors.length > 0" class="space-y-2.5">
        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
          Колір: <span class="text-zinc-800 dark:text-zinc-200 normal-case tracking-normal">{{ selectedColor }}</span>
        </span>
        <div class="flex gap-2.5">
          <button
            v-for="color in availableColors"
            :key="color"
            :title="color"
            class="w-8 h-8 rounded-full transition-all border-2"
            :class="[
              selectedColor === color ? 'ring-2 ring-offset-2 ring-[#00a046] dark:ring-offset-zinc-900 scale-110' : 'ring-0 hover:scale-105',
              color.toLowerCase().includes('black') || color.toLowerCase().includes('чорн') ? 'bg-[#1c1c1e] border-zinc-600' :
              color.toLowerCase().includes('silver') || color.toLowerCase().includes('срібл') ? 'bg-[#c8c8c8] border-zinc-300' :
              color.toLowerCase().includes('emerald') || color.toLowerCase().includes('зелен') ? 'bg-[#004d40] border-emerald-600' :
              color.toLowerCase().includes('blue') || color.toLowerCase().includes('синь') ? 'bg-[#1a56db] border-blue-600' :
              color.toLowerCase().includes('white') || color.toLowerCase().includes('біл') ? 'bg-white border-zinc-300' :
              'bg-zinc-400 border-zinc-300'
            ]"
            @click="$emit('select-variant', 'color', color)"
          />
        </div>
      </div>

      <!-- Storage/Config -->
      <div v-if="availableStorage.length > 0" class="space-y-2.5">
        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Конфігурація</span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="storage in availableStorage"
            :key="storage"
            :class="selectedStorage === storage
              ? 'border-2 border-[#00a046] text-[#00a046] bg-emerald-50 dark:bg-emerald-900/20 font-bold shadow-sm'
              : 'border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-[#00a046]/50 hover:text-[#00a046] font-semibold'"
            class="py-2 px-3.5 rounded-lg transition-all text-sm min-w-[72px] text-center"
            @click="$emit('select-variant', 'memory', storage)"
          >
            {{ storage }}
          </button>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="space-y-2.5 pt-1">
        <button
          class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3.5 rounded-lg font-bold text-sm shadow-sm hover:shadow-md hover:shadow-emerald-500/15 active:scale-[0.99] transition-all flex items-center justify-center gap-2"
          @click="cartStore.addToCart(product)"
        >
          <span class="material-symbols-outlined text-[19px]">shopping_cart</span>
          Додати в кошик
        </button>
        <button
          class="w-full border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 py-3.5 rounded-lg font-bold text-sm active:scale-[0.99] transition-all flex items-center justify-center gap-2"
          @click="$emit('quick-order')"
        >
          <span class="material-symbols-outlined text-[17px]">bolt</span>
          Швидке замовлення
        </button>
      </div>
    </div>

    <!-- Delivery info -->
    <div class="flex items-stretch gap-0 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden divide-x divide-zinc-200 dark:divide-zinc-800">
      <div class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900">
        <span class="material-symbols-outlined text-[#00a046] text-[22px] shrink-0">inventory_2</span>
        <div>
          <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">В наявності</p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500">Відправка сьогодні</p>
        </div>
      </div>
      <div class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900">
        <span class="material-symbols-outlined text-[#00a046] text-[22px] shrink-0">local_shipping</span>
        <div>
          <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Доставка</p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500">Безкоштовно від 2000 ₴</p>
        </div>
      </div>
      <div class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900">
        <span class="material-symbols-outlined text-[#00a046] text-[22px] shrink-0">assignment_return</span>
        <div>
          <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Повернення</p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500">14 днів</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";

defineProps<{
  product: any;
  availableColors: string[];
  selectedColor: string;
  availableStorage: string[];
  selectedStorage: string;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "select-variant", attributeCode: string, value: string): void;
  (e: "quick-order"): void;
}>();

const cartStore = useCartStore();
</script>
