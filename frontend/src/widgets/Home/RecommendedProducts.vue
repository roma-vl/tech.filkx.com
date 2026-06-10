<script setup lang="ts">
import { ref } from "vue";
import { useCartStore } from "@/entities/order/model/cartStore";

interface Product {
  id: string | number;
  slug: string;
  category: string;
  name: string;
  rating: number;
  reviews: number;
  price: number;
  oldPrice?: number;
  image: string;
}

const props = defineProps<{
  products: Product[];
}>();

const cartStore = useCartStore();
const carouselRef = ref<HTMLElement | null>(null);

const scrollCarousel = (direction: "left" | "right") => {
  if (carouselRef.value) {
    const scrollAmount = direction === "left" ? -320 : 320;
    carouselRef.value.scrollBy({ left: scrollAmount, behavior: "smooth" });
  }
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-14 font-sans">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="space-y-1.5">
        <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest">Персональна підбірка</span>
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
        >
          Рекомендовано для вас
        </h2>
        <p class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400">
          На основі ваших інтересів та історії переглядів
        </p>
      </div>

      <!-- Arrow Controls -->
      <div class="flex gap-2 shrink-0">
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          aria-label="Прокрутити ліворуч"
          @click="scrollCarousel('left')"
        >
          <span class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400">chevron_left</span>
        </button>
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          aria-label="Прокрутити праворуч"
          @click="scrollCarousel('right')"
        >
          <span class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto gap-4 hide-scrollbar scroll-smooth snap-x snap-mandatory px-0.5 py-2"
    >
      <div
        v-for="prod in products"
        :key="prod.id"
        class="group flex flex-col p-4 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl hover:shadow-lg hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 relative overflow-hidden w-[calc(50%-8px)] md:w-[calc(33.33%-11px)] lg:w-[calc(20%-13px)] min-w-[220px] snap-start shrink-0"
      >
        <!-- Image -->
        <div
          class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-lg mb-3 overflow-hidden relative flex items-center justify-center cursor-pointer"
          @click="$router.push({ name: 'product-detail', params: { id: prod.slug } })"
        >
          <img
            class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500"
            :src="prod.image"
            :alt="prod.name"
          />
          <!-- Wishlist -->
          <button
            class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/95 dark:bg-zinc-800/95 shadow hover:scale-110 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
            @click.stop="cartStore.toggleWishlist(prod as any)"
          >
            <span
              class="material-symbols-outlined text-[17px]"
              :class="{ 'fill text-rose-600': cartStore.isInWishlist(prod.id as any) }"
              :style="cartStore.isInWishlist(prod.id as any) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>
        </div>

        <!-- Info -->
        <div class="flex flex-col flex-grow">
          <span class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] mb-1 uppercase tracking-wider">
            {{ prod.category }}
          </span>
          <h3
            class="font-bold text-sm text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] transition-colors line-clamp-2 leading-snug min-h-[40px] cursor-pointer"
            @click="$router.push({ name: 'product-detail', params: { id: prod.slug } })"
          >
            {{ prod.name }}
          </h3>

          <!-- Rating -->
          <div class="flex items-center gap-1 my-2">
            <div class="flex">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[12px]"
                :class="star <= Math.round(prod.rating) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600'"
                :style="star <= Math.round(prod.rating) ? 'font-variation-settings: &quot;FILL&quot; 1' : ''"
              >star</span>
            </div>
            <span class="text-zinc-400 text-[10px] font-bold ml-0.5">({{ prod.reviews }})</span>
          </div>

          <!-- Price -->
          <div class="flex flex-wrap items-baseline gap-1.5 mt-auto">
            <span class="font-black text-base text-[#00a046]">{{ formatPrice(prod.price) }}</span>
            <span v-if="prod.oldPrice" class="text-xs text-zinc-400 line-through font-bold">{{ formatPrice(prod.oldPrice) }}</span>
          </div>

          <!-- Actions -->
          <div class="mt-3 flex gap-2">
            <button
              class="flex-grow bg-[#00a046] hover:bg-[#00b050] text-white py-2 rounded-lg text-xs font-extrabold shadow-sm transition-colors flex items-center justify-center gap-1.5"
              @click.stop="cartStore.addToCart(prod as any)"
            >
              <span>В кошик</span>
              <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
            </button>
            <button
              class="w-8 h-8 border border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex items-center justify-center shrink-0"
              :class="{ 'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]': cartStore.isInCompare(prod.id as any) }"
              title="Порівняти"
              @click.stop="cartStore.toggleCompare(prod as any)"
            >
              <span
                class="material-symbols-outlined text-[15px]"
                :class="{ fill: cartStore.isInCompare(prod.id as any) }"
              >compare_arrows</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
