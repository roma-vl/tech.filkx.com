<script setup>
import { ref } from 'vue';
import { store } from '@/store.js';

const props = defineProps({
  products: {
    type: Array,
    default: () => []
  }
});

const carouselRef = ref(null);

const scrollCarousel = (direction) => {
  if (carouselRef.value) {
    const scrollAmount = direction === 'left' ? -320 : 320;
    carouselRef.value.scrollBy({
      left: scrollAmount,
      behavior: 'smooth'
    });
  }
};
</script>

<template>
  <!-- Recommended for You Section -->
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 select-none font-sans">
    
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-10">
      <div class="space-y-1">
        <h2 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight">Рекомендовано для вас</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">На основі ваших інтересів та історії переглядів</p>
      </div>
      
      <!-- Arrow Controls -->
      <div class="flex gap-2">
        <button
          @click="scrollCarousel('left')"
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
        >
          <span class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350">chevron_left</span>
        </button>
        <button
          @click="scrollCarousel('right')"
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
        >
          <span class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto gap-6 hide-scrollbar scroll-smooth snap-x snap-mandatory px-1 py-4"
    >
      <!-- Recommendation Item -->
      <router-link
        v-for="prod in products"
        :key="prod.id"
        :to="{ name: 'product-detail', params: { id: prod.slug } }"
        class="group cursor-pointer flex-shrink-0 w-[calc(50%-12px)] md:w-[calc(33.33%-16px)] lg:w-[calc(20%-20px)] min-w-[220px] snap-start block"
        @click="store.viewProduct(prod)"
      >
        <!-- Product Image Container -->
        <div class="aspect-square bg-zinc-50 dark:bg-zinc-900 rounded-3xl mb-2.5 overflow-hidden relative border border-zinc-100 dark:border-zinc-800 shadow-sm transition-all duration-300 group-hover:shadow-lg group-hover:border-zinc-200 dark:group-hover:border-zinc-700">
          <img
            class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500"
            :src="prod.image"
            :alt="prod.name"
          />
          <!-- Wishlist -->
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-md shadow hover:scale-105 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 opacity-0 group-hover:opacity-100 z-10"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-rose-600': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>
        </div>

        <!-- Product Text Info -->
        <div class="flex flex-col gap-2">
          <span class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] uppercase tracking-wider">{{ prod.category }}</span>
          <h3 class="font-extrabold text-sm md:text-base text-zinc-800 dark:text-zinc-200 group-hover:text-[#00a046] transition-colors line-clamp-2 leading-snug min-h-[40px] md:min-h-[44px]">
            {{ prod.name }}
          </h3>
          <div class="flex items-center justify-between mt-1">
            <span class="font-black text-lg md:text-xl text-zinc-900 dark:text-white">${{ prod.price.toFixed(2) }}</span>
            <div class="flex gap-2">
              <!-- Compare -->
              <button
                @click.stop="store.toggleCompare(prod)"
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-650 dark:text-zinc-350 flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:scale-105 active:scale-95 transition-all"
                :class="{ 'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]': store.isInCompare(prod.id) }"
                title="Порівняти"
              >
                <span class="material-symbols-outlined text-[18px]" :class="{ 'fill': store.isInCompare(prod.id) }">compare_arrows</span>
              </button>
              <!-- Buy -->
              <button
                @click.stop="store.addToCart(prod)"
                class="w-9 h-9 rounded-xl bg-[#00a046] hover:bg-[#00b050] text-white flex items-center justify-center hover:scale-105 active:scale-95 transition-all shadow-sm"
                title="В кошик"
              >
                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
              </button>
            </div>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
