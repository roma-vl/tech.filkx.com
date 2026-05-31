<script setup lang="ts">
import { ref } from "vue";
import { useCartStore } from "@/entities/order/model/cartStore";

interface Product {
  id: string | number;
  slug: string;
  category: string;
  name: string;
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
    carouselRef.value.scrollBy({
      left: scrollAmount,
      behavior: "smooth",
    });
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
  <!-- Recommended for You Section -->
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 font-sans">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-10">
      <div class="space-y-1">
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
        >
          Рекомендовано для вас
        </h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          На основі ваших інтересів та історії переглядів
        </p>
      </div>

      <!-- Arrow Controls -->
      <div class="flex gap-2">
        <button
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          @click="scrollCarousel('left')"
        >
          <span
            class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350"
          >chevron_left</span>
        </button>
        <button
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          @click="scrollCarousel('right')"
        >
          <span
            class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350"
          >chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto gap-6 hide-scrollbar scroll-smooth snap-x snap-mandatory px-1 py-4"
    >
      <!-- Recommendation Item -->
      <div
        v-for="prod in products"
        :key="prod.id"
        class="group flex flex-col p-4 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-3xl hover:shadow-xl hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 relative overflow-hidden w-[calc(50%-12px)] md:w-[calc(33.33%-16px)] lg:w-[calc(20%-20px)] min-w-[240px] snap-start"
      >
        <!-- Product Image Container -->
        <div
          class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-2xl mb-4 overflow-hidden relative flex items-center justify-center p-2 cursor-pointer"
          @click="
            $router.push({ name: 'product-detail', params: { id: prod.slug } })
          "
        >
          <img
            class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500"
            :src="prod.image"
            :alt="prod.name"
          >
          <!-- Wishlist -->
          <button
            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/95 dark:bg-zinc-800/95 shadow hover:scale-110 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
            @click.stop="cartStore.toggleWishlist(prod as any)"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-rose-600': cartStore.isInWishlist(prod.id as any) }"
              :style="
                cartStore.isInWishlist(prod.id as any)
                  ? 'font-variation-settings: \'FILL\' 1;'
                  : ''
              "
            >
              favorite
            </span>
          </button>
        </div>

        <!-- Product Text Info -->
        <div class="flex flex-col flex-grow">
          <span
            class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] mb-1.5 uppercase tracking-wider"
          >{{ prod.category }}</span>
          <h3
            class="font-extrabold text-sm md:text-base text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] transition-colors line-clamp-2 leading-snug min-h-[40px] md:min-h-[44px] cursor-pointer"
            @click="
              $router.push({
                name: 'product-detail',
                params: { id: prod.slug },
              })
            "
          >
            {{ prod.name }}
          </h3>

          <!-- Rating -->
          <div class="flex items-center gap-1 my-2">
            <div class="flex text-amber-400">
              <span
                v-for="star in 4"
                :key="star"
                class="material-symbols-outlined text-[13px]"
                style="font-variation-settings: &quot;FILL&quot; 1"
              >star</span>
              <span class="material-symbols-outlined text-[13px]">star_half</span>
            </div>
            <span
              class="text-zinc-555 dark:text-zinc-455 text-[10px] font-bold ml-1"
            >({{ prod.reviews }})</span>
          </div>

          <!-- Price -->
          <div class="flex flex-wrap items-baseline gap-2 mt-auto">
            <span class="font-black text-lg md:text-xl text-[#00a046]">{{
              formatPrice(prod.price)
            }}</span>
            <span
              v-if="prod.oldPrice"
              class="text-xs text-zinc-400 line-through font-bold"
            >{{ formatPrice(prod.oldPrice) }}</span>
          </div>

          <!-- Actions -->
          <div class="mt-4 flex gap-2">
            <button
              class="flex-grow bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-xl text-xs font-extrabold shadow-sm transition-colors flex items-center justify-center gap-1.5"
              @click.stop="cartStore.addToCart(prod as any)"
            >
              <span>В кошик</span>
              <span class="material-symbols-outlined text-[15px]">shopping_cart</span>
            </button>
            <button
              class="w-9 h-9 border border-zinc-200 dark:border-zinc-800 text-zinc-550 dark:text-zinc-450 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex items-center justify-center"
              :class="{
                'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]':
                  cartStore.isInCompare(prod.id as any),
              }"
              title="Порівняти"
              @click.stop="cartStore.toggleCompare(prod as any)"
            >
              <span
                class="material-symbols-outlined text-[16px]"
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
