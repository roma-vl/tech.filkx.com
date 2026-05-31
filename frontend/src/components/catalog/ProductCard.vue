<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";

interface ProductItem {
  id: string | number;
  name: string;
  brand: string;
  image: string;
  rating: number;
  reviews: number;
  price: number;
  oldPrice?: number;
  inStock: boolean;
  description: string;
  ram?: string;
  badge?: string;
  badgeClass?: string;
  specs: {
    screen: string;
    storage: string;
  };
}

const props = defineProps<{
  product: ProductItem;
  viewMode?: "grid" | "list";
}>();

const emit = defineEmits<{
  (e: "quick-view", product: ProductItem): void;
}>();

const cartStore = useCartStore();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>

<template>
  <article
    :class="
      viewMode === 'grid'
        ? 'flex-col rounded-xl hover:shadow-xl hover:border-emerald-500/30'
        : 'flex-col sm:flex-row rounded-xl hover:shadow-xl hover:border-emerald-500/30'
    "
    class="group flex bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 transition-all duration-300 overflow-hidden relative"
  >
    <!-- Image Section -->
    <div
      :class="
        viewMode === 'grid'
          ? 'w-full p-4 border-b border-zinc-100 dark:border-zinc-800'
          : 'w-full sm:w-60 p-4 border-r border-zinc-100 dark:border-zinc-800'
      "
      class="relative bg-white dark:bg-white/95 flex justify-center items-center aspect-[1.15/1]"
    >
      <a
        class="w-full h-full overflow-hidden relative cursor-pointer block"
        @click.prevent="cartStore.viewProduct(product as any)"
      >
        <img
          :alt="product.name"
          class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 p-2"
          :src="product.image"
        >
        <span
          v-if="product.badge"
          :class="product.badgeClass"
          class="absolute top-2 left-2 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm"
        >{{ product.badge }}</span>
      </a>

      <!-- Quick View Floating Button on Hover (Grid) -->
      <button
        class="absolute bottom-4 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all bg-zinc-955/80 hover:bg-zinc-950 text-white font-extrabold text-[10px] px-3.5 py-2 rounded-lg flex items-center gap-1.5 shadow-md backdrop-blur-sm"
        @click="emit('quick-view', product)"
      >
        <span class="material-symbols-outlined text-[14px]">visibility</span>
        Швидкий перегляд
      </button>

      <!-- Wishlist button (oblong pill) -->
      <button
        class="absolute top-4 right-4 px-2.5 py-1.5 bg-white/90 dark:bg-zinc-800/90 hover:bg-rose-500/10 hover:text-rose-550 text-zinc-550 dark:text-zinc-300 rounded-lg transition-all shadow-sm flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider backdrop-blur-sm"
        type="button"
        @click="cartStore.toggleWishlist(product as any)"
      >
        <span
          :class="{ 'text-rose-550': cartStore.isInWishlist(product.id as any) }"
          :style="
            cartStore.isInWishlist(product.id as any)
              ? 'font-variation-settings: \'FILL\' 1;'
              : ''
          "
          class="material-symbols-outlined text-[14px]"
        >
          favorite
        </span>
        <span>{{
          cartStore.isInWishlist(product.id as any) ? "Обрано" : "В обране"
        }}</span>
      </button>
    </div>

    <!-- Product Details Body -->
    <div
      :class="
        viewMode === 'grid'
          ? 'p-5 flex-col flex-1'
          : 'p-6 flex-1 flex flex-col justify-between'
      "
      class="flex flex-col justify-between"
    >
      <div class="space-y-3">
        <!-- Stars & brand -->
        <div class="flex items-center justify-between gap-2">
          <span
            class="text-[9px] font-black text-[#00a046] uppercase bg-emerald-500/10 px-2 py-0.5 rounded"
          >{{ product.brand }}</span>
          <div class="flex items-center gap-1">
            <div class="flex text-amber-400">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[14px]"
                :style="
                  star <= Math.floor(product.rating)
                    ? 'font-variation-settings: \'FILL\' 1;'
                    : ''
                "
              >
                {{ star <= Math.floor(product.rating) ? "star" : "star_half" }}
              </span>
            </div>
            <span
              class="text-[10px] font-black text-zinc-450 dark:text-zinc-500"
            >({{ product.reviews }})</span>
          </div>
        </div>

        <!-- Product Name -->
        <a
          class="block text-left cursor-pointer"
          @click.prevent="cartStore.viewProduct(product as any)"
        >
          <h2
            :class="
              viewMode === 'grid'
                ? 'text-sm md:text-base line-clamp-2 min-h-[44px]'
                : 'text-base md:text-lg'
            "
            class="font-extrabold text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug"
          >
            {{ product.name }}
          </h2>
        </a>

        <!-- Specs Pills (Grid / List spec display) -->
        <div class="flex flex-wrap gap-1.5 mt-2">
          <span
            class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">memory</span>
            {{ product.ram }} RAM
          </span>
          <span
            class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">laptop</span>
            {{ product.specs.screen.split(" ")[0] }}
          </span>
          <span
            class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">storage</span>
            {{ product.specs.storage.split(" ")[0] }}
          </span>
          <span
            v-if="product.inStock"
            class="inline-flex items-center gap-1 rounded bg-emerald-500/5 border border-emerald-500/10 px-2 py-0.5 text-[9px] font-extrabold text-[#00a046]"
          >
            В наявності
          </span>
          <span
            v-else
            class="inline-flex items-center gap-1 rounded bg-zinc-500/5 border border-zinc-500/10 px-2 py-0.5 text-[9px] font-extrabold text-zinc-450 dark:text-zinc-500"
          >
            Немає в наявності
          </span>
        </div>

        <!-- List short description -->
        <p
          v-if="viewMode === 'list'"
          class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed mt-2.5"
        >
          {{ product.description }}
        </p>
      </div>

      <!-- Price and Cart Buttons -->
      <div
        :class="
          viewMode === 'grid'
            ? 'pt-4 mt-4 border-t border-zinc-100 dark:border-zinc-800'
            : 'mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex sm:flex-row flex-col justify-between items-stretch sm:items-center gap-4'
        "
        class="space-y-3"
      >
        <div class="flex items-center justify-between gap-4">
          <div class="flex flex-col">
            <span
              v-if="product.oldPrice"
              class="text-[10px] text-zinc-400 line-through font-extrabold"
            >{{ formatPrice(product.oldPrice) }}</span>
            <span
              class="text-lg md:text-xl font-black text-[#00a046] tracking-tight"
            >{{ formatPrice(product.price) }}</span>
          </div>

          <!-- Compare Button -->
          <button
            type="button"
            :class="
              cartStore.isInCompare(product.id as any)
                ? 'bg-emerald-500/10 text-[#00a046] border-[#00a046]/30'
                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-550 dark:text-zinc-400 border-zinc-250 dark:border-zinc-700 hover:text-[#00a046] hover:border-[#00a046]/40'
            "
            class="flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-wider transition-all"
            @click="cartStore.toggleCompare(product as any)"
          >
            <span class="material-symbols-outlined text-[14px]">compare_arrows</span>
            <span>{{
              cartStore.isInCompare(product.id as any) ? "У порівнянні" : "Порівняти"
            }}</span>
          </button>
        </div>

        <div :class="viewMode === 'grid' ? 'w-full' : 'w-full sm:w-auto'">
          <button
            :disabled="!product.inStock"
            :class="
              product.inStock
                ? 'bg-[#00a046] hover:bg-[#00b050] text-white active:scale-[0.98]'
                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'
            "
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-2.5 px-6 rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-wider"
            type="button"
            @click="cartStore.addToCart(product as any)"
          >
            <span class="material-symbols-outlined text-[16px] md:text-[18px]">shopping_cart</span>
            {{ product.inStock ? "Купити" : "Немає в наявності" }}
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
