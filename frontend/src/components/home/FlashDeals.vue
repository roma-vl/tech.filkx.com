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
const selectedColor = ref(0);
const quantity = ref(1);

const openQuickView = (product) => {
  activeProduct.value = product;
  selectedColor.value = 0;
  quantity.value = 1;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  activeProduct.value = null;
};

const incrementQty = () => {
  quantity.value++;
};

const decrementQty = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
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
    description: 'Професійний звук високої чіткості з гібридним активним шумозаглушенням (ANC), режимом прозорості та до 40 годин роботи на одному заряді батареї.',
    specs: {
      brand: 'FilkxTech',
      warranty: '24 місяці',
      sku: 'FT-AUD-PRO9',
      availability: 'В наявності',
      colors: ['#09090b', '#e4e4e7', '#27272a']
    },
    features: [
      'Гібридне активне шумозаглушення (ANC) до 40 дБ',
      '40 годин відтворення з вимкненим ANC',
      'Швидка зарядка: 10 хв дають 5 годин роботи',
      'Bluetooth 5.3 та підтримка кодеку LDAC'
    ]
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
    description: 'Ефект повного занурення з радіусом вигину 1500R, частотою оновлення 144 Гц, відгуком 1 мс та підтримкою технології FreeSync Premium.',
    specs: {
      brand: 'FilkxTech',
      warranty: '36 місяців',
      sku: 'FT-MON-32CRV',
      availability: 'В наявності',
      colors: ['#09090b', '#71717a']
    },
    features: [
      'Радіус кривизни екрану 1500R для повного занурення',
      'Частота оновлення 144 Гц та час відгуку 1 мс',
      'Матриця VA з контрастністю 3000:1',
      'Підтримка AMD FreeSync Premium'
    ]
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
    description: 'Цілодобовий моніторинг серцевого ритму, кисню в крові, фаз сну, тренувань та пройденої дистанції за допомогою вбудованого GPS.',
    specs: {
      brand: 'FilkxTech',
      warranty: '12 місяців',
      sku: 'FT-WCH-SPT5',
      availability: 'В наявності',
      colors: ['#09090b', '#dc2626', '#0284c7']
    },
    features: [
      'Вбудований GPS для точного трекінгу бігу та велоспорту',
      'Водонепроникність класу 5ATM (до 50 метрів)',
      'До 7 днів автономної роботи від одного заряду',
      'Вимірювання рівня кисню в крові (SpO2)'
    ]
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
    description: 'Професійні знімки завдяки повнокадровій матриці високої роздільної здатності, зйомці 4K відео та системі оптичної стабілізації.',
    specs: {
      brand: 'Panasonic',
      warranty: '24 місяці',
      sku: 'LMX-ALPH-V2',
      availability: 'В наявності',
      colors: ['#09090b', '#e4e4e7']
    },
    features: [
      'Повнокадровий сенсор 24.2 МП з високим динамічним діапазоном',
      'Відеозйомка 4K 60p 10-bit без ліміту часу',
      '5-осьова подвійна стабілізація зображення (Dual I.S. 2)',
      'Швидкий та точний автофокус з технологією розпізнавання об\'єктів'
    ]
  }
];
</script>

