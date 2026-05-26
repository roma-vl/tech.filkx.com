<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { store } from '@/store.js';

// Countdown Timer Logic
const hours = ref(23);
const minutes = ref(59);
const seconds = ref(59);
let timerId = null;

const startCountdown = () => {
  timerId = setInterval(() => {
    if (seconds.value > 0) {
      seconds.value--;
    } else {
      seconds.value = 59;
      if (minutes.value > 0) {
        minutes.value--;
      } else {
        minutes.value = 59;
        if (hours.value > 0) {
          hours.value--;
        } else {
          // Reset countdown to 24h
          hours.value = 23;
          minutes.value = 59;
          seconds.value = 59;
        }
      }
    }
  }, 1000);
};

const formatNumber = (num) => {
  return num < 10 ? `0${num}` : num;
};

// Quick View Modal Logic
const showModal = ref(false);
const activeProduct = ref(null);

const openQuickView = (product) => {
  activeProduct.value = product;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  activeProduct.value = null;
};

const products = [
  {
    id: 1,
    name: 'Studio Pro Noise Cancelling Wireless Headphones',
    category: 'Audio Tech',
    price: 189.00,
    oldPrice: 349.00,
    discount: '-45% OFF',
    rating: 4.5,
    reviews: 128,
    soldPercent: 75,
    leftCount: 24,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBiUjsdg3UEZ3Invqr34vCkdhNreoHpan9Pb-BGKmaVy0RU0uSQyzTsKkbXAwFc883Y5jTxMbVRUKZoDSaTo3FbaT7XaZnss_re9YT00JhaTBlC6yvueGFeJfKhhh6JIjDtiTNIfRdJjC8ZyTTSHtYB81L85eJ1STBcLutY96W12sDqOctNxTwyq1m0MT7_6PTUKAE858poN7UqRe7nE46hjcjRrp_larxv7sHMDVCn7iT7817fw1OcxPdOG2sWfInGcMEAEIPakTE',
    description: 'Professional high-fidelity sound with advanced hybrid active noise cancellation, ambient pass-through mode, and up to 40 hours of battery life.'
  },
  {
    id: 2,
    name: '32" Ultra-Wide Curved Gaming Monitor 144Hz',
    category: 'Gaming',
    price: 499.00,
    oldPrice: 729.00,
    discount: '-31% OFF',
    rating: 4.8,
    reviews: 95,
    soldPercent: 40,
    leftCount: 15,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVMdErktV3TxMtO3JGMvZ9zS0x0zYbKz1BOjfXYU1LCka8ZihAQRInqqCc_p8i-qxa_HPIqDZ5Kevwr5VKLqyqstWGjEH_WRir7OtrPCpV_H8AvfRt69AI8p1TEbtE5tbqG2zcqQqVYp5pEPBnpsxa17bgXzaPwXHLRwCkbNLOL2MDuK_HzBB7j5pEfGKiX20Mo3vcs919pbLNN6aCAU31C3gWvj4f1OiGSSrW_Xd-ECi9ml_qk2QQhzRko2TzwHZxUspi7SRTQJg',
    description: 'Immersive curved screen with 144Hz refresh rate, 1ms response time, FreeSync Premium support, and breathtaking colors.'
  },
  {
    id: 3,
    name: 'FitTrack Pro 5 Health & GPS Smartwatch',
    category: 'Wearables',
    price: 129.00,
    oldPrice: 199.00,
    discount: '-35% OFF',
    rating: 4.3,
    reviews: 64,
    soldPercent: 60,
    leftCount: 18,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0',
    description: 'Track daily activity, heart rate, blood oxygen levels, workouts, sleep stages, and route navigation with precision built-in GPS.'
  },
  {
    id: 4,
    name: 'Lumix Alpha Prime v2 Mirrorless Camera',
    category: 'Cameras',
    price: 1150.00,
    oldPrice: 1400.00,
    discount: '-17% OFF',
    rating: 4.9,
    reviews: 42,
    soldPercent: 85,
    leftCount: 5,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuATZZV5WGL8X21fMJ4zbzJqjOQrP5WtH59wdZ76aQq8CQz4N-_BW5kXynAsvxVY7V9KfXszWmJgUfQbJP92IJNVprYoqpbyPA1X8MLw3pVOwBJ8VSb4aH5zwNl18jlhXVFDAv8rEtCBSTQw23VkV72JflGsCm_YankTEulT2iRKpF041yLSapjVqyWOk9nP-vg3RI9GljpcKRP-ftNvSORJcNl0YjjQn6rD0krvStirsy_BBHTMZ8Dd3-OSCaaeIJZanzdpfzxUSuw',
    description: 'Capture world-class details with a high-resolution full-frame sensor, 4K/60p video, fast auto-focus tracking, and dual image stabilization.'
  }
];

onMounted(() => {
  startCountdown();
});

onUnmounted(() => {
  if (timerId) clearInterval(timerId);
});
</script>

