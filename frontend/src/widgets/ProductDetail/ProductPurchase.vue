<template>
  <div class="flex flex-col gap-6 text-left">
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <span
          class="bg-emerald-500/10 text-[#00a046] font-extrabold text-[10px] px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-500/20"
        >Преміум якість FilkxTech</span>
        <div class="flex gap-2">
          <!-- Wishlist action -->
          <button
            class="p-2.5 border border-zinc-200 dark:border-zinc-800 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-rose-550 transition-all flex items-center justify-center text-zinc-400 dark:text-zinc-500"
            type="button"
            @click="cartStore.toggleWishlist(product)"
          >
            <span
              :class="{ 'text-rose-550': cartStore.isInWishlist(product.id) }"
              :style="
                cartStore.isInWishlist(product.id)
                  ? 'font-variation-settings: \'FILL\' 1;'
                  : ''
              "
              class="material-symbols-outlined text-[20px]"
            >
              favorite
            </span>
          </button>

          <!-- Compare Action -->
          <button
            class="p-2.5 border border-zinc-200 dark:border-zinc-800 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-[#00a046] transition-all flex items-center justify-center text-zinc-400 dark:text-zinc-500"
            type="button"
            :class="{
              'text-[#00a046] border-[#00a046]/40': cartStore.isInCompare(
                product.id
              ),
            }"
            @click="cartStore.toggleCompare(product)"
          >
            <span class="material-symbols-outlined text-[20px]">compare_arrows</span>
          </button>
        </div>
      </div>

      <h1
        class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight font-bold"
      >
        {{ product.name }}
      </h1>
      <p class="text-sm text-zinc-500 font-bold">
        {{ product.subtitle }}
      </p>

      <div class="flex flex-wrap items-center gap-4 py-1 text-xs">
        <div class="flex items-center gap-1.5">
          <div class="flex text-amber-400">
            <span
              v-for="star in 5"
              :key="star"
              class="material-symbols-outlined text-[16px]"
              style="font-variation-settings: &quot;FILL&quot; 1"
            >star</span>
          </div>
          <span
            class="font-extrabold text-zinc-800 dark:text-zinc-200 underline hover:text-[#00a046] transition-colors cursor-pointer"
          >{{ product.reviews }} відгуків</span>
        </div>
        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800" />
        <span
          class="font-bold text-zinc-500 uppercase tracking-wider"
        >КОД: FLX-{{ product.productId || product.id }}</span>
      </div>
    </div>

    <!-- Price box -->
    <div
      class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-6"
    >
      <div class="space-y-2">
        <div class="flex flex-wrap items-baseline gap-3">
          <span
            class="text-3xl font-black tracking-tight text-[#00a046] font-bold"
          >{{ formatPrice(product.price) }}</span>
          <span
            v-if="product.oldPrice"
            class="text-base text-zinc-400 line-through font-extrabold"
          >{{ formatPrice(product.oldPrice) }}</span>
          <span
            v-if="product.oldPrice"
            class="bg-rose-600 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider ml-auto font-bold"
          >Акція</span>
        </div>
        <p class="text-[11px] text-zinc-550 dark:text-zinc-500 font-bold">
          Можлива безвідсоткова оплата частинами від банків-партнерів
        </p>
      </div>

      <!-- Color options selector -->
      <div
        v-if="availableColors.length > 0"
        class="space-y-3"
      >
        <span
          class="block font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500 font-bold"
        >Колір: {{ selectedColor }}</span>
        <div class="flex gap-3">
          <button
            v-for="color in availableColors"
            :key="color"
            :title="color"
            class="w-8 h-8 rounded-full hover:scale-105 transition-transform p-0.5 border"
            :class="[
              selectedColor === color
                ? 'ring-2 ring-[#00a046] ring-offset-2'
                : '',
              color === 'Space Black' ||
              color.toLowerCase().includes('black') ||
              color.toLowerCase().includes('чорн')
                ? 'bg-[#1c1c1c] border-white'
                : color === 'Lunar Silver' ||
                  color.toLowerCase().includes('silver') ||
                  color.toLowerCase().includes('срібл')
                ? 'bg-[#d1d1d1] border-zinc-300'
                : color === 'Deep Emerald' ||
                  color.toLowerCase().includes('emerald') ||
                  color.toLowerCase().includes('зелен')
                ? 'bg-[#004d40] border-white'
                : 'bg-zinc-400 border-zinc-300',
            ]"
            type="button"
            @click="$emit('select-variant', 'color', color)"
          />
        </div>
      </div>

      <!-- Configuration selector -->
      <div
        v-if="availableStorage.length > 0"
        class="space-y-3"
      >
        <span
          class="block font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500 font-bold"
        >Конфігурація</span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="storage in availableStorage"
            :key="storage"
            :class="
              selectedStorage === storage
                ? 'border-2 border-[#00a046] font-black text-[#00a046] bg-emerald-500/5 shadow-sm'
                : 'border border-zinc-200 dark:border-zinc-800 font-bold text-zinc-500 dark:text-zinc-400 hover:border-[#00a046] hover:text-[#00a046]'
            "
            class="py-2 px-3 rounded-lg transition-all text-xs min-w-[70px] font-semibold"
            type="button"
            @click="$emit('select-variant', 'memory', storage)"
          >
            {{ storage }}
          </button>
        </div>
      </div>

      <!-- Add to cart -->
      <div class="pt-2 space-y-3">
        <button
          class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3.5 rounded-lg font-extrabold text-sm shadow-md hover:shadow-lg hover:shadow-emerald-500/10 active:scale-[0.98] transition-all flex items-center justify-center gap-2 uppercase tracking-wider font-bold"
          type="button"
          @click="cartStore.addToCart(product)"
        >
          <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
          Додати в кошик
        </button>
        <button
          class="w-full border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-800 dark:text-zinc-200 py-3.5 rounded-lg font-extrabold text-sm active:scale-[0.98] transition-all uppercase tracking-wider font-bold"
          type="button"
          @click="cartStore.addToCart(product)"
        >
          Швидке замовлення
        </button>
      </div>

      <!-- Delivery info -->
      <div
        class="grid grid-cols-2 gap-px bg-zinc-200 dark:bg-zinc-800 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-800"
      >
        <div
          class="bg-white dark:bg-zinc-900 p-4.5 flex flex-col gap-1 items-center text-center p-3"
        >
          <span
            class="material-symbols-outlined text-[#00a046] text-[22px]"
          >inventory_2</span>
          <span
            class="text-[10px] font-black text-[#00a046] tracking-wider uppercase mt-1 font-bold"
          >В наявності</span>
          <span class="text-[10px] text-zinc-500 dark:text-zinc-500">Відправка сьогодні</span>
        </div>
        <div
          class="bg-white dark:bg-zinc-900 p-4.5 flex flex-col gap-1 items-center text-center p-3"
        >
          <span
            class="material-symbols-outlined text-[#00a046] text-[22px]"
          >local_shipping</span>
          <span
            class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 tracking-wider uppercase mt-1 font-bold"
          >Доставка</span>
          <span class="text-[10px] text-zinc-550 dark:text-zinc-500">Безкоштовно від 2000 ₴</span>
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
}>();

const cartStore = useCartStore();
</script>
