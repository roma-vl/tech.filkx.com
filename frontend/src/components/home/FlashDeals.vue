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
    name: 'Безпровідні навушники FilkxTech Studio Pro ANC',
    category: 'Аудіотехніка',
    price: 189.00,
    oldPrice: 349.00,
    discount: '-45% OFF',
    rating: 4.5,
    reviews: 128,
    soldPercent: 75,
    leftCount: 24,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBiUjsdg3UEZ3Invqr34vCkdhNreoHpan9Pb-BGKmaVy0RU0uSQyzTsKkbXAwFc883Y5jTxMbVRUKZoDSaTo3FbaT7XaZnss_re9YT00JhaTBlC6yvueGFeJfKhhh6JIjDtiTNIfRdJjC8ZyTTSHtYB81L85eJ1STBcLutY96W12sDqOctNxTwyq1m0MT7_6PTUKAE858poN7UqRe7nE46hjcjRrp_larxv7sHMDVCn7iT7817fw1OcxPdOG2sWfInGcMEAEIPakTE',
    description: 'Професійний звук високої чіткості з гібридним активним шумозаглушенням (ANC), режимом прозорості та до 40 годин роботи на одному заряді батареї.'
  },
  {
    id: 2,
    name: 'Вигнутий ігровий монітор FilkxTech Curved Gaming 32"',
    category: 'Геймінг',
    price: 499.00,
    oldPrice: 729.00,
    discount: '-31% OFF',
    rating: 4.8,
    reviews: 95,
    soldPercent: 40,
    leftCount: 15,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVMdErktV3TxMtO3JGMvZ9zS0x0zYbKz1BOjfXYU1LCka8ZihAQRInqqCc_p8i-qxa_HPIqDZ5Kevwr5VKLqyqstWGjEH_WRir7OtrPCpV_H8AvfRt69AI8p1TEbtE5tbqG2zcqQqVYp5pEPBnpsxa17bgXzaPwXHLRwCkbNLOL2MDuK_HzBB7j5pEfGKiX20Mo3vcs919pbLNN6aCAU31C3gWvj4f1OiGSSrW_Xd-ECi9ml_qk2QQhzRko2TzwHZxUspi7SRTQJg',
    description: 'Ефект повного занурення з радіусом вигину 1500R, частотою оновлення 144 Гц, відгуком 1 мс та підтримкою технології FreeSync Premium.'
  },
  {
    id: 3,
    name: 'Смарт-годинник FilkxTech Watch Sport 5 GPS',
    category: 'Смарт-годинники',
    price: 129.00,
    oldPrice: 199.00,
    discount: '-35% OFF',
    rating: 4.3,
    reviews: 64,
    soldPercent: 60,
    leftCount: 18,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0',
    description: 'Цілодобовий моніторинг серцевого ритму, кисню в крові, фаз сну, тренувань та пройденої дистанції за допомогою вбудованого GPS.'
  },
  {
    id: 4,
    name: 'Дзеркальна камера Lumix Alpha Prime v2 Mirrorless',
    category: 'Камери',
    price: 1150.00,
    oldPrice: 1400.00,
    discount: '-17% OFF',
    rating: 4.9,
    reviews: 42,
    soldPercent: 85,
    leftCount: 5,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuATZZV5WGL8X21fMJ4zbzJqjOQrP5WtH59wdZ76aQq8CQz4N-_BW5kXynAsvxVY7V9KfXszWmJgUfQbJP92IJNVprYoqpbyPA1X8MLw3pVOwBJ8VSb4aH5zwNl18jlhXVFDAv8rEtCBSTQw23VkV72JflGsCm_YankTEulT2iRKpF041yLSapjVqyWOk9nP-vg3RI9GljpcKRP-ftNvSORJcNl0YjjQn6rD0krvStirsy_BBHTMZ8Dd3-OSCaaeIJZanzdpfzxUSuw',
    description: 'Професійні знімки завдяки повнокадровій матриці високої роздільної здатності, зйомці 4K відео та системі оптичної стабілізації.'
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
  <!-- Hot Deals Section -->
  <section class="bg-zinc-50 dark:bg-zinc-950 py-12 border-y border-zinc-100 dark:border-zinc-900 select-none font-sans">
    <div class="max-w-container-max mx-auto px-4 md:px-8">
      
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="space-y-1">
          <span class="text-[#00a046] font-bold text-xs uppercase tracking-wider">Супер Знижки</span>
          <div class="flex flex-wrap items-center gap-4">
            <h2 class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight">Гарячі пропозиції дня</h2>
            <!-- Countdown timer -->
            <div class="flex items-center gap-1.5 bg-rose-600 text-white px-3 py-1 rounded-lg text-xs font-black shadow-md shadow-rose-600/10">
              <span class="material-symbols-outlined text-[16px] animate-pulse">schedule</span>
              <span class="font-mono text-sm">
                {{ formatNumber(hours) }}:{{ formatNumber(minutes) }}:{{ formatNumber(seconds) }}
              </span>
            </div>
          </div>
        </div>
        <a class="text-zinc-500 hover:text-[#00a046] font-bold text-xs flex items-center gap-1 transition-colors" href="/catalog">
          Всі акційні товари
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </a>
      </div>

      <!-- Products Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="prod in products"
          :key="prod.id"
          class="bg-white dark:bg-zinc-900 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 group relative flex flex-col border border-zinc-100 dark:border-zinc-800"
        >
          <!-- Sale Badge -->
          <div class="absolute top-4 left-4 z-10">
            <span class="bg-rose-600 text-white px-2.5 py-1 rounded-lg font-black text-[10px] shadow-sm">
              {{ prod.discount }}
            </span>
          </div>

          <!-- Wishlist Button -->
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm shadow hover:scale-105 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-rose-600': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>

          <div class="p-5 flex-grow flex flex-col">
            <!-- Product Image -->
            <div class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center">
              <img
                class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                :src="prod.image"
                :alt="prod.name"
              />
            </div>

            <p class="text-zinc-400 font-bold text-[10px] mb-1.5 uppercase tracking-wider">{{ prod.category }}</p>
            <h3 class="font-bold text-sm text-zinc-850 dark:text-zinc-100 line-clamp-2 mb-3 leading-snug min-h-[40px] hover:text-[#00a046] transition-colors cursor-pointer">{{ prod.name }}</h3>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-4 mt-auto">
              <div class="flex text-amber-400">
                <span
                  v-for="star in 4"
                  :key="star"
                  class="material-symbols-outlined text-[14px]"
                  style="font-variation-settings: 'FILL' 1;"
                >star</span>
                <span class="material-symbols-outlined text-[14px]">star_half</span>
              </div>
              <span class="text-zinc-400 text-[10px] font-semibold ml-1">({{ prod.reviews }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
              <span class="font-black text-lg text-zinc-900 dark:text-white">${{ prod.price.toFixed(2) }}</span>
              <span class="text-xs text-zinc-400 line-through">${{ prod.oldPrice.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Inventory Progress Bar -->
          <div class="px-5 pb-4" v-if="prod.soldPercent">
            <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1 rounded-full mb-1.5 overflow-hidden">
              <div class="bg-rose-600 h-full rounded-full" :style="{ width: prod.soldPercent + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-[9px]">
              <p class="font-extrabold text-rose-600 uppercase tracking-wider">Залишилось: {{ prod.leftCount }} шт</p>
              <p class="font-medium text-zinc-400">{{ prod.soldPercent }}% Розпродано</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="px-5 pb-5 mt-auto flex flex-col gap-2">
            <button
              @click="store.addToCart(prod)"
              class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2 rounded-lg text-xs font-bold shadow-md shadow-emerald-700/10 transition-colors flex items-center justify-center gap-1.5"
            >
              В кошик
              <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
            </button>
            <div class="grid grid-cols-4 gap-2">
              <button
                @click="openQuickView(prod)"
                class="col-span-3 py-1.5 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-lg font-bold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all text-[11px]"
              >
                Огляд
              </button>
              <button
                @click="store.toggleCompare(prod)"
                class="col-span-1 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-lg font-bold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex items-center justify-center"
                :class="{ 'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]': store.isInCompare(prod.id) }"
                title="Порівняти"
              >
                <span class="material-symbols-outlined text-[16px]" :class="{ 'fill': store.isInCompare(prod.id) }">compare_arrows</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick View Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-fade"
    >
      <div
        class="bg-white dark:bg-zinc-900 rounded-2xl max-w-xl w-full p-6 relative shadow-2xl border border-zinc-100 dark:border-zinc-800 flex flex-col"
      >
        <button
          @click="closeModal"
          class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
        >
          <span class="material-symbols-outlined text-[24px]">close</span>
        </button>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-2" v-if="activeProduct">
          <div class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-xl flex items-center justify-center p-4">
            <img class="w-full h-full object-contain" :src="activeProduct.image" :alt="activeProduct.name" />
          </div>
          <div class="flex flex-col justify-between">
            <div>
              <span class="text-[#00a046] font-bold text-[10px] uppercase tracking-wider">{{ activeProduct.category }}</span>
              <h3 class="font-bold text-base mt-1.5 text-zinc-900 dark:text-white leading-snug">{{ activeProduct.name }}</h3>

              <div class="flex items-center gap-1.5 mt-2">
                <div class="flex text-amber-400">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <span class="text-zinc-400 text-[10px] font-semibold">({{ activeProduct.reviews }} відгуків)</span>
              </div>

              <p class="text-zinc-500 dark:text-zinc-400 text-xs mt-3 leading-relaxed">{{ activeProduct.description }}</p>
            </div>

            <div class="mt-4 border-t border-zinc-100 dark:border-zinc-800 pt-4">
              <div class="flex items-baseline gap-2.5 mb-4">
                <span class="text-2xl font-black text-zinc-900 dark:text-white">${{ activeProduct.price.toFixed(2) }}</span>
                <span class="text-xs text-zinc-400 line-through">${{ activeProduct.oldPrice.toFixed(2) }}</span>
              </div>
              <button
                @click="store.addToCart(activeProduct); closeModal()"
                class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-md shadow-emerald-700/10"
              >
                Додати в кошик
                <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.animate-fade { animation: fadeIn .2s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