<template>
  <!-- Hot Deals Section (Premium Cards) -->
  <section class="bg-surface-container-low py-stack-lg border-y border-surface-variant">
    <div class="max-w-container-max mx-auto px-margin-desktop">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-gutter mb-10">
        <div class="flex flex-col gap-2">
          <span class="text-primary font-bold text-sm uppercase tracking-widest">Time Limited Offers</span>
          <div class="flex items-center gap-6">
            <h2 class="font-headline-lg text-headline-lg">Hot Flash Deals</h2>
            <div class="flex items-center gap-3 bg-error text-white px-4 py-2 rounded-lg font-bold shadow-lg shadow-error/20">
              <span class="material-symbols-outlined text-[20px]">timer</span>
              <span class="font-mono text-lg" id="countdown">
                {{ formatNumber(hours) }}:{{ formatNumber(minutes) }}:{{ formatNumber(seconds) }}
              </span>
            </div>
          </div>
        </div>
        <a class="text-on-surface-variant font-bold hover:text-primary transition-colors flex items-center gap-1 group" href="#">
          View All Deals <span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">chevron_right</span>
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div
          v-for="prod in products"
          :key="prod.id"
          class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 product-card group relative flex flex-col border border-outline-variant/20"
        >
          <!-- Sale Badge -->
          <div class="absolute top-4 left-4 z-10">
            <span class="bg-error text-white px-2.5 py-1 rounded font-black text-xs shadow-md">
              {{ prod.discount }}
            </span>
          </div>

          <!-- Wishlist Button -->
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 backdrop-blur-sm shadow hover:bg-white transition-all flex items-center justify-center text-on-surface-variant hover:text-error z-10"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-error': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>

          <div class="p-6 flex-grow">
            <!-- Product Image -->
            <div class="aspect-square bg-surface-container-lowest rounded-lg mb-6 overflow-hidden relative group">
              <img
                class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                :src="prod.image"
                :alt="prod.name"
              />
            </div>

            <p class="text-primary font-label-md font-bold mb-2 uppercase tracking-tighter">{{ prod.category }}</p>
            <h3 class="font-title-md text-on-surface line-clamp-2 mb-3 leading-tight min-h-[40px]">{{ prod.name }}</h3>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-4">
              <div class="flex text-amber-400">
                <span
                  v-for="star in 4"
                  :key="star"
                  class="material-symbols-outlined text-[16px] fill"
                  style="font-variation-settings: 'FILL' 1;"
                >star</span>
                <span class="material-symbols-outlined text-[16px]">star_half</span>
              </div>
              <span class="text-on-surface-variant text-xs font-medium ml-1">({{ prod.reviews }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3">
              <span class="font-price-lg text-primary">${{ prod.price.toFixed(2) }}</span>
              <span class="font-body-md text-on-surface-variant line-through opacity-60">${{ prod.oldPrice.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Inventory / Progress Bar (For first product or all) -->
          <div class="px-6 pb-6" v-if="prod.soldPercent">
            <div class="w-full bg-surface-container-high h-1.5 rounded-full mb-2 overflow-hidden">
              <div class="bg-error h-full rounded-full" :style="{ width: prod.soldPercent + '%' }"></div>
            </div>
            <div class="flex justify-between items-center">
              <p class="text-[10px] font-black text-error uppercase tracking-wider">Hurry! {{ prod.leftCount }} Left</p>
              <p class="text-[10px] font-bold text-on-surface-variant">{{ prod.soldPercent }}% Sold</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="px-6 pb-6 mt-auto flex flex-col gap-2">
            <button
              @click="store.addToCart(prod)"
              class="w-full bg-primary text-white py-2.5 rounded-lg font-bold shadow-md hover:bg-primary-container transition-colors flex items-center justify-center gap-2"
            >
              Add to Cart <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
            </button>
            <div class="flex gap-2">
              <button
                @click="openQuickView(prod)"
                class="flex-grow py-2 border border-outline-variant text-on-surface rounded-lg font-bold hover:bg-surface-container-low hover:border-primary transition-all text-xs"
              >
                Quick View
              </button>
              <button
                @click="store.toggleCompare(prod)"
                class="px-3 border border-outline-variant text-on-surface rounded-lg font-bold hover:bg-surface-container-low hover:border-primary transition-all flex items-center justify-center"
                :class="{ 'bg-primary-container/20 border-primary text-primary': store.isInCompare(prod.id) }"
                title="Compare Product"
              >
                <span class="material-symbols-outlined text-[18px]" :class="{ 'fill': store.isInCompare(prod.id) }">compare_arrows</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick View Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    >
      <div
        class="bg-white rounded-2xl max-w-2xl w-full p-8 relative shadow-2xl border border-surface-variant animate-in fade-in zoom-in duration-300"
      >
        <button
          @click="closeModal"
          class="absolute top-4 right-4 text-on-surface-variant hover:text-primary transition-colors"
        >
          <span class="material-symbols-outlined text-[28px]">close</span>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8" v-if="activeProduct">
          <div class="aspect-square bg-surface-container-low rounded-xl flex items-center justify-center p-4">
            <img class="w-full h-full object-contain" :src="activeProduct.image" :alt="activeProduct.name" />
          </div>
          <div class="flex flex-col justify-between">
            <div>
              <span class="text-primary font-bold text-xs uppercase tracking-widest">{{ activeProduct.category }}</span>
              <h3 class="font-headline-md text-headline-md mt-2 text-on-background leading-tight">{{ activeProduct.name }}</h3>

              <div class="flex items-center gap-2 mt-3">
                <div class="flex text-amber-400">
                  <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                  <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                  <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                  <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                  <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <span class="text-on-surface-variant text-sm font-semibold">({{ activeProduct.reviews }} reviews)</span>
              </div>

              <p class="text-on-surface-variant text-sm mt-4 leading-relaxed">{{ activeProduct.description }}</p>
            </div>

            <div class="mt-6 border-t border-surface-variant pt-6">
              <div class="flex items-baseline gap-4 mb-4">
                <span class="text-3xl font-bold text-primary">${{ activeProduct.price.toFixed(2) }}</span>
                <span class="text-lg text-on-surface-variant line-through opacity-60">${{ activeProduct.oldPrice.toFixed(2) }}</span>
              </div>
              <button
                @click="store.addToCart(activeProduct); closeModal()"
                class="w-full bg-primary text-white py-4 rounded-xl font-title-md hover:bg-primary-container transition-all flex items-center justify-center gap-3 shadow-lg shadow-primary/20"
              >
                Add to Shopping Cart <span class="material-symbols-outlined">shopping_cart</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
