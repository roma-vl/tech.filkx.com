<template>
  <article
    :class="viewMode === 'grid'
      ? 'flex-col rounded-lg hover:shadow-lg hover:-translate-y-0.5'
      : 'flex-col sm:flex-row rounded-lg hover:shadow-md'"
    class="group flex bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-200 overflow-hidden relative"
  >
    <!-- Image Section -->
    <div
      :class="viewMode === 'grid'
        ? 'w-full border-b border-zinc-100 dark:border-zinc-800'
        : 'w-full sm:w-56 border-b sm:border-b-0 sm:border-r border-zinc-100 dark:border-zinc-800 shrink-0'"
      class="relative bg-zinc-50 dark:bg-zinc-800/30 flex justify-center items-center aspect-square overflow-hidden"
    >
      <a
        class="w-full h-full flex items-center justify-center p-4 cursor-pointer"
        @click.prevent="cartStore.viewProduct(product)"
      >
        <img
          :alt="product.name"
          class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
          :src="product.image"
        />
      </a>

      <!-- Sale Badge -->
      <span
        v-if="product.badge"
        :class="product.badgeClass"
        class="absolute top-2.5 left-2.5 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider"
      >{{ product.badge }}</span>

      <!-- Wishlist Icon Button -->
      <button
        class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm shadow border border-zinc-200/40 dark:border-zinc-700/40 flex items-center justify-center text-zinc-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all hover:scale-110 active:scale-95"
        type="button"
        @click.stop="cartStore.toggleWishlist(product)"
      >
        <span
          class="material-symbols-outlined text-[17px]"
          :class="{ 'text-rose-500': cartStore.isInWishlist(product.id) }"
          :style="cartStore.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >favorite</span>
      </button>

      <!-- Quick View (hover) -->
      <button
        class="absolute bottom-3 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200 bg-zinc-900/85 hover:bg-zinc-950 text-white font-bold text-[11px] px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-lg backdrop-blur-sm whitespace-nowrap"
        @click.stop="emit('quick-view', product)"
      >
        <span class="material-symbols-outlined text-[14px]">visibility</span>
        Швидкий перегляд
      </button>
    </div>

    <!-- Info Body -->
    <div
      :class="viewMode === 'grid' ? 'p-4 flex-col' : 'p-5 flex-1 flex-col justify-between'"
      class="flex flex-col justify-between flex-1"
    >
      <div class="space-y-2.5">
        <!-- Brand + Rating row -->
        <div class="flex items-center justify-between gap-2">
          <span class="text-[11px] font-extrabold text-[#00a046] uppercase bg-emerald-500/8 dark:bg-emerald-500/10 px-2 py-0.5 rounded">
            {{ product.brand }}
          </span>
          <div class="flex items-center gap-1">
            <div class="flex">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[13px]"
                :class="star <= Math.round(product.rating) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600'"
                :style="star <= Math.round(product.rating) ? 'font-variation-settings: \'FILL\' 1;' : ''"
              >star</span>
            </div>
            <span class="text-[11px] font-semibold text-zinc-400">({{ product.reviews }})</span>
          </div>
        </div>

        <!-- Product Name -->
        <a class="block cursor-pointer" @click.prevent="cartStore.viewProduct(product)">
          <h2
            :class="viewMode === 'grid' ? 'text-sm line-clamp-2 min-h-[40px]' : 'text-[15px]'"
            class="font-bold text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug"
          >
            {{ product.name }}
          </h2>
        </a>

        <!-- Spec Pills -->
        <div class="flex flex-wrap gap-1.5">
          <span
            v-if="product.ram && product.ram !== '16GB'"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">memory</span>
            {{ product.ram }}
          </span>
          <span
            v-if="product.specs?.screen"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">monitor</span>
            {{ product.specs.screen.split(' ')[0] }}
          </span>
          <span
            v-if="product.specs?.storage"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">storage</span>
            {{ product.specs.storage.split(' ')[0] }}
          </span>
          <span
            :class="product.inStock
              ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700/30 text-[#00a046]'
              : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400'"
            class="inline-flex items-center gap-1 border px-2 py-0.5 rounded text-[11px] font-bold"
          >
            <span class="material-symbols-outlined text-[11px]">{{ product.inStock ? 'check_circle' : 'cancel' }}</span>
            {{ product.inStock ? 'В наявності' : 'Немає' }}
          </span>
        </div>

        <!-- Description (list mode) -->
        <p
          v-if="viewMode === 'list'"
          class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2"
        >
          {{ product.description }}
        </p>
      </div>

      <!-- Price + Actions -->
      <div
        :class="viewMode === 'grid'
          ? 'mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800'
          : 'mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 sm:flex sm:items-center sm:gap-4'"
      >
        <!-- Price -->
        <div class="flex-1 mb-3 sm:mb-0">
          <div v-if="product.oldPrice" class="text-xs text-zinc-400 line-through font-semibold">
            {{ formatPrice(product.oldPrice) }}
          </div>
          <div class="text-xl font-black text-[#00a046] tracking-tight">
            {{ formatPrice(product.price) }}
          </div>
        </div>

        <!-- Cart + Compare -->
        <div class="flex items-center gap-2">
          <button
            :disabled="!product.inStock"
            :class="product.inStock
              ? 'bg-[#00a046] hover:bg-[#00b050] text-white active:scale-[0.98]'
              : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'"
            class="flex-1 sm:flex-none font-bold text-xs py-2.5 px-5 rounded-lg transition-all flex items-center justify-center gap-1.5 shadow-sm"
            @click="cartStore.addToCart(product)"
          >
            <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
            {{ product.inStock ? 'Купити' : 'Немає' }}
          </button>

          <!-- Compare -->
          <div class="relative group/cmp">
            <button
              :class="cartStore.isInCompare(product.id)
                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] border-[#00a046]/30'
                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700 hover:text-[#00a046] hover:border-[#00a046]/30'"
              class="w-9 h-9 flex items-center justify-center rounded-lg border transition-all shrink-0"
              @click="cartStore.toggleCompare(product)"
            >
              <span class="material-symbols-outlined text-[17px]">compare_arrows</span>
            </button>
            <div class="absolute bottom-full right-0 mb-1.5 px-2 py-1 bg-zinc-800 dark:bg-zinc-700 text-white text-[11px] rounded opacity-0 pointer-events-none group-hover/cmp:opacity-100 transition-opacity whitespace-nowrap z-10 font-semibold">
              {{ cartStore.isInCompare(product.id) ? 'У порівнянні' : 'Порівняти' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";

defineProps<{
  product: any;
  viewMode?: string;
}>();

const emit = defineEmits(["quick-view"]);
const cartStore = useCartStore();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>
