<template>
  <article
    :class="viewMode === 'grid'
      ? 'flex-col border-r border-b border-zinc-200 dark:border-zinc-800 hover:z-10 hover:bg-zinc-50 dark:hover:bg-zinc-900/60'
      : 'flex-col sm:flex-row rounded-md border border-transparent hover:border-zinc-200 dark:hover:border-zinc-800 hover:bg-white dark:hover:bg-zinc-900 hover:-translate-y-0.5'"
    class="group flex relative hover:shadow-lg transition-all duration-200 overflow-hidden"
  >
    <!-- Image Section -->
    <div
      :class="viewMode === 'grid'
        ? 'w-full'
        : 'w-full sm:w-56 shrink-0'"
      class="relative flex justify-center items-center aspect-square overflow-hidden"
    >
      <router-link
        :to="{ name: 'product-detail', params: { id: product.slug || product.id } }"
        class="w-full h-full flex items-center justify-center p-3"
      >
        <img
          :alt="product.name"
          class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
          :src="product.image"
        />
      </router-link>

      <!-- Discount Badge -->
      <span
        v-if="product.badge"
        :class="product.badgeClass"
        class="absolute top-2.5 left-2.5 text-white text-[11px] font-black px-1.5 py-0.5 rounded"
      >{{ product.badge }}</span>

      <!-- Wishlist Icon Button -->
      <button
        class="absolute top-1 right-1 w-8 h-8 flex items-center justify-center text-zinc-400 dark:text-zinc-500 hover:text-rose-500 transition-all hover:scale-110 active:scale-95"
        type="button"
        @click.stop="cartStore.toggleWishlist(product)"
      >
        <span
          class="material-symbols-outlined text-[19px] drop-shadow-sm"
          :class="{ 'text-rose-500': cartStore.isInWishlist(product.id) }"
          :style="cartStore.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >favorite</span>
      </button>

      <!-- Quick View (hover) -->
      <button
        class="absolute bottom-3 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0 transition-all duration-200 bg-zinc-900/85 hover:bg-zinc-950 text-white font-bold text-[11px] px-4 py-2 rounded-md flex items-center gap-1.5 shadow-lg backdrop-blur-sm whitespace-nowrap"
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
          <span
            v-if="product.brand"
            class="text-[11px] font-extrabold text-[#00a046] uppercase bg-emerald-500/8 dark:bg-emerald-500/10 px-2 py-0.5 rounded"
          >
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
        <router-link :to="{ name: 'product-detail', params: { id: product.slug || product.id } }" class="block">
          <h2
            :class="viewMode === 'grid' ? 'text-sm line-clamp-2 min-h-[40px]' : 'text-[15px]'"
            class="font-medium text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug"
          >
            {{ product.name }}
          </h2>
        </router-link>

        <!-- Spec Pills -->
        <div class="flex flex-wrap gap-1.5">
          <span
            v-if="product.ram"
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
      <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3">
        <!-- Price -->
        <div class="min-w-0">
          <div v-if="product.oldPrice" class="text-xs text-zinc-400 line-through font-semibold">
            {{ formatPrice(product.oldPrice) }}
          </div>
          <div class="text-lg font-black text-[#00a046] tracking-tight">
            {{ formatPrice(product.price) }}
          </div>
        </div>

        <!-- Cart + Compare -->
        <div class="flex items-center gap-1.5 shrink-0">
          <div class="relative group/cart">
            <button
              :disabled="!product.inStock"
              :class="product.inStock
                ? 'bg-[#00a046] hover:bg-[#00b050] text-white active:scale-[0.98]'
                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'"
              class="w-8 h-8 flex items-center justify-center rounded-md transition-all shadow-sm shrink-0"
              @click="cartStore.addToCart(product)"
            >
              <span class="material-symbols-outlined text-[17px]">shopping_cart</span>
            </button>
            <div class="absolute bottom-full right-0 mb-1.5 px-2 py-1 bg-zinc-800 dark:bg-zinc-700 text-white text-[11px] rounded opacity-0 pointer-events-none group-hover/cart:opacity-100 transition-opacity whitespace-nowrap z-10 font-semibold">
              {{ product.inStock ? 'Купити' : 'Немає в наявності' }}
            </div>
          </div>

          <!-- Compare -->
          <div class="relative group/cmp">
            <button
              :class="cartStore.isInCompare(product.id)
                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] border-[#00a046]/30'
                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700 hover:text-[#00a046] hover:border-[#00a046]/30'"
              class="w-8 h-8 flex items-center justify-center rounded-md border transition-all shrink-0"
              @click="cartStore.toggleCompare(product)"
            >
              <span class="material-symbols-outlined text-[16px]">compare_arrows</span>
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