<template>
  <!-- Hot Deals Section -->
  <section class="bg-zinc-50 dark:bg-zinc-950 py-16 border-y border-zinc-100 dark:border-zinc-900 select-none font-sans">
    <div class="max-w-container-max mx-auto px-4 md:px-8">
      
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div class="space-y-1">
          <span class="text-[#00a046] font-extrabold text-sm uppercase tracking-wider">Супер Знижки</span>
          <div class="flex flex-wrap items-center gap-4">
            <h2 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight">Гарячі пропозиції дня</h2>
            <!-- Countdown timer -->
            <div class="flex items-center gap-2 bg-rose-600 text-white px-4 py-1.5 rounded-xl text-xs md:text-sm font-black shadow-md shadow-rose-600/10">
              <span class="material-symbols-outlined text-[18px] animate-pulse">schedule</span>
              <span class="font-mono text-sm md:text-base">
                {{ formatNumber(hours) }}:{{ formatNumber(minutes) }}:{{ formatNumber(seconds) }}
              </span>
            </div>
          </div>
        </div>
        <a class="text-zinc-500 hover:text-[#00a046] font-bold text-sm flex items-center gap-1 transition-colors" href="/catalog">
          Всі акційні товари
          <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </a>
      </div>

      <!-- Products Grid (Thinner gap to make cards wider) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div
          v-for="prod in products"
          :key="prod.id"
          class="bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 group relative flex flex-col border border-zinc-100 dark:border-zinc-800"
        >
          <!-- Sale Badge -->
          <div class="absolute top-4 left-4 z-10">
            <span class="bg-rose-600 text-white px-2.5 py-0.5 rounded-lg font-black text-xs shadow-sm">
              {{ prod.discount }}
            </span>
          </div>

          <!-- Wishlist Button -->
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm shadow hover:scale-110 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-rose-600': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>

          <!-- Padding reduced from p-6 to p-4 -->
          <div class="p-4 md:p-5 flex-grow flex flex-col">
            <!-- Product Image Container (Reduced vertical margins, photo padding reduced from p-5 to p-2) -->
            <div class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-2xl mb-3 overflow-hidden relative flex items-center justify-center">
              <img
                class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500"
                :src="prod.image"
                :alt="prod.name"
              />
            </div>

            <p class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] mb-1.5 uppercase tracking-wider">{{ prod.category }}</p>
            <h3 class="font-extrabold text-base md:text-lg text-zinc-850 dark:text-zinc-100 line-clamp-3 mb-2.5 leading-snug min-h-[72px] hover:text-[#00a046] transition-colors cursor-pointer">{{ prod.name }}</h3>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-3 mt-auto">
              <div class="flex text-amber-400">
                <span
                  v-for="star in 4"
                  :key="star"
                  class="material-symbols-outlined text-[15px]"
                  style="font-variation-settings: 'FILL' 1;"
                >star</span>
                <span class="material-symbols-outlined text-[15px]">star_half</span>
              </div>
              <span class="text-zinc-500 dark:text-zinc-400 text-[11px] font-bold ml-1">({{ prod.reviews }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
              <span class="font-black text-xl md:text-2xl text-zinc-900 dark:text-white">${{ prod.price.toFixed(2) }}</span>
              <span class="text-xs text-zinc-400 line-through">${{ prod.oldPrice.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Inventory Progress Bar -->
          <div class="px-4 md:px-5 pb-3" v-if="prod.soldPercent">
            <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full mb-1.5 overflow-hidden">
              <div class="bg-rose-600 h-full rounded-full" :style="{ width: prod.soldPercent + '%' }"></div>
            </div>
            <div class="flex justify-between items-center text-[11px]">
              <p class="font-extrabold text-rose-600 uppercase tracking-wider">Залишилось: {{ prod.leftCount }} шт</p>
              <p class="font-bold text-zinc-400">{{ prod.soldPercent }}% Розпродано</p>
            </div>
          </div>

          <!-- Action Buttons (padding adjusted) -->
          <div class="px-4 md:px-5 pb-5 mt-auto flex flex-col gap-2.5">
            <button
              @click="store.addToCart(prod)"
              class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3 rounded-xl text-sm font-extrabold shadow-sm shadow-emerald-700/10 transition-colors flex items-center justify-center gap-2"
            >
              В кошик
              <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
            </button>
            <div class="grid grid-cols-4 gap-2">
              <button
                @click="openQuickView(prod)"
                class="col-span-3 py-2 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl font-extrabold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all text-xs"
              >
                Швидкий огляд
              </button>
              <button
                @click="store.toggleCompare(prod)"
                class="col-span-1 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-xl font-bold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex items-center justify-center"
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

    <!-- Quick View Modal (Enhanced with specs, features, colors, qty selectors) -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-fade"
    >
      <div
        class="bg-white dark:bg-zinc-900 rounded-3xl max-w-2xl w-full relative shadow-2xl border border-zinc-200 dark:border-zinc-800 flex flex-col max-h-[90vh] overflow-y-auto"
      >
        <!-- Close Button -->
        <button
          @click="closeModal"
          class="absolute top-5 right-5 text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-250 transition-colors z-20"
        >
          <span class="material-symbols-outlined text-[26px]">close</span>
        </button>

        <div class="p-6 md:p-8" v-if="activeProduct">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Image and Basic Info -->
            <div>
              <div class="aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-2xl flex items-center justify-center p-3 relative mb-4">
                <img class="w-full h-full object-contain" :src="activeProduct.image" :alt="activeProduct.name" />
                <!-- Status Badge -->
                <span class="absolute top-3 left-3 bg-emerald-500/10 text-emerald-500 px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider border border-emerald-500/20">
                  {{ activeProduct.specs?.availability }}
                </span>
              </div>

              <!-- Specifications Table -->
              <div class="space-y-2 mt-4 bg-zinc-50 dark:bg-zinc-850 p-4 rounded-2xl">
                <h4 class="font-extrabold text-xs text-zinc-400 uppercase tracking-wider mb-2">Характеристики</h4>
                <div class="flex justify-between text-xs py-1 border-b border-zinc-200/50 dark:border-zinc-800">
                  <span class="text-zinc-500">Бренд</span>
                  <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ activeProduct.specs?.brand }}</span>
                </div>
                <div class="flex justify-between text-xs py-1 border-b border-zinc-200/50 dark:border-zinc-800">
                  <span class="text-zinc-500">Артикул</span>
                  <span class="font-bold text-zinc-800 dark:text-zinc-200 font-mono">{{ activeProduct.specs?.sku }}</span>
                </div>
                <div class="flex justify-between text-xs py-1">
                  <span class="text-zinc-500">Гарантія</span>
                  <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ activeProduct.specs?.warranty }}</span>
                </div>
              </div>
            </div>

            <!-- Product Details and Actions -->
            <div class="flex flex-col justify-between pb-2">
              <div class="space-y-4">
                <div>
                  <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-wider">{{ activeProduct.category }}</span>
                  <h3 class="font-extrabold text-lg md:text-xl mt-1 text-zinc-900 dark:text-white leading-snug">{{ activeProduct.name }}</h3>

                  <!-- Rating -->
                  <div class="flex items-center gap-1.5 mt-2">
                    <div class="flex text-amber-400">
                      <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    </div>
                    <span class="text-zinc-500 text-xs font-bold">({{ activeProduct.reviews }} відгуків)</span>
                  </div>
                </div>

                <!-- Price Box -->
                <div class="flex items-baseline gap-3 bg-zinc-50 dark:bg-zinc-850 px-4 py-3 rounded-2xl w-fit">
                  <span class="text-3xl font-black text-zinc-900 dark:text-white">${{ activeProduct.price.toFixed(2) }}</span>
                  <span class="text-sm text-zinc-400 line-through">${{ activeProduct.oldPrice.toFixed(2) }}</span>
                </div>

                <!-- Excerpt / Description -->
                <p class="text-zinc-650 dark:text-zinc-450 text-xs md:text-sm leading-relaxed">{{ activeProduct.description }}</p>

                <!-- Color selector -->
                <div v-if="activeProduct.specs?.colors" class="space-y-1.5">
                  <span class="text-xs font-bold text-zinc-500">Колір:</span>
                  <div class="flex gap-2">
                    <button
                      v-for="(color, index) in activeProduct.specs.colors"
                      :key="index"
                      @click="selectedColor = index"
                      class="w-6 h-6 rounded-full border-2 transition-all flex items-center justify-center"
                      :class="selectedColor === index ? 'border-[#00a046]' : 'border-transparent'"
                    >
                      <span class="w-4 h-4 rounded-full border border-black/10" :style="{ backgroundColor: color }"></span>
                    </button>
                  </div>
                </div>

                <!-- Bullet Features List -->
                <div v-if="activeProduct.features" class="space-y-1.5">
                  <span class="text-xs font-bold text-zinc-500">Особливості модели:</span>
                  <ul class="space-y-1">
                    <li 
                      v-for="(feat, fIdx) in activeProduct.features" 
                      :key="fIdx"
                      class="text-[11px] md:text-xs text-zinc-600 dark:text-zinc-400 flex items-start gap-1.5 leading-relaxed"
                    >
                      <span class="material-symbols-outlined text-[#00a046] text-[14px] mt-0.5">check_circle</span>
                      {{ feat }}
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Actions Selector & Buy button -->
              <div class="mt-6 border-t border-zinc-100 dark:border-zinc-800 pt-6 flex flex-col gap-4">
                <!-- Qty counter -->
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-zinc-500">Кількість:</span>
                  <div class="flex items-center border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-zinc-50 dark:bg-zinc-850">
                    <button @click="decrementQty" class="w-8 h-8 flex items-center justify-center text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">remove</span>
                    </button>
                    <span class="w-10 text-center font-bold text-xs text-zinc-800 dark:text-zinc-200">{{ quantity }}</span>
                    <button @click="incrementQty" class="w-8 h-8 flex items-center justify-center text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                      <span class="material-symbols-outlined text-[16px]">add</span>
                    </button>
                  </div>
                </div>

                <button
                  @click="store.addToCart(activeProduct); closeModal()"
                  class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3.5 rounded-xl text-sm font-extrabold transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-700/10"
                >
                  Додати в кошик
                  <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                </button>
              </div>
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
